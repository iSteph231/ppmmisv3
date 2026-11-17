<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddApprovedStatusToInspectionReportsTable extends Migration
{
    public function up()
    {

        DB::statement("ALTER TABLE inspection_reports MODIFY COLUMN status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE inspection_reports MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending'");
    }
}
