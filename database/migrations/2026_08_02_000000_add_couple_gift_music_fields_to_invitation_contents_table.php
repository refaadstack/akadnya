<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->string('couple_photo_url', 500)->nullable()->after('groom_photo_url');
            $table->string('music_title', 255)->nullable()->after('music_url');
            $table->text('gift_address')->nullable()->after('dana_number');
        });
    }

    public function down(): void
    {
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->dropColumn(['couple_photo_url', 'music_title', 'gift_address']);
        });
    }
};
