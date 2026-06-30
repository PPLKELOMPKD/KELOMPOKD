<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $company,
        public readonly ?string $reason
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi Akun Perusahaan SIKARA Ditolak')
            ->view('emails.company-rejected', [
                'company' => $this->company,
                'reason' => $this->reason ?: 'Dokumen tidak lengkap atau tidak sesuai.',
                'url' => route('register'),
            ]);
    }
}
