<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_a_selected_role(): void
    {
        Storage::fake('public');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'perusahaan',
            'legal_document' => UploadedFile::fake()->create('legal.pdf', 100, 'application/pdf'),
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'perusahaan',
        ]);
    }

    public function test_registration_sends_branded_email_verification_notification(): void
    {
        Notification::fake();
        Storage::fake('public');

        $this->post('/register', [
            'name' => 'Company User',
            'email' => 'company@example.com',
            'phone' => '08123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'perusahaan',
            'legal_document' => UploadedFile::fake()->create('legal.pdf', 100, 'application/pdf'),
            'terms' => true,
        ]);

        $user = User::where('email', 'company@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_registration_does_not_send_email_verification_to_sikara_test_address(): void
    {
        Notification::fake();
        Storage::fake('public');

        $this->post('/register', [
            'name' => 'Test Admin',
            'email' => 'test-admin@sikara.test',
            'phone' => '08123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'perusahaan',
            'legal_document' => UploadedFile::fake()->create('legal.pdf', 100, 'application/pdf'),
            'terms' => true,
        ]);

        $user = User::where('email', 'test-admin@sikara.test')->firstOrFail();

        Notification::assertNotSentTo($user, VerifyEmailNotification::class);
    }

    public function test_mahasiswa_can_register_with_profile_details_from_registration_form(): void
    {
        $response = $this->post('/register', [
            'role' => 'mahasiswa',
            'name' => 'Naufal Dz',
            'email' => 'naufal@example.com',
            'phone' => '+62 812-3456-7890',
            'university' => 'Universitas Indonesia',
            'study_program' => 'Teknik Informatika',
            'nim' => '123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'naufal@example.com',
            'role' => 'mahasiswa',
        ]);
        $this->assertDatabaseHas('mahasiswa_profiles', [
            'nim' => '123456789',
            'phone' => '+62 812-3456-7890',
            'university' => 'Universitas Indonesia',
            'study_program' => 'Teknik Informatika',
        ]);
    }
}
