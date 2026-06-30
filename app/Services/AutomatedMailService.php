<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationStatusUpdatedNotification;
use App\Notifications\ApplicationSubmittedToCompanyNotification;
use App\Notifications\CompanyVerifiedNotification;
use App\Notifications\CompanyRejectedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutomatedMailService
{
    public function sendCompanyVerified(User $company): bool
    {
        return $this->send(
            'company_verified',
            $company,
            new CompanyVerifiedNotification($company)
        );
    }

    public function sendCompanyRejected(User $company, ?string $reason): bool
    {
        return $this->send(
            'company_rejected',
            $company,
            new CompanyRejectedNotification($company, $reason)
        );
    }

    public function sendApplicationSubmittedToCompany(Application $application): bool
    {
        $application->loadMissing(['internship.company', 'user']);

        return $this->send(
            'application_submitted_to_company',
            $application->internship?->company,
            new ApplicationSubmittedToCompanyNotification($application),
            $application
        );
    }

    public function sendApplicationStatusUpdated(Application $application, ?string $oldStatus, string $newStatus): bool
    {
        $application->loadMissing(['user', 'internship']);

        return $this->send(
            'application_status_updated',
            $application->user,
            new ApplicationStatusUpdatedNotification($application, $oldStatus, $newStatus),
            $application
        );
    }

    private function send(string $event, ?User $recipient, Notification $notification, ?Application $application = null): bool
    {
        if (! $recipient || ! filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Automated mail skipped because recipient email is invalid.', [
                'event' => $event,
                'recipient_id' => $recipient?->id,
                'recipient_email' => $recipient?->email,
                'application_id' => $application?->id,
            ]);

            return false;
        }

        try {
            $recipient->notify($notification);
            return true;
        } catch (Throwable $exception) {
            Log::error('Automated mail failed.', [
                'event' => $event,
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'application_id' => $application?->id,
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }
}
