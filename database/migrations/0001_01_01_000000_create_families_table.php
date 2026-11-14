<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table): void {
            $table->id();
            $table->string('kkj_id', 40)->unique();
            $table->string('head_name', 150);
            $table->unsignedSmallInteger('joined_year')->nullable();
            $table->string('joy_number', 40)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('village', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('qr_payload')->nullable();
            $table->json('legacy_raw')->nullable();
            $table->timestamps();

            $table->index('head_name');
            $table->index('district');
            $table->index('joined_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
