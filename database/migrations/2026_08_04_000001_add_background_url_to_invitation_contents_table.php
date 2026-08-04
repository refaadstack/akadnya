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
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->string('background_url', 500)->nullable()->after('cover_photo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->dropColumn('background_url');
        });
    }
};
