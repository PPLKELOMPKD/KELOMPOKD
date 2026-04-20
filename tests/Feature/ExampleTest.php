<?php

namespace Tests\Feature;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_home_page_uses_the_inertia_shell(): void
    {
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('app');
        $response->assertSee('data-page=', false);
        $response->assertSee('const Ziggy=', false);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('profile', null)
            ->where('skills', [])
        );
    }

    public function test_the_home_page_shares_a_null_role_before_role_support_exists(): void
    {
        $this->withoutVite();

        $user = User::factory()->make();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('auth.user.role')
            ->where('auth.user.name', $user->name)
            ->where('auth.user.email', $user->email)
            ->where('auth.user.role', null)
        );
    }

    public function test_the_temporary_profile_show_page_exists(): void
    {
        $this->assertFileExists(resource_path('js/Pages/Profile/Show.vue'));
    }
}
