<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DataResetService
{
    /**
     * Hapus seluruh data KKJ dan jemaat, termasuk foto dan urutan nomor KKJ.
     *
     * @return array{families: int, members: int, photos: int}
     */
    public function reset(): array
    {
        $familyCount = Family::query()->count();
        $memberCount = Member::query()->count();
        $photoCount = count($this->photoFiles('family-photos'))
            + count($this->photoFiles('member-photos'));

        DB::transaction(function (): void {
            DB::table('users')->whereNotNull('member_id')->update(['member_id' => null]);

            Member::query()->delete();
            Family::query()->delete();
            DB::table('kkj_sequences')->delete();
        });

        try {
            Storage::disk('public')->deleteDirectory('family-photos');
            Storage::disk('public')->deleteDirectory('member-photos');
        } catch (Throwable $exception) {
            report($exception);
        }

        return [
            'families' => $familyCount,
            'members' => $memberCount,
            'photos' => $photoCount,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function photoFiles(string $directory): array
    {
        $disk = Storage::disk('public');

        return $disk->directoryExists($directory) ? $disk->allFiles($directory) : [];
    }
}
