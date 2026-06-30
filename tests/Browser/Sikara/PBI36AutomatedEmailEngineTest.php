<?php

namespace Tests\Browser\Sikara;

use App\Models\Application;
use App\Models\Internship;
use App\Models\User;
use App\Models\PerusahaanProfile;
use App\Services\AutomatedMailService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use RuntimeException;
use Tests\DuskTestCase;

class PBI36AutomatedEmailEngineTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected static bool $initialized = false;

    protected function setUp(): void
    {
        parent::setUp();


        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }


        if (! self::$initialized) {
            sleep(8);
            self::$initialized = true;
        } else {
            sleep(1);
        }
    }

    protected function tearDown(): void
    {

        try {
            $this->browse(function (Browser $browser) {
                $browser->blank();
            });
        } catch (\Throwable $e) {
            
        }

        parent::tearDown();
    }

    protected function assertEmailSentWithSubject(string $subject): void
    {
        $logPath = storage_path('logs/laravel.log');
        $this->assertFileExists($logPath);


        usleep(200000);

        $logContent = file_get_contents($logPath);


        if (function_exists('mb_decode_mimeheader')) {
            $logContent = mb_decode_mimeheader($logContent);
        }

        $this->assertStringContainsString("Subject: {$subject}", $logContent);
    }


    public function test_tc01_mengirim_email_reset_password(): void
    {
        $user = User::factory()->create([
            'email' => 'registered_user@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/forgot-password')
                ->waitFor('#email', 15)
                ->type('#email', $user->email)
                ->press('Lanjutkan Proses Verifikasi')
                ->waitForText('Email Terkirim', 15);
        });

        $this->assertEmailSentWithSubject('Reset Password – SIKARA');
    }


    public function test_tc02_mengirim_email_registrasi_akun(): void
    {

        $tempPdf = tempnam(sys_get_temp_dir(), 'siup') . '.pdf';
        file_put_contents($tempPdf, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF");

        $this->browse(function (Browser $browser) use ($tempPdf) {
            $browser->visit('/register')
                ->waitFor('div.grid-cols-2 button', 15)
                ->click('div.grid-cols-2 button:nth-child(2)') 
                ->type('#name', 'PT Mitra Baru')
                ->type('#email', 'new_company@example.com')
                ->type('#phone', '081234567890')
                ->attach('#legal_document', $tempPdf)
                ->check('input[type="checkbox"]')
                ->type('#password', 'password123')
                ->type('#password_confirmation', 'password123')
                ->press('Daftar Sekarang')
                ->waitForRoute('verification.notice', [], 15);
        });

        if (file_exists($tempPdf)) {
            unlink($tempPdf);
        }

        $this->assertEmailSentWithSubject('Verifikasi Email Akun SIKARA');
    }


    public function test_tc03_mengirim_email_notifikasi_lamaran_baru(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'email_verified_at' => now(),
        ]);
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Backend Engineer Intern',
            'company_name' => $company->name,
            'location' => 'Bandung',
            'description' => 'Lowongan backend engineer intern.',
            'requirements' => 'Laravel dan MySQL',
            'deadline_at' => now()->addDays(10),
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                ->visit("/internships/{$internship->id}")
                ->waitForText('Lamar Sekarang', 15)
                ->press('Lamar Sekarang')
                ->waitForText('Application sent successfully!', 15);
        });

        $this->assertEmailSentWithSubject('Pelamar Baru untuk Backend Engineer Intern');
    }


    public function test_tc04_mengirim_email_perubahan_status_lamaran(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'email_verified_at' => now(),
        ]);
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'Frontend Engineer Intern',
            'company_name' => $company->name,
            'location' => 'Jakarta',
            'description' => 'Lowongan frontend engineer intern.',
            'requirements' => 'Vue dan Tailwind',
            'deadline_at' => now()->addDays(10),
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);
        $application = Application::query()->create([
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);

        $this->browse(function (Browser $browser) use ($company, $application) {
            $browser->loginAs($company)
                ->visit("/perusahaan/applicants/{$application->id}")
                ->waitForText('Undang Wawancara', 15)
                ->press('Undang Wawancara')
                ->waitForText('Ya, Undang')
                ->press('Ya, Undang')
                ->waitForText('updated to "Wawancara"', 15);
        });

        $this->assertEmailSentWithSubject('Update Status Lamaran SIKARA');
    }


    public function test_tc05_mengirim_email_verifikasi_perusahaan_diterima(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'inactive',
        ]);
        PerusahaanProfile::create([
            'user_id' => $company->id,
            'legal_document_path' => 'legal_documents/siup.pdf',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $company) {
            $browser->loginAs($admin)
                ->visit("/admin/verifications/{$company->id}")
                ->waitForText('Verifikasi & Setujui Mitra', 15)
                ->press('Verifikasi & Setujui Mitra')
                ->waitForText('Konfirmasi & Simpan')
                ->press('Konfirmasi & Simpan')
                ->waitForText('verified and activated', 15);
        });

        $this->assertEmailSentWithSubject('Akun Perusahaan SIKARA Terverifikasi');
    }


    public function test_tc06_mengirim_email_verifikasi_perusahaan_ditolak(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'status' => 'inactive',
        ]);
        PerusahaanProfile::create([
            'user_id' => $company->id,
            'legal_document_path' => 'legal_documents/siup.pdf',
        ]);

        $reason = 'Dokumen legal tidak jelas atau buram.';

        $this->browse(function (Browser $browser) use ($admin, $company, $reason) {
            $browser->loginAs($admin)
                ->visit("/admin/verifications/{$company->id}")
                ->waitForText('Tolak / Blokir Akun (Banned)', 15)
                ->press('Tolak / Blokir Akun (Banned)')
                ->waitForText('Catatan/Alasan Internal')
                ->type('textarea', $reason)
                ->press('Konfirmasi & Simpan')
                ->waitForText('banned', 15);
        });

        $this->assertEmailSentWithSubject('Verifikasi Akun Perusahaan SIKARA Ditolak');
    }


    public function test_tc07_email_penerima_tidak_valid(): void
    {
        $invalidCompany = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'email' => 'invalid-email-format',
        ]);

        $mailService = new AutomatedMailService();
        $mailService->sendCompanyVerified($invalidCompany);

        
        $logPath = storage_path('logs/laravel.log');
        $this->assertFileExists($logPath);
        $logContent = file_get_contents($logPath);
        $this->assertStringNotContainsString('Subject: Akun Perusahaan SIKARA Terverifikasi', $logContent);
        $this->assertStringContainsString('Automated mail skipped because recipient email is invalid.', $logContent);
    }

    public function test_tc08_mail_service_gagal(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
            'email_verified_at' => now(),
        ]);
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'email_verified_at' => now(),
            'status' => 'active',
        ]);
        $internship = Internship::query()->create([
            'company_id' => $company->id,
            'title' => 'QA Engineer Intern',
            'company_name' => $company->name,
            'location' => 'Bandung',
            'description' => 'Lowongan QA engineer intern.',
            'requirements' => 'Testing dasar',
            'deadline_at' => now()->addDays(10),
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->app->instance(AutomatedMailService::class, new class extends AutomatedMailService {
            public function sendApplicationSubmittedToCompany(Application $application): void
            {
                throw new RuntimeException('Provider down');
            }
        });

        $this->browse(function (Browser $browser) use ($student, $internship) {
            $browser->loginAs($student)
                ->visit("/internships/{$internship->id}")
                ->waitForText('Lamar Sekarang', 15)
                ->press('Lamar Sekarang')
                ->waitForText('Application sent successfully!', 15);
        });

        $this->assertDatabaseHas('applications', [
            'user_id' => $student->id,
            'internship_id' => $internship->id,
            'status' => 'submitted',
        ]);
    }


    public function test_tc09_template_email_tidak_ditemukan(): void
    {
        $company = User::factory()->create(['role' => User::ROLE_PERUSAHAAN]);

        $badNotification = new class extends \Illuminate\Notifications\Notification {
            public function via($notifiable) { return ['mail']; }
            public function toMail($notifiable) {
                return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->view('emails.non-existent-template');
            }
        };

        $mailService = new class extends AutomatedMailService {
            public function sendBadMail(User $recipient, $notification): void
            {
                $ref = new \ReflectionClass(AutomatedMailService::class);
                $method = $ref->getMethod('send');
                $method->setAccessible(true);
                $method->invoke($this, 'bad_event', $recipient, $notification);
            }
        };

        $mailService->sendBadMail($company, $badNotification);

        $logPath = storage_path('logs/laravel.log');
        $this->assertFileExists($logPath);
        $logContent = file_get_contents($logPath);
        $this->assertStringContainsString('Automated mail failed.', $logContent);
        $this->assertStringContainsString('emails.non-existent-template', $logContent);
    }
    public function test_tc10_email_berhasil_dikirim_dan_tercatat(): void
    {
        $company = User::factory()->create([
            'role' => User::ROLE_PERUSAHAAN,
            'email' => 'company_log@example.com',
        ]);

        $mailService = new AutomatedMailService();
        $mailService->sendCompanyVerified($company);

        $this->assertEmailSentWithSubject('Akun Perusahaan SIKARA Terverifikasi');
    }
}
