<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmittedToCompanyNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Application $application)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $this->application->loadMissing(['user.mahasiswaProfile', 'internship']);

        return (new MailMessage)
            ->subject('Pelamar Baru untuk '.$this->application->internship?->title)
            ->view('emails.application-submitted-company', [
                'application' => $this->application,
                'student' => $this->application->user,
                'internship' => $this->application->internship,
                'url' => route('perusahaan.applicants.show', $this->application),
            ]);
    }
}
