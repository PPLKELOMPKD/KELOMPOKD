<?php

namespace Tests\Feature\Sikara;

use App\Models\Internship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PBI15TimelineLowonganAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-01 & TC-02: Menampilkan Penanda Kalender dengan Benar & Menyediakan Data untuk Laci Samping
     * Memastikan data lowongan dengan tanggal tertentu (Created, Approved, Deadline) 
     * dikirim dengan benar ke frontend Inertia untuk ditampilkan pada Kalender & Drawer.
     */
    public function test_tc01_tc02_kalender_timeline_dan_data_drawer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Set waktu pengujian agar konsisten
        Carbon::setTestNow(Carbon::create(2026, 6, 8, 10, 0, 0));

        // Buat lowongan dengan spesifikasi tanggal Juni 2026:
        // - Dibuat (created_at): 4 Juni 2026
        // - Disetujui (moderated_at): 4 Juni 2026 (status approved)
        // - Deadline (deadline_at): 19 Juni 2026
        $internship = new Internship();
        $internship->timestamps = false;
        $internship->fill([
            'company_id' => null,
            'title' => 'Network Engineer Intern',
            'company_name' => 'PT SIKARA Net',
            'location' => 'Jakarta',
            'description' => 'Mengevaluasi infrastruktur jaringan.',
            'requirements' => 'Paham CCNA.',
            'work_type' => 'Magang',
            'deadline_at' => Carbon::create(2026, 6, 19),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
            'moderated_at' => Carbon::create(2026, 6, 4, 14, 0, 0),
        ]);
        $internship->created_at = Carbon::create(2026, 6, 4, 9, 0, 0);
        $internship->save();

        $response = $this->actingAs($admin)
            ->get(route('admin.internships.calendar'));

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Internships/Calendar')
            ->has('internships', 1)
            ->where('internships.0.id', $internship->id)
            ->where('internships.0.moderation_status', 'approved')
        );

        // Verifikasi format string tanggal yang dikirimkan ke Kalender/Drawer
        $inertiaData = $response->original->getData()['page']['props']['internships'][0];
        $this->assertStringContainsString('2026-06-04', (string) $inertiaData['created_at']);
        $this->assertStringContainsString('2026-06-04', (string) $inertiaData['moderated_at']);
        $this->assertStringContainsString('2026-06-19', (string) $inertiaData['deadline_at']);

        Carbon::setTestNow(); // Reset waktu testing
    }

    /**
     * TC-03: Filter Legenda Kategori
     * Memastikan data memiliki field type yang tepat (status, created, moderated, deadline)
     * sehingga logika filtering frontend (legenda checkbox) dapat memilah data secara instan.
     */
    public function test_tc03_format_data_untuk_filter_legenda(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Buat lowongan ditolak (rejected) untuk menguji filtering
        $internshipRejected = Internship::query()->create([
            'company_id' => null,
            'title' => 'UI Designer Intern',
            'company_name' => 'PT SIKARA Design',
            'location' => 'Bandung',
            'description' => 'Design layout.',
            'requirements' => 'Figma.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(5),
            'quota' => 1,
            'is_published' => false,
            'moderation_status' => 'rejected',
            'moderated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.internships.calendar'));

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Internships/Calendar')
            ->has('internships')
            // Memastikan data status penolakan 'rejected' terkirim untuk filter warna merah legenda
            ->where('internships.0.moderation_status', 'rejected')
            ->where('internships.0.is_published', false)
        );
    }

    /**
     * TC-04: Cek Tampilan Timeline Alur Lowongan
     * Verifikasi halaman detail lowongan oleh Admin memuat status disetujui (Approved)
     * dan tanggal sejarah moderasi (timeline alur) dengan benar.
     */
    public function test_tc04_tampilan_timeline_alur_lowongan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $internship = new Internship();
        $internship->timestamps = false;
        $internship->fill([
            'company_id' => null,
            'title' => 'System Analyst Intern',
            'company_name' => 'PT SIKARA System',
            'location' => 'Surabaya',
            'description' => 'System analysis.',
            'requirements' => 'Paham UML.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 2,
            'is_published' => true,
            'moderation_status' => 'approved',
            'moderated_at' => now()->subDays(4),
        ]);
        $internship->created_at = now()->subDays(5);
        $internship->save();

        $response = $this->actingAs($admin)
            ->get(route('admin.internships.show', $internship->id));

        $response->assertSuccessful();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Internships/Show')
            ->where('internship.id', $internship->id)
            ->where('internship.moderation_status', 'approved')
            ->has('internship.created_at')
            ->has('internship.moderated_at')
        );
    }

    /**
     * Proteksi Akses Halaman Kalender & Detail Moderasi
     * Role selain Admin (Mahasiswa & Perusahaan) tidak boleh mengakses kalender/show admin.
     */
    public function test_proteksi_akses_kalender_dan_moderasi_admin(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $company = User::factory()->create(['role' => 'perusahaan']);

        $internship = Internship::query()->create([
            'title' => 'Draft Internship',
            'company_name' => 'PT SIKARA',
            'location' => 'Bandung',
            'description' => 'Desc.',
            'requirements' => 'Req.',
            'work_type' => 'Magang',
            'quota' => 2,
            'deadline_at' => now()->addDays(5),
        ]);

        // 1. Mahasiswa mencoba akses
        $this->actingAs($student)
            ->get(route('admin.internships.calendar'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.internships.show', $internship->id))
            ->assertForbidden();

        // 2. Perusahaan mencoba akses
        $this->actingAs($company)
            ->get(route('admin.internships.calendar'))
            ->assertForbidden();

        $this->actingAs($company)
            ->get(route('admin.internships.show', $internship->id))
            ->assertForbidden();
    }
}
