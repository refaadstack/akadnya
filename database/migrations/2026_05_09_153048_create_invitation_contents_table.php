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
        Schema::create('invitation_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->unique()->constrained()->onDelete('cascade');
            // Identitas
            $table->string('bride_name')->nullable();
            $table->string('groom_name')->nullable();
            $table->string('bride_father')->nullable();
            $table->string('bride_mother')->nullable();
            $table->string('groom_father')->nullable();
            $table->string('groom_mother')->nullable();
            // Akad
            $table->dateTime('akad_datetime')->nullable();
            $table->string('akad_venue', 500)->nullable();
            $table->string('akad_maps_url', 500)->nullable();
            // Resepsi
            $table->dateTime('reception_datetime')->nullable();
            $table->string('reception_venue', 500)->nullable();
            $table->string('reception_maps_url', 500)->nullable();
            // Media
            $table->string('cover_photo_url', 500)->nullable();
            $table->string('music_url', 500)->nullable();
            // Konten
            $table->text('love_story')->nullable();
            $table->text('special_message')->nullable();
            // Amplop Digital
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_name')->nullable();
            $table->string('qris_image_url', 500)->nullable();
            $table->string('gopay_number', 20)->nullable();
            $table->string('ovo_number', 20)->nullable();
            $table->string('dana_number', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_contents');
    }
};
