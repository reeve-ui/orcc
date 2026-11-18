<?php

namespace App\Services;

use App\Enums\Gender;
use App\Models\Family;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use Throwable;

class OrccdataImporter
{
    /** @var array<string, int> */
    private array $flags = [];

    /**
     * Impor workbook ORCCDATA (sheet KKJ dan JEMAAT).
     *
     * @return array{
     *     kkj_rows: int,
     *     member_rows: int,
     *     families_created: int,
     *     families_updated: int,
     *     members_created: int,
     *     members_updated: int,
     *     skipped: int,
     *     flags: array<string, int>,
     *     fresh: bool,
     *     dry_run: bool
     * }
     */
    public function import(string $path, bool $fresh = false, bool $dryRun = false): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("File tidak ditemukan: {$path}");
        }

        $this->flags = [];

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
        } catch (Throwable $exception) {
            throw new RuntimeException('Gagal membaca file Excel: '.$exception->getMessage(), 0, $exception);
        }

        $kkjSheet = $spreadsheet->getSheetByName('KKJ');
        $memberSheet = $spreadsheet->getSheetByName('JEMAAT');

        if (! $kkjSheet || ! $memberSheet) {
            throw new RuntimeException('Sheet KKJ atau JEMAAT tidak ditemukan. Pastikan nama sheet sesuai.');
        }

        $kkjRows = $this->rows($kkjSheet->toArray(null, true, false, false));
        $memberRows = $this->rows($memberSheet->toArray(null, true, false, false));

        $spreadsheet->disconnectWorksheets();

        $stats = [
            'families_created' => 0,
            'families_updated' => 0,
            'members_created' => 0,
            'members_updated' => 0,
            'skipped' => 0,
        ];

        DB::beginTransaction();

        try {
            if ($fresh) {
                Member::query()->delete();
                Family::query()->delete();
            }

            foreach ($kkjRows as $row) {
                $kkjId = $this->text($row['KKJID'] ?? null);

                if (! $kkjId) {
                    $stats['skipped']++;

                    continue;
                }

                $family = Family::query()->firstOrNew(['kkj_id' => $kkjId]);
                $isNew = ! $family->exists;

                $values = [
                    'head_name' => $this->text($row['KEPALA KELUARGA'] ?? null) ?: 'Keluarga '.$kkjId,
                    'joined_year' => $this->year($row['MASUK'] ?? null),
                    'joy_number' => $this->text($row['SUKACITA'] ?? null),
                    'province' => $this->text($row['PROVINSI'] ?? null),
                    'city' => $this->text($row['KABUPATEN KOTA'] ?? null),
                    'district' => $this->text($row['KECAMATAN'] ?? null),
                    'village' => $this->text($row['KELURAHAN'] ?? null),
                    'postal_code' => $this->text($row['KODE POS'] ?? null),
                    'address' => $this->text($row['ALAMAT TINGGAL'] ?? null),
                    'photo_path' => $this->text($row['PHOTO KELUARGA'] ?? null),
                ];

                $qr = $this->text($row['QRCODE'] ?? null);
                if ($qr && str_contains($qr, 'http')) {
                    $values['qr_payload'] = $qr;
                }

                $this->applyValues($family, $values);
                $family->legacy_raw = $this->legacy($row);
                $family->save();

                $isNew ? $stats['families_created']++ : $stats['families_updated']++;
            }

            foreach ($memberRows as $row) {
                $kkjId = $this->text($row['KKJID'] ?? null);
                $fullName = $this->text($row['NAMA LENGKAP'] ?? null);

                if (! $kkjId || ! $fullName) {
                    $stats['skipped']++;

                    continue;
                }

                $family = Family::query()->firstOrCreate(
                    ['kkj_id' => $kkjId],
                    ['head_name' => 'Keluarga '.$kkjId]
                );

                $rowFlags = [];
                [$gender, $genderFlag] = $this->gender($row);

                if ($genderFlag) {
                    $rowFlags[] = $genderFlag;
                }

                $status = $this->text($row['STATUS KELUARGA'] ?? null) ?: 'Anggota Keluarga';
                if (! in_array($status, Member::FAMILY_STATUSES, true)) {
                    $rowFlags[] = 'status keluarga tidak dikenali';
                    $status = 'Anggota Keluarga';
                }

                $marital = $this->text($row['STATUS PERKAWINAN'] ?? null);

                if ($marital === 'Sudah Menikah') {
                    $marital = Member::MARITAL_STATUS_MARRIED;
                }

                if ($marital && ! in_array($marital, Member::MARITAL_STATUSES, true)) {
                    $rowFlags[] = 'status pernikahan tidak dikenali';
                    $marital = null;
                }

                $nikRaw = $this->text($row['NOMOR KTP'] ?? null);
                $nik = $nikRaw && preg_match('/^\d{16}$/', $nikRaw) ? $nikRaw : null;
                if ($nikRaw && ! $nik) {
                    $rowFlags[] = 'NIK bukan 16 digit';
                }

                $phoneRaw = $this->text($row['SELULER'] ?? null);
                $phone = $this->phone($phoneRaw);
                if ($phoneRaw && ! $phone) {
                    $rowFlags[] = 'nomor seluler tidak valid';
                }

                $waRaw = $this->text($row['WHATSAPP'] ?? null);
                $whatsapp = $this->phone($waRaw);
                if ($waRaw && ! $whatsapp) {
                    $rowFlags[] = 'nomor WhatsApp tidak valid';
                }

                $emailRaw = $this->text($row['EMAIL'] ?? null);
                $email = $emailRaw && filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? strtolower($emailRaw) : null;
                if ($emailRaw && ! $email) {
                    $rowFlags[] = 'email tidak valid';
                }

                $birthRaw = $row['TANGGAL LAHIR'] ?? null;
                $birthDate = $this->dateValue($birthRaw);
                if ($this->text($birthRaw) && ! $birthDate) {
                    $rowFlags[] = 'tanggal lahir tidak valid';
                }

                $baptismRaw = $row['TANGGAL BAPTIS'] ?? null;
                $baptismDate = $this->dateValue($baptismRaw);
                if ($this->text($baptismRaw) && ! $baptismDate) {
                    $rowFlags[] = 'tanggal baptis tidak valid';
                }

                $baptismPlace = $this->meaningfulText($row['TEMPAT BAPTIS'] ?? null, $rowFlags, 'tempat baptis berupa kode angka');

                $spouse = $this->meaningfulText($row['NAMA PASANGAN'] ?? null, $rowFlags, 'nama pasangan berupa kode angka');
                $ministry = $this->meaningfulText($row['PELAYANAN'] ?? null, $rowFlags, 'pelayanan berupa kode angka');

                // KOMSEL dipertahankan apa adanya karena akan dihubungkan dengan aplikasi lain.
                $cellGroup = $this->text($row['KOMSEL'] ?? null);
                if ($cellGroup !== null && preg_match('/^\d{1,6}$/', $cellGroup)) {
                    $rowFlags[] = 'komsel berupa kode angka';
                }

                $notes = $this->meaningfulText($row['KETERANGAN'] ?? null, $rowFlags, 'keterangan berupa kode angka');

                $ktjId = $this->text($row['KTJID'] ?? null);
                $childOrder = $this->childOrder($row['ANAK KE'] ?? null);

                $member = $ktjId
                    ? Member::query()->firstOrNew(['ktj_id' => $ktjId])
                    : Member::query()->firstOrNew(['family_id' => $family->id, 'full_name' => $fullName]);
                $isNew = ! $member->exists;

                $values = [
                    'family_id' => $family->id,
                    'ktj_id' => $ktjId,
                    'family_status' => $status,
                    'child_order' => $childOrder,
                    'gender' => $gender,
                    'full_name' => $fullName,
                    'alias' => $this->text($row['ALIAS'] ?? null),
                    'nik' => $nik,
                    'phone' => $phone,
                    'whatsapp' => $whatsapp,
                    'email' => $email,
                    'birth_date' => $birthDate,
                    'baptism_date' => $baptismDate,
                    'baptism_place' => $baptismPlace,
                    'marital_status' => $marital,
                    'spouse_name' => $spouse,
                    'photo_path' => $this->text($row['PHOTO'] ?? null),
                    'ministry' => $ministry,
                    'cell_group' => $cellGroup,
                    'notes' => $notes,
                ];

                $qr = $this->text($row['QRCODE'] ?? null);
                if ($qr && str_contains($qr, 'http')) {
                    $values['qr_payload'] = $qr;
                }

                $this->applyValues($member, $values);
                $member->legacy_raw = $this->legacy($row);

                if ($isNew) {
                    $member->data_flags = array_values(array_unique($rowFlags));
                }

                $member->save();

                foreach (array_unique($rowFlags) as $flag) {
                    $this->flags[$flag] = ($this->flags[$flag] ?? 0) + 1;
                }

                $isNew ? $stats['members_created']++ : $stats['members_updated']++;
            }

            $this->fixPlaceholderFamilyNames();

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        arsort($this->flags);

        return [
            'kkj_rows' => count($kkjRows),
            'member_rows' => count($memberRows),
            ...$stats,
            'flags' => $this->flags,
            'fresh' => $fresh,
            'dry_run' => $dryRun,
        ];
    }

    /**
     * Mengisi nilai hanya jika record baru atau kolom tujuan masih kosong.
     *
     * @param  array<string, mixed>  $values
     */
    private function applyValues(Family|Member $model, array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (! $model->exists || blank($model->getAttribute($key))) {
                $model->setAttribute($key, $value);
            }
        }
    }

    /**
     * @param  array<int, mixed>  $sheetRows
     * @return array<int, array<string, mixed>>
     */
    private function rows(array $sheetRows): array
    {
        if ($sheetRows === []) {
            return [];
        }

        $headers = array_map(
            fn ($header) => strtoupper(trim((string) preg_replace('/\s+/', ' ', (string) $header))),
            array_shift($sheetRows) ?? []
        );

        $rows = [];

        foreach ($sheetRows as $sheetRow) {
            if (! is_array($sheetRow)) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }
                $row[$header] = $sheetRow[$index] ?? null;
            }

            $hasValue = collect($row)->contains(fn ($value) => $value !== null && trim((string) $value) !== '');

            if ($hasValue) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function meaningfulText(mixed $value, array &$flags, string $flag): ?string
    {
        $text = $this->text($value);

        if ($text === null) {
            return null;
        }

        if (preg_match('/^\d{1,6}$/', $text)) {
            $flags[] = $flag;

            return null;
        }

        return $text;
    }

    private function year(mixed $value): ?int
    {
        $text = $this->text($value);

        if (! $text || ! preg_match('/^\d{1,4}$/', $text)) {
            return null;
        }

        $year = (int) $text;
        if ($year < 100) {
            $year += 2000;
        }

        if ($year < 1900 || $year > now()->year + 1) {
            return null;
        }

        return $year;
    }

    private function childOrder(mixed $value): ?int
    {
        $text = $this->text($value);

        if (! $text || ! preg_match('/^\d{1,2}$/', $text)) {
            return null;
        }

        $order = (int) $text;

        return $order >= 1 && $order <= 30 ? $order : null;
    }

    /**
     * @return array{0: Gender|null, 1: string|null}
     */
    private function gender(array $row): array
    {
        $gender = strtoupper(trim((string) ($row['JENIS KELAMIN'] ?? '')));
        $childCode = trim((string) ($row['ANAK KE'] ?? ''));

        if (in_array($gender, ['PRIA', 'LAKI-LAKI', 'LAKI LAKI', 'L'], true)) {
            return [Gender::Pria, null];
        }

        if (in_array($gender, ['WANITA', 'PEREMPUAN', 'P'], true)) {
            return [Gender::Wanita, null];
        }

        // Pada workbook lama, kode 668/681 pada kolom ANAK KE dipakai sebagai kode gender.
        if ($childCode === '668') {
            return [Gender::Pria, 'gender diturunkan dari kode 668'];
        }

        if ($childCode === '681') {
            return [Gender::Wanita, 'gender diturunkan dari kode 681'];
        }

        return [null, $gender !== '' ? 'jenis kelamin tidak dikenali' : null];
    }

    private function phone(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $clean = preg_replace('/[^0-9+]/', '', $value) ?? '';
        $digits = preg_replace('/\D/', '', $clean) ?? '';

        if (strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        return $clean;
    }

    private function dateValue(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $serial = (float) $value;

                if ($serial < 8000 || $serial > 80000) {
                    return null;
                }

                $date = Carbon::instance(ExcelDate::excelToDateTimeObject($serial))->startOfDay();
            } else {
                $date = Carbon::parse((string) $value)->startOfDay();
            }
        } catch (Throwable) {
            return null;
        }

        if ($date->year < 1900 || $date->isFuture() || $date->diffInYears(now()) > 110) {
            return null;
        }

        return $date;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function legacy(array $row): array
    {
        return array_map(
            fn ($value) => is_scalar($value) || $value === null ? $value : (string) $value,
            $row
        );
    }

    private function fixPlaceholderFamilyNames(): void
    {
        Family::query()
            ->where('head_name', 'like', 'Keluarga %')
            ->each(function (Family $family): void {
                $head = Member::query()
                    ->where('family_id', $family->id)
                    ->where('family_status', 'Kepala Keluarga')
                    ->orderBy('id')
                    ->first();

                if ($head) {
                    $family->forceFill(['head_name' => $head->full_name])->save();
                }
            });
    }
}
