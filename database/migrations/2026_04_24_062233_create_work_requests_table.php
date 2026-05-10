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
            
            // Basic request information
            $table->string('title');
            $table->text('description');
            
            // Location fields
            $table->string('campus')->nullable();
            $table->string('department')->nullable();
            $table->string('building_name')->nullable();
            $table->string('office_room')->nullable();
            
            // Work request type fields
            $table->enum('work_type', ['ocular', 'installation', 'repair', 'replacement', 'others'])->nullable();
            $table->string('ocular_details')->nullable();
            $table->string('installation_details')->nullable();
            $table->string('repair_details')->nullable();
            $table->string('replacement_details')->nullable();
            $table->string('others_details')->nullable();
            
            // Status fields (priority removed)
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            // Requester information
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requester_name')->nullable();
            
            // Assignment and completion
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Tracking
            $table->string('request_number')->unique();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
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