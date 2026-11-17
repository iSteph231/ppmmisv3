<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_requests', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description');

            $table->string('campus')->nullable();
            $table->string('department')->nullable();
            $table->string('building_name')->nullable();
            $table->string('office_room')->nullable();

            $table->enum('work_type', ['ocular', 'installation', 'repair', 'replacement', 'others'])->nullable();
            $table->string('ocular_details')->nullable();
            $table->string('installation_details')->nullable();
            $table->string('repair_details')->nullable();
            $table->string('replacement_details')->nullable();
            $table->string('others_details')->nullable();

            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');

            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requester_name')->nullable();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->string('request_number')->unique();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('work_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_requests');
    }
};
