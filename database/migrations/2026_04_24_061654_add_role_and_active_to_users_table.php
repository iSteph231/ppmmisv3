<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'user', 'personnel'])->default('user')->after('email');
            }
            
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('is_active');
            }
            
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'department', 'phone_number']);
        });
    }
};