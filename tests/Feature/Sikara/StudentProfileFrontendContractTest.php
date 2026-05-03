<?php

namespace Tests\Feature\Sikara;

use Tests\TestCase;

class StudentProfileFrontendContractTest extends TestCase
{
    public function test_profile_page_frontend_implements_pbi_10_interactions(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Profile/Show.vue'));

        $this->assertStringContainsString('isProfileDrawerOpen', $source);
        $this->assertStringContainsString('validateGpa', $source);
        $this->assertStringContainsString('IPK harus berada di antara 0.00 - 4.00', $source);
        $this->assertStringContainsString('Profil berhasil diperbarui.', $source);
        $this->assertStringContainsString("route('skills.store')", $source);
        $this->assertStringContainsString("route('cv.download')", $source);
        $this->assertStringContainsString('profilePhotoPreview', $source);
        $this->assertStringContainsString('type="file"', $source);
        $this->assertStringContainsString('profileForm.photo', $source);
        $this->assertStringNotContainsString('Sertifikasi', $source);
    }
}
