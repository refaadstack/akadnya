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
        Schema::create('invitation_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_section_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('sort_order')->unsigned();
            $table->boolean('is_visible')->default(true);
            $table->timestamp('created_at')->nullable();

            $table->unique(['invitation_id', 'template_section_id'], 'unique_section');
            $table->index('invitation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_sections');
    }
};
