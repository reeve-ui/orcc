<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('family_id')->constrained('families')->cascadeOnDelete();
            $table->string('ktj_id', 40)->nullable()->unique();
            $table->string('family_status', 30)->default('Anggota Keluarga');
            $table->unsignedTinyInteger('child_order')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('full_name', 150);
            $table->string('alias', 150)->nullable();
            $table->string('nik', 32)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('whatsapp', 25)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('spouse_name', 150)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('qr_payload')->nullable();
            $table->string('ministry', 150)->nullable();
            $table->string('cell_group', 150)->nullable();
            $table->text('notes')->nullable();
            $table->json('data_flags')->nullable();
            $table->json('legacy_raw')->nullable();
            $table->timestamps();

            $table->index(['family_id', 'family_status']);
            $table->index('full_name');
            $table->index('gender');
            $table->index('birth_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
