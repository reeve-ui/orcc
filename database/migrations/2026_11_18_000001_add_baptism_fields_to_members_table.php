<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->date('baptism_date')->nullable()->after('birth_date');
            $table->string('baptism_place', 150)->nullable()->after('baptism_date');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropColumn(['baptism_date', 'baptism_place']);
        });
    }
};
