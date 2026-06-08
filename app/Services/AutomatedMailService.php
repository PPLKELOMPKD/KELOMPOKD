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
    public function sendCompanyVerified(User $company): void
    {
        $this->send(
            'company_verified',
            $company,
            new CompanyVerifiedNotification($company)
        );
    }

    public function sendCompanyRejected(User $company, ?string $reason): void
    {
        $this->send(
            'company_rejected',
            $company,
            new CompanyRejectedNotification($company, $reason)
        );
    }

    public function sendApplicationSubmittedToCompany(Application $application): void
    {
        $application->loadMissing(['internship.company', 'user']);

        $this->send(
            'application_submitted_to_company',
            $application->internship?->company,
            new ApplicationSubmittedToCompanyNotification($application),
            $application
        );
    }

    public function sendApplicationStatusUpdated(Application $application, ?string $oldStatus, string $newStatus): void
    {
        $application->loadMissing(['user', 'internship']);

        $this->send(
            'application_status_updated',
            $application->user,
            new ApplicationStatusUpdatedNotification($application, $oldStatus, $newStatus),
            $application
        );
    }

    private function send(string $event, ?User $recipient, Notification $notification, ?Application $application = null): void
    {
        if (! $recipient || ! filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Automated mail skipped because recipient email is invalid.', [
                'event' => $event,
                'recipient_id' => $recipient?->id,
                'recipient_email' => $recipient?->email,
                'application_id' => $application?->id,
            ]);

            return;
        }

        try {
            $recipient->notify($notification);
        } catch (Throwable $exception) {
            Log::error('Automated mail failed.', [
                'event' => $event,
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'application_id' => $application?->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
