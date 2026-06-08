<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly User $company)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun Perusahaan SIKARA Terverifikasi')
            ->view('emails.company-verified', [
                'company' => $this->company,
                'url' => route('login'),
            ]);
    }
}
