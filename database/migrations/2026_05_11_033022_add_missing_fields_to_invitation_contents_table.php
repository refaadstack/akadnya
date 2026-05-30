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
            // Foto mempelai
            $table->string('bride_photo_url', 500)->nullable()->after('bride_mother');
            $table->string('groom_photo_url', 500)->nullable()->after('groom_mother');
            
            // Gallery photos (JSON array)
            $table->json('gallery_photos')->nullable()->after('music_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->dropColumn(['bride_photo_url', 'groom_photo_url', 'gallery_photos']);
        });
    }
};
