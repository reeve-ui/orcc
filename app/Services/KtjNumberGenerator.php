<?php

namespace App\Services;

use App\Enums\Gender;
use App\Models\Family;
use App\Models\Member;
use RuntimeException;

class KtjNumberGenerator
{
    /**
     * Format: {nomor KKJ 13 karakter}{kode anggota}{kode jenis kelamin}
     *
     * Kode anggota:
     * - Kepala Keluarga dan Pasangan: 0
     * - Anggota Keluarga: nomor anak (1-9)
     * - Status lain: nomor anak bila tersedia, selain itu 0
     *
     * Kode jenis kelamin: 0 untuk Pria, 1 untuk Wanita.
     */
    public function generate(
        Family $family,
        string $familyStatus,
        ?int $childOrder,
        Gender $gender,
        ?int $ignoreMemberId = null
    ): string {
        $kkjId = strtoupper(trim((string) $family->kkj_id));

        if (preg_match('/^[A-Z0-9]{13}$/', $kkjId) !== 1) {
            throw new RuntimeException('Nomor KKJ keluarga harus terdiri dari 13 karakter agar nomor KTJ dapat dibuat.');
        }

        $memberDigit = $this->memberDigit($familyStatus, $childOrder);
        $candidate = $kkjId.$memberDigit.$this->genderDigit($gender);

        $query = Member::query()->where('ktj_id', $candidate);

        if ($ignoreMemberId !== null) {
            $query->whereKeyNot($ignoreMemberId);
        }

        if ($query->exists()) {
            throw new RuntimeException("Nomor KTJ {$candidate} sudah dipakai. Periksa status keluarga, anak ke-, atau jenis kelamin.");
        }

        return $candidate;
    }

    private function memberDigit(string $familyStatus, ?int $childOrder): string
    {
        if (in_array($familyStatus, ['Kepala Keluarga', 'Pasangan'], true)) {
            return '0';
        }

        if ($familyStatus === Member::FAMILY_STATUS_ANGGOTA) {
            return $this->childOrderDigit($childOrder);
        }

        if ($childOrder !== null) {
            return $this->childOrderDigit($childOrder);
        }

        return '0';
    }

    private function childOrderDigit(?int $childOrder): string
    {
        if ($childOrder === null || $childOrder < 1 || $childOrder > 9) {
            throw new RuntimeException('Anak ke- harus diisi antara 1 dan 9 agar nomor KTJ tetap 15 karakter.');
        }

        return (string) $childOrder;
    }

    private function genderDigit(Gender $gender): string
    {
        return $gender === Gender::Wanita ? '1' : '0';
    }
}
