<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->datetime('scheduled_date')->nullable()->after('admin_notes');
            $table->text('inspection_notes')->nullable()->after('scheduled_date');
        });
    }

    public function down()
    {
        Schema::table('work_requests', function (Blueprint $table) {
            $table->dropColumn(['scheduled_date', 'inspection_notes']);
        });
    }
};