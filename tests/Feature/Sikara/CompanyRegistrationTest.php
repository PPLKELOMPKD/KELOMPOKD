<?php

namespace Tests\Feature\Sikara;

use App\Models\User;
use App\Models\PerusahaanProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_registration_uploads_legal_document_and_syncs_with_profile(): void
    {
        Storage::fake('public');

        $document = UploadedFile::fake()->create('siup_document.pdf', 100, 'application/pdf');

        $response = $this->post(route('register'), [
            'role' => 'perusahaan',
            'name' => 'PT Maju Terus',
            'email' => 'majuterus@test.com',
            'phone' => '081234567890',
            'legal_document' => $document,
            'terms' => true,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Registration redirects to email verification notice for Perusahaan
        $response->assertRedirect(route('verification.notice'));

        // Assert user was created
        $user = User::where('email', 'majuterus@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('perusahaan', $user->role);
        $this->assertEquals('inactive', $user->status);

        // Assert profile was created
        $profile = PerusahaanProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);

        // Assert file was uploaded and saved in database
        $this->assertNotNull($profile->legal_document_path);
        Storage::disk('public')->assertExists($profile->legal_document_path);

        // Assert admin verification detail page shows the legal document path
        $admin = User::factory()->create(['role' => 'admin']);

        $showResponse = $this->actingAs($admin)
            ->get(route('admin.verifications.show', $user->id));

        $showResponse->assertSuccessful();
        $showResponse->assertInertia(function ($page) use ($profile) {
            $page->component('Admin/Verifications/Show')
                ->where('profile.legal_document_path', $profile->legal_document_path);
        });
    }
}
