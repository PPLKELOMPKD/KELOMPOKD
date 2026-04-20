<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'perusahaan',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'perusahaan',
        ]);
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

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
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
