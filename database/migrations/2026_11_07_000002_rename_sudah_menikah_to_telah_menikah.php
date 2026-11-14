<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('members')
            ->where('marital_status', 'Sudah Menikah')
            ->update(['marital_status' => 'Telah Menikah']);
    }

    public function down(): void
    {
        DB::table('members')
            ->where('marital_status', 'Telah Menikah')
            ->update(['marital_status' => 'Sudah Menikah']);
    }
};
