<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mail:test {email : The recipient email address}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test email to verify mail configuration is working';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        $this->info("📧 Mengirim test email ke: {$email}");
        $this->info('Menggunakan konfigurasi:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['MAIL_MAILER',       config('mail.default')],
                ['MAIL_HOST',         config('mail.mailers.smtp.host')],
                ['MAIL_PORT',         config('mail.mailers.smtp.port')],
                ['MAIL_USERNAME',     config('mail.mailers.smtp.username') ?: '(kosong)'],
                ['MAIL_PASSWORD',     config('mail.mailers.smtp.password') ? '***set***' : '(KOSONG - BELUM DIISI!)'],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                ['APP_URL',           config('app.url')],
            ]
        );

        if (! config('mail.mailers.smtp.password')) {
            $this->error('❌ MAIL_PASSWORD masih kosong! Isi dulu di .env sebelum testing.');
            return self::FAILURE;
        }

        try {
            Mail::raw(
                "Ini adalah test email dari SIKARA.\n\nJika Anda menerima email ini, konfigurasi SMTP sudah benar.\n\nApp URL: " . config('app.url'),
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('✅ Test Email SIKARA – Konfigurasi Berhasil!');
                }
            );

            $this->newLine();
            $this->info('✅ Email berhasil dikirim! Cek inbox ' . $email);
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Gagal mengirim email: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Kemungkinan penyebab:');
            $this->warn('1. MAIL_PASSWORD salah atau belum diisi dengan App Password Gmail');
            $this->warn('2. 2-Factor Authentication belum diaktifkan di akun Gmail');
            $this->warn('3. App Password belum dibuat di https://myaccount.google.com/apppasswords');
            return self::FAILURE;
        }
    }
}
