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
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('attendance', ['hadir', 'tidak_hadir', 'belum_konfirmasi'])->default('belum_konfirmasi');
            $table->tinyInteger('pax_count')->unsigned()->default(1);
            $table->text('message')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamps();

            $table->index('attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rsvps');
    }
};
