<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table): void {
            $table->string('verification_token', 64)->nullable()->unique()->after('kkj_id');
        });

        DB::table('families')
            ->select('id')
            ->orderBy('id')
            ->each(function (object $family): void {
                do {
                    $token = Str::lower(Str::random(32));
                } while (DB::table('families')->where('verification_token', $token)->exists());

                DB::table('families')->where('id', $family->id)->update([
                    'verification_token' => $token,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table): void {
            $table->dropUnique(['verification_token']);
            $table->dropColumn('verification_token');
        });
    }
};
