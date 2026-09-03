<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->renameColumn('is_from_myakad', 'is_from_akadnya');
        });
    }

    public function down(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->renameColumn('is_from_akadnya', 'is_from_myakad');
        });
    }
};
