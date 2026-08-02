<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->string('bride_nickname', 255)->nullable()->after('bride_name');
            $table->string('groom_nickname', 255)->nullable()->after('groom_name');
        });
    }

    public function down(): void
    {
        Schema::table('invitation_contents', function (Blueprint $table) {
            $table->dropColumn(['bride_nickname', 'groom_nickname']);
        });
    }
};
