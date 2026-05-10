<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionReportsTable extends Migration
{
    public function up()
    {
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_request_id');
            $table->string('report_number')->unique();
            $table->dateTime('scheduled_date');
            $table->text('inspection_notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->dateTime('actual_inspection_date')->nullable();
            $table->unsignedBigInteger('inspected_by')->nullable();
            $table->timestamps();
            
            $table->foreign('work_request_id')->references('id')->on('work_requests')->onDelete('cascade');
            $table->foreign('inspected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inspection_reports');
    }
}