<?php

namespace Tests\Feature\Sikara;

use Tests\TestCase;

class LowonganFrontendContractTest extends TestCase
{
    public function test_lowongan_page_uses_database_filter_options_for_location_select(): void
    {
        $source = file_get_contents(resource_path('js/Pages/Features/Lowongan.vue'));

        $this->assertStringContainsString('opsiLokasi', $source);
        $this->assertStringContainsString(':options="opsiLokasi"', $source);
        $this->assertStringNotContainsString("import locationsData from '@/Data/locations.json';", $source);
    }
}
