<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rsvps MODIFY attendance VARCHAR(32) NOT NULL DEFAULT \'pending\'');

        DB::table('rsvps')
            ->where('attendance', 'hadir')
            ->update(['attendance' => 'yes']);

        DB::table('rsvps')
            ->where('attendance', 'tidak_hadir')
            ->update(['attendance' => 'no']);

        DB::table('rsvps')
            ->where('attendance', 'belum_konfirmasi')
            ->update(['attendance' => 'pending']);

        DB::statement("ALTER TABLE rsvps MODIFY attendance ENUM('yes', 'no', 'pending') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rsvps MODIFY attendance VARCHAR(32) NOT NULL DEFAULT \'belum_konfirmasi\'');

        DB::table('rsvps')
            ->where('attendance', 'yes')
            ->update(['attendance' => 'hadir']);

        DB::table('rsvps')
            ->where('attendance', 'no')
            ->update(['attendance' => 'tidak_hadir']);

        DB::table('rsvps')
            ->where('attendance', 'pending')
            ->update(['attendance' => 'belum_konfirmasi']);

        DB::statement("ALTER TABLE rsvps MODIFY attendance ENUM('hadir', 'tidak_hadir', 'belum_konfirmasi') NOT NULL DEFAULT 'belum_konfirmasi'");
    }
};
