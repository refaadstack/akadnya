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
        Schema::table('template_ornaments', function (Blueprint $table) {
            // Change position from enum to string for more flexibility
            $table->string('position', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_ornaments', function (Blueprint $table) {
            $table->enum('position', ['top', 'bottom', 'between', 'overlay'])->change();
        });
    }
};
