<?php
// database/migrations/2024_01_01_000002_add_otp_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('is_active');
            }
            
            if (!Schema::hasColumn('users', 'otp')) {
                $table->string('otp')->nullable()->after('is_verified');
            }
            
            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'otp', 'otp_expires_at']);
        });
    }
};