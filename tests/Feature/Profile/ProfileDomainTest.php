<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_user_can_have_one_profile_and_many_skills(): void
    {
        $user = User::factory()->make();

        $this->assertSame('mahasiswa', $user->role);

        $user = User::factory()->mahasiswa()->create();

        $this->assertTrue(method_exists($user, 'mahasiswaProfile'));
        $this->assertTrue(method_exists($user, 'skills'));

        $user->mahasiswaProfile()->create([
            'nim' => '2023123456',
            'department' => 'Informatika',
            'study_program' => 'S1 Teknik Informatika',
            'gpa' => 3.75,
            'phone' => '081234567890',
            'university' => 'Universitas Contoh',
            'location' => 'Bandung',
            'bio' => 'Mahasiswa aktif dan antusias belajar.',
        ]);

        $user->skills()->createMany([
            [
                'name' => 'Laravel',
                'proficiency' => 'advanced',
            ],
            [
                'name' => 'Vue.js',
                'proficiency' => 'intermediate',
            ],
        ]);

        $user->refresh();

        $this->assertSame($user->id, $user->mahasiswaProfile->user_id);
        $this->assertSame('2023123456', $user->mahasiswaProfile->nim);
        $this->assertCount(2, $user->skills);
    }
}
