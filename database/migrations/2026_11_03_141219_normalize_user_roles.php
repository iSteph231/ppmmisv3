<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNotIn('role', ['admin', 'user'])->update(['role' => 'user']);
    }

    /**
     * Converted roles cannot be restored without a record of their previous values.
     */
    public function down(): void {}
};
