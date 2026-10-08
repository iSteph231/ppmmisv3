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
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->time('requested_time')->nullable();
            $table->string('lead_person')->nullable();
            $table->string('contact_number', 50)->nullable();
            $table->text('participants')->nullable();
            $table->string('requested_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_requests', function (Blueprint $table) {
            $table->dropColumn(['requested_time', 'lead_person', 'contact_number', 'participants', 'requested_by']);
        });
    }
};
