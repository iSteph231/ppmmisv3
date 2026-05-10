<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // For MySQL 8.0+
        DB::statement("ALTER TABLE work_requests MODIFY COLUMN status ENUM('pending', 'approved', 'completed') NOT NULL DEFAULT 'pending'");
        
        // Fix any existing invalid values
        DB::table('work_requests')
            ->whereNotIn('status', ['pending', 'approved', 'completed'])
            ->update(['status' => 'pending']);
    }
    
    public function down()
    {
        // Revert if needed
        DB::statement("ALTER TABLE work_requests MODIFY COLUMN status VARCHAR(255) DEFAULT 'pending'");
    }
};