<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('scheduled_date');
            $table->string('campus', 100);
            $table->text('activity');
            $table->string('maintenance_in_charge', 100)->nullable();
            $table->string('engineer_in_charge', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->string('month_year', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_schedules');
    }
}