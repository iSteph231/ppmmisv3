<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE work_requests MODIFY COLUMN work_type ENUM('ocular_inspection', 'installation', 'repair', 'replacement', 'others') NULL");

        DB::table('work_requests')
            ->where('work_type', 'ocular')
            ->update(['work_type' => 'ocular_inspection']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('work_requests')
            ->where('work_type', 'ocular_inspection')
            ->update(['work_type' => 'ocular']);

        DB::statement("ALTER TABLE work_requests MODIFY COLUMN work_type ENUM('ocular', 'installation', 'repair', 'replacement', 'others') NULL");
    }
};
