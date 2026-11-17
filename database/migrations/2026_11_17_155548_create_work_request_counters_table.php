<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_request_counters', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->char('month', 6)->primary();
            $table->unsignedInteger('last_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_request_counters');
    }
};
