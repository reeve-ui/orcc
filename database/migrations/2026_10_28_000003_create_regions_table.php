<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->id();
            $table->string('province', 100);
            $table->string('city', 100);
            $table->string('district', 100);
            $table->string('village', 100);
            $table->string('postal_code', 5);

            $table->index('province');
            $table->index(['province', 'city']);
            $table->index(['province', 'city', 'district']);
            $table->index(['province', 'city', 'district', 'village']);
            $table->unique(['province', 'city', 'district', 'village', 'postal_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
