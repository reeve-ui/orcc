<?php

namespace Tests\Unit;

use App\Support\AgeCategory;
use Carbon\Carbon;
use Tests\TestCase;

class AgeCategoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_classifies_age_boundaries(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28'));

        $this->assertSame('Anak', AgeCategory::fromBirthDate(Carbon::parse('2014-10-28')));
        $this->assertSame('Remaja', AgeCategory::fromBirthDate(Carbon::parse('2013-10-28')));
        $this->assertSame('Pemuda', AgeCategory::fromBirthDate(Carbon::parse('2008-10-28')));
        $this->assertSame('Dewasa', AgeCategory::fromBirthDate(Carbon::parse('1995-10-28')));
        $this->assertSame('Lansia', AgeCategory::fromBirthDate(Carbon::parse('1966-10-28')));
    }

    public function test_it_returns_unknown_for_missing_or_future_birth_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28'));

        $this->assertSame(AgeCategory::UNKNOWN, AgeCategory::fromBirthDate(null));
        $this->assertSame(AgeCategory::UNKNOWN, AgeCategory::fromBirthDate(Carbon::parse('2027-01-01')));
    }

    public function test_groups_cover_configured_ranges(): void
    {
        $this->assertSame([0, 12], AgeCategory::bounds('Anak'));
        $this->assertSame([60, null], AgeCategory::bounds('Lansia'));
        $this->assertNull(AgeCategory::bounds('Tidak Ada'));
    }
}
