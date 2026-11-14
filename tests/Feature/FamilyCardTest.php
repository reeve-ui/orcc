<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\ChurchSetting;
use App\Models\Family;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_kartu_kkj_diunduh_sebagai_pdf_a4_landscape_dengan_penanda_tangan(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin.kartu@example.test',
            'password' => 'password',
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $family = Family::query()->create([
            'kkj_id' => 'SEKRT00260099',
            'head_name' => 'Kepala Keluarga Uji',
            'joined_year' => 2026,
            'joy_number' => 'S-2026-0099',
            'province' => 'Jawa Barat',
            'city' => 'Kota Depok',
            'district' => 'Tapos',
            'village' => 'Sukatani',
            'postal_code' => '16454',
            'address' => 'Jl. Contoh No. 1',
        ]);

        Member::query()->create([
            'family_id' => $family->id,
            'ktj_id' => 'SEKRT0026009900',
            'family_status' => 'Kepala Keluarga',
            'gender' => 'Pria',
            'full_name' => 'Kepala Keluarga Uji',
            'birth_date' => '1980-01-01',
            'marital_status' => 'Telah Menikah',
            'spouse_name' => 'Pasangan Uji',
            'whatsapp' => '081234567890',
            'cell_group' => 'Keluarga Sukacita',
        ]);

        ChurchSetting::current()->update([
            'name' => 'Gereja Uji',
            'address' => 'Depok, Jawa Barat',
            'card_note' => 'Catatan kartu uji.',
            'card_signature_name' => 'Pdt. Uji, S.Th.',
            'card_signature_title' => 'Gembala Sidang',
        ]);

        $response = $this->actingAs($admin)->get(route('families.card.download', $family));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString(
            '/MediaBox [0.000 0.000 841.890 595.280]',
            $response->getContent()
        );

        $html = view('pdf.family-card', [
            'family' => $family->load('members'),
            'members' => $family->members,
            'qrMatrix' => [],
            'church' => config('church'),
            'logoDataUri' => null,
        ])->render();

        $this->assertStringContainsString('size: A4 landscape', $html);
        $this->assertStringContainsString('KARTU KELUARGA JEMAAT (KKJ)', $html);
        $this->assertStringContainsString('Pdt. Uji, S.Th.', $html);
        $this->assertStringContainsString('Gembala Sidang', $html);
        $this->assertStringContainsString('#1e3a8a', $html);
    }
}
