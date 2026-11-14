<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_gets_a_unique_verification_token_on_creation(): void
    {
        $first = Family::query()->create([
            'kkj_id' => 'TEST-VERIFY-1',
            'head_name' => 'Keluarga Pertama',
        ]);

        $second = Family::query()->create([
            'kkj_id' => 'TEST-VERIFY-2',
            'head_name' => 'Keluarga Kedua',
        ]);

        $this->assertNotEmpty($first->verification_token);
        $this->assertSame(32, strlen($first->verification_token));
        $this->assertNotSame($first->verification_token, $second->verification_token);
        $this->assertStringContainsString('/verifikasi/'.$first->verification_token, $first->verification_url);
    }

    public function test_public_verification_page_shows_summary_without_private_data(): void
    {
        $family = Family::query()->create([
            'kkj_id' => 'TEST-VERIFY-PUBLIC',
            'head_name' => 'Keluarga Terverifikasi',
            'village' => 'Sukamaju Baru',
            'district' => 'Tapos',
            'city' => 'Depok',
            'joined_year' => 2019,
        ]);

        Member::query()->create([
            'family_id' => $family->id,
            'full_name' => 'Kepala Terverifikasi',
            'family_status' => 'Kepala Keluarga',
            'nik' => '3276010101010001',
            'phone' => '081234567890',
            'email' => 'rahasia@example.test',
            'birth_date' => '1980-01-01',
        ]);

        $response = $this->get(route('families.verify', $family->verification_token));

        $response->assertOk()
            ->assertSee('Terdaftar di Sistem Data Jemaat')
            ->assertSee('TEST-VERIFY-PUBLIC')
            ->assertSee('Keluarga Terverifikasi')
            ->assertSee('Sukamaju Baru')
            ->assertSee('1 orang')
            ->assertDontSee('3276010101010001')
            ->assertDontSee('081234567890')
            ->assertDontSee('rahasia@example.test')
            ->assertDontSee('1980-01-01');
    }

    public function test_unknown_verification_token_is_not_found(): void
    {
        $this->get(route('families.verify', 'tokenyangtidakada1234567890abcdef'))
            ->assertNotFound();
    }

    public function test_guest_cannot_open_the_private_family_card(): void
    {
        $family = Family::query()->create([
            'kkj_id' => 'TEST-VERIFY-CARD',
            'head_name' => 'Keluarga Kartu',
        ]);

        $this->get(route('families.card', $family))
            ->assertRedirect(route('login'));
    }
}
