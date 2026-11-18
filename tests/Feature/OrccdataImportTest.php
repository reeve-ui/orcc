<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Family;
use App\Models\Member;
use App\Models\User;
use App\Services\OrccdataImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class OrccdataImportTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_service_mengimpor_kkj_dan_jemaat(): void
    {
        $result = app(OrccdataImporter::class)->import($this->workbook());

        $this->assertSame(1, $result['kkj_rows']);
        $this->assertSame(1, $result['member_rows']);
        $this->assertSame(1, $result['families_created']);
        $this->assertSame(1, $result['members_created']);
        $this->assertSame(0, $result['skipped']);
        $this->assertFalse($result['dry_run']);

        $this->assertDatabaseHas('families', [
            'kkj_id' => 'TEST-001',
            'head_name' => 'Kepala Keluarga Uji',
            'province' => 'Jawa Barat',
            'city' => 'Kota Depok',
            'district' => 'Pancoran Mas',
            'postal_code' => '16431',
        ]);

        $this->assertDatabaseHas('members', [
            'ktj_id' => 'TEST-001-01',
            'full_name' => 'Kepala Keluarga Uji',
            'family_status' => 'Kepala Keluarga',
            'gender' => 'Pria',
            'nik' => '3276010101010001',
            'email' => 'kepala@example.test',
            'marital_status' => 'Telah Menikah',
            'baptism_place' => 'Gereja Uji',
        ]);

        $member = Member::query()->where('ktj_id', 'TEST-001-01')->firstOrFail();
        $this->assertSame('2005-12-25', $member->baptism_date?->toDateString());
    }

    public function test_impor_dry_run_tidak_menyimpan_perubahan(): void
    {
        $result = app(OrccdataImporter::class)->import($this->workbook(), dryRun: true);

        $this->assertTrue($result['dry_run']);
        $this->assertSame(1, $result['families_created']);
        $this->assertSame(1, $result['members_created']);
        $this->assertDatabaseCount('families', 0);
        $this->assertDatabaseCount('members', 0);
    }

    public function test_impor_fresh_menghapus_data_lama(): void
    {
        $oldFamily = Family::query()->create([
            'kkj_id' => 'OLD-001',
            'head_name' => 'Keluarga Lama',
        ]);

        Member::query()->create([
            'family_id' => $oldFamily->id,
            'ktj_id' => 'OLD-001-01',
            'full_name' => 'Anggota Lama',
        ]);

        $result = app(OrccdataImporter::class)->import($this->workbook(), fresh: true);

        $this->assertTrue($result['fresh']);
        $this->assertDatabaseMissing('families', ['kkj_id' => 'OLD-001']);
        $this->assertDatabaseMissing('members', ['ktj_id' => 'OLD-001-01']);
        $this->assertDatabaseHas('families', ['kkj_id' => 'TEST-001']);
        $this->assertDatabaseCount('families', 1);
        $this->assertDatabaseCount('members', 1);
    }

    public function test_impor_ulang_tidak_menggandakan_data(): void
    {
        $importer = app(OrccdataImporter::class);
        $path = $this->workbook();

        $importer->import($path);
        $result = $importer->import($path);

        $this->assertSame(1, $result['families_updated']);
        $this->assertSame(1, $result['members_updated']);
        $this->assertDatabaseCount('families', 1);
        $this->assertDatabaseCount('members', 1);
    }

    public function test_sheet_jemaat_yang_hilang_menghasilkan_pesan_jelas(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Sheet KKJ atau JEMAAT tidak ditemukan');

        app(OrccdataImporter::class)->import($this->workbook(withMemberSheet: false));
    }

    public function test_admin_dapat_mengunggah_excel_dari_pengaturan(): void
    {
        $admin = $this->user(Role::Admin, 'admin.impor@example.test');

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Impor data dari Excel')
            ->assertSee('Unduh template');

        $response = $this->actingAs($admin)->post(route('settings.import'), [
            'excel' => $this->uploadedWorkbook(),
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHas('status');
        $response->assertSessionHas('import_result');
        $this->assertDatabaseHas('families', ['kkj_id' => 'TEST-001']);
        $this->assertDatabaseHas('members', ['ktj_id' => 'TEST-001-01']);
    }

    public function test_impor_fresh_tanpa_konfirmasi_ditolak(): void
    {
        $admin = $this->user(Role::Admin, 'admin.fresh@example.test');

        $response = $this->actingAs($admin)->post(route('settings.import'), [
            'excel' => $this->uploadedWorkbook(),
            'fresh' => '1',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHasErrors('confirm_fresh');
        $this->assertDatabaseCount('families', 0);
        $this->assertDatabaseCount('members', 0);
    }

    public function test_selain_admin_tidak_dapat_mengimpor(): void
    {
        $pengerja = $this->user(Role::Pengerja, 'pengerja.impor@example.test');

        $this->actingAs($pengerja)
            ->post(route('settings.import'), [
                'excel' => $this->uploadedWorkbook(),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('families', 0);
    }

    public function test_admin_dapat_mengunduh_template_excel(): void
    {
        $admin = $this->user(Role::Admin, 'admin.template@example.test');

        $response = $this->actingAs($admin)->get(route('settings.import.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $path = $this->temporaryFile();
        file_put_contents($path, $response->streamedContent());

        $spreadsheet = IOFactory::load($path);
        $this->assertSame(['KKJ', 'JEMAAT', 'PETUNJUK'], $spreadsheet->getSheetNames());
        $this->assertSame('KKJID', $spreadsheet->getSheetByName('KKJ')?->getCell('A1')->getValue());
        $this->assertSame('NAMA LENGKAP', $spreadsheet->getSheetByName('JEMAAT')?->getCell('C1')->getValue());
        $this->assertSame('TANGGAL BAPTIS', $spreadsheet->getSheetByName('JEMAAT')?->getCell('T1')->getValue());
        $this->assertSame('TEMPAT BAPTIS', $spreadsheet->getSheetByName('JEMAAT')?->getCell('U1')->getValue());
        $spreadsheet->disconnectWorksheets();
    }

    public function test_selain_admin_tidak_dapat_mengunduh_template(): void
    {
        $pengerja = $this->user(Role::Pengerja, 'pengerja.template@example.test');

        $this->actingAs($pengerja)
            ->get(route('settings.import.template'))
            ->assertForbidden();
    }

    private function workbook(bool $withMemberSheet = true): string
    {
        $spreadsheet = new Spreadsheet;
        $kkj = $spreadsheet->getActiveSheet();
        $kkj->setTitle('KKJ');
        $kkj->fromArray([
            [
                'KKJID', 'KEPALA KELUARGA', 'MASUK', 'SUKACITA', 'PROVINSI', 'KABUPATEN KOTA',
                'KECAMATAN', 'KELURAHAN', 'KODE POS', 'ALAMAT TINGGAL', 'PHOTO KELUARGA', 'QRCODE',
            ],
            [
                'TEST-001', 'Kepala Keluarga Uji', 2015, null, 'Jawa Barat', 'Kota Depok',
                'Pancoran Mas', 'Depok', '16431', 'Jl. Contoh No. 1', null, null,
            ],
        ], null, 'A1');

        if ($withMemberSheet) {
            $members = $spreadsheet->createSheet();
            $members->setTitle('JEMAAT');
            $members->fromArray([
                [
                    'KKJID', 'KTJID', 'NAMA LENGKAP', 'ALIAS', 'STATUS KELUARGA', 'ANAK KE',
                    'JENIS KELAMIN', 'NOMOR KTP', 'SELULER', 'WHATSAPP', 'EMAIL', 'TANGGAL LAHIR',
                    'STATUS PERKAWINAN', 'NAMA PASANGAN', 'PHOTO', 'PELAYANAN', 'KOMSEL', 'KETERANGAN', 'QRCODE',
                    'TANGGAL BAPTIS', 'TEMPAT BAPTIS',
                ],
                [
                    'TEST-001', 'TEST-001-01', 'Kepala Keluarga Uji', null, 'Kepala Keluarga', null,
                    'Pria', '3276010101010001', '081234567890', '081234567890', 'kepala@example.test',
                    '1985-04-17', 'Sudah Menikah', null, null, null, null, null, null,
                    '2005-12-25', 'Gereja Uji',
                ],
            ], null, 'A1');
        }

        $path = $this->temporaryFile();
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    private function uploadedWorkbook(): UploadedFile
    {
        $path = $this->workbook();

        return new UploadedFile(
            $path,
            'ORCCDATA-test.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function temporaryFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'orccdata-test-');

        if ($path === false) {
            throw new RuntimeException('Tidak dapat membuat file sementara.');
        }

        $this->temporaryFiles[] = $path;

        return $path;
    }

    private function user(Role $role, string $email): User
    {
        return User::query()->create([
            'name' => $role->label(),
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
