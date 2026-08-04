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
            $table->string('cover_name_display', 20)->default('full')->after('groom_mother');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->dropColumn('cover_name_display');
        });
    }
};
