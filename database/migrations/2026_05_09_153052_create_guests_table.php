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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('unique_code', 20)->unique();
            $table->tinyInteger('max_pax')->unsigned()->default(1);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('invitation_id');
            $table->index('unique_code');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
