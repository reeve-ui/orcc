<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kkj_sequences', function (Blueprint $table): void {
            $table->unsignedSmallInteger('sequence_year')->primary();
            $table->unsignedSmallInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kkj_sequences');
    }
};
