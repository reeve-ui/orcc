<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('church_code', 5)->nullable();
            $table->string('name', 150);
            $table->string('tagline', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('logo_path')->nullable();
            $table->text('card_note')->nullable();
            $table->string('card_signature_title', 100)->nullable();
            $table->timestamps();
        });

        DB::table('church_settings')->insert([
            'church_code' => config('church.code'),
            'name' => config('church.name', 'Sekretariat Gereja'),
            'tagline' => config('church.tagline'),
            'address' => config('church.address'),
            'phone' => config('church.phone'),
            'email' => config('church.email'),
            'card_note' => config('church.card.note'),
            'card_signature_title' => config('church.card.signature_title'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('church_settings');
    }
};
