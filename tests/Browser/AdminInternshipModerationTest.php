<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Internship;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminInternshipModerationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test admin can view internship moderation list and detail page.
     */
    public function test_admin_can_view_moderation_index_and_detail()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Android Engineer Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Membangun aplikasi Android.',
            'requirements' => 'Menguasai Kotlin/Java.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 2,
            'is_published' => false,
            'moderation_status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $internship) {
            $browser->loginAs($admin)
                    ->visit('/admin/internships')
                    ->waitForText('Android Engineer Intern')
                    ->assertSee('Android Engineer Intern')
                    ->clickLink('Detail')
                    ->waitForText('Garis Waktu Alur Lowongan')
                    ->assertPathIs('/admin/internships/' . $internship->id)
                    ->assertSee('Android Engineer Intern')
                    ->assertSee('Menunggu Review');
        });
    }

    /**
     * Test admin can approve an internship.
     */
    public function test_admin_can_approve_internship()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Data Analyst Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Jakarta',
            'description' => 'Menganalisis data performa.',
            'requirements' => 'Menguasai SQL/Python.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 3,
            'is_published' => false,
            'moderation_status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $internship) {
            $browser->loginAs($admin)
                    ->visit('/admin/internships/' . $internship->id)
                    ->waitForText('Setujui & Tayangkan')
                    ->press('Setujui & Tayangkan')
                    ->waitForText('Disetujui & Tayang')
                    ->assertSee('Disetujui & Tayang');
        });
    }

    /**
     * Test admin can reject an internship.
     */
    public function test_admin_can_reject_internship()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'UI/UX Designer Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Bandung',
            'description' => 'Mendesain antarmuka aplikasi.',
            'requirements' => 'Menguasai Figma.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 2,
            'is_published' => false,
            'moderation_status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $internship) {
            $browser->loginAs($admin)
                    ->visit('/admin/internships/' . $internship->id)
                    ->waitForText('Tolak Lowongan')
                    ->press('Tolak Lowongan')
                    ->waitForText('Tolak Lowongan') // Wait for modal to load
                    ->type('textarea', 'Kualifikasi yang dicantumkan tidak sesuai dengan standar program.')
                    ->press('Ya, Tolak Lowongan')
                    ->waitForText('Ditolak')
                    ->assertSee('Ditolak');
        });
    }

    /**
     * Test admin can takedown an approved internship.
     */
    public function test_admin_can_takedown_approved_internship()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $internship = Internship::query()->create([
            'company_id' => null,
            'title' => 'Backend Dev Intern',
            'company_name' => 'PT SIKARA',
            'location' => 'Surabaya',
            'description' => 'Membangun API Laravel.',
            'requirements' => 'Menguasai PHP.',
            'work_type' => 'Magang',
            'deadline_at' => now()->addDays(10),
            'quota' => 3,
            'is_published' => true,
            'moderation_status' => 'approved',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $internship) {
            $browser->loginAs($admin)
                    ->visit('/admin/internships/' . $internship->id)
                    ->waitForText('Takedown Lowongan')
                    ->press('Takedown Lowongan')
                    ->waitForText('Takedown Lowongan') // Wait for modal to load
                    ->type('textarea', 'Lowongan ini ditarik kembali karena alasan kepatuhan internal.')
                    ->press('Ya, Cabut Lowongan')
                    ->waitForText('Ditutup (Takedown)')
                    ->assertSee('Ditutup (Takedown)');
        });
    }
}
