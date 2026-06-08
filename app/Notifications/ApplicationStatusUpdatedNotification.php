<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly ?string $oldStatus,
        private readonly string $newStatus,
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $this->application->loadMissing(['internship.company']);

        return (new MailMessage)
            ->subject('Update Status Lamaran SIKARA')
            ->view('emails.application-status-updated', [
                'application' => $this->application,
                'internship' => $this->application->internship,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'statusLabel' => $this->statusLabel($this->newStatus),
                'guidance' => $this->guidance($this->newStatus),
                'url' => route('applications.index'),
            ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'menunggu ulasan' => 'Menunggu Ulasan',
            'wawancara' => 'Wawancara',
            'lolos' => 'Diterima',
            'tidak lolos' => 'Tidak Lolos',
            default => ucwords($status),
        };
    }

    private function guidance(string $status): string
    {
        return match ($status) {
            'wawancara' => 'Persiapkan diri Anda dan pantau informasi lanjutan dari perusahaan.',
            'lolos' => 'Selamat. Silakan pantau dashboard SIKARA untuk langkah berikutnya.',
            'tidak lolos' => 'Tetap semangat. Anda masih dapat melamar kesempatan lain di SIKARA.',
            default => 'Pantau dashboard SIKARA untuk melihat perkembangan lamaran Anda.',
        };
    }
}
