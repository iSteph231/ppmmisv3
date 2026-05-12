<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('maintenance_schedules', function (Blueprint $table) {
        $table->string('status')->default('pending')->after('scheduled_date');
        // Or use enum for specific statuses:
        // $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
    });
}

public function down()
{
    Schema::table('maintenance_schedules', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
};
