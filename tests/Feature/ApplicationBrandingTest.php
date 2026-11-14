<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationBrandingTest extends TestCase
{
    public function test_nama_dan_versi_aplikasi_tampil_di_halaman_masuk(): void
    {
        $version = config('app.version');

        $this->assertNotEmpty($version);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ORCC4CHURCH')
            ->assertSee('v'.$version);
    }
}
