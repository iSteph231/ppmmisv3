<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {

        DB::statement("ALTER TABLE work_requests MODIFY COLUMN status ENUM('pending', 'approved', 'completed') NOT NULL DEFAULT 'pending'");

        DB::table('work_requests')
            ->whereNotIn('status', ['pending', 'approved', 'completed'])
            ->update(['status' => 'pending']);
    }

    public function down()
    {

        DB::statement("ALTER TABLE work_requests MODIFY COLUMN status VARCHAR(255) DEFAULT 'pending'");
    }
};
