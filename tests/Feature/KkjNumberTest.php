<?php

namespace Tests\Feature;

use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KkjNumberTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_nomor_kkj_dibuat_otomatis_dan_reset_setiap_tahun(): void
    {
        config(['church.code' => 'SEKRT']);
        Carbon::setTestNow('2026-05-01 10:00:00');

        $first = Family::query()->create(['head_name' => 'Keluarga Pertama']);
        $second = Family::query()->create(['head_name' => 'Keluarga Kedua']);

        $this->assertSame('SEKRT00260001', $first->kkj_id);
        $this->assertSame('SEKRT00260002', $second->kkj_id);

        Carbon::setTestNow('2027-01-02 10:00:00');

        $third = Family::query()->create(['head_name' => 'Keluarga Tahun Baru']);

        $this->assertSame('SEKRT00270001', $third->kkj_id);
    }

    public function test_nomor_kkj_tidak_dibuat_tanpa_kode_gereja(): void
    {
        config(['church.code' => null]);

        $this->expectException(\RuntimeException::class);

        Family::query()->create(['head_name' => 'Keluarga Tanpa Kode']);
    }
}
