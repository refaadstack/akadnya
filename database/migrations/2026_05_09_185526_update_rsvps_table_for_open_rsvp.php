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
        Schema::table('rsvps', function (Blueprint $table) {
            // Add invitation_id to link RSVP directly to invitation
            $table->foreignId('invitation_id')->nullable()->after('id')->constrained()->onDelete('cascade');

            // Make guest_id nullable (RSVP can be from anyone, not just invited guests)
            $table->foreignId('guest_id')->nullable()->change();

            // Add name field for open RSVP
            $table->string('name')->nullable()->after('guest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->dropForeign(['invitation_id']);
            $table->dropColumn('invitation_id');
            $table->dropColumn('name');

            // Note: Cannot easily revert guest_id to non-nullable without data loss
        });
    }
};
