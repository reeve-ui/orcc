<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_settings', function (Blueprint $table): void {
            $table->string('card_signature_name', 100)->nullable()->after('card_note');
        });
    }

    public function down(): void
    {
        Schema::table('church_settings', function (Blueprint $table): void {
            $table->dropColumn('card_signature_name');
        });
    }
};
