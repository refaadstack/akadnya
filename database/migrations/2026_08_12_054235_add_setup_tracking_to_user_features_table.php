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
        Schema::table('user_features', function (Blueprint $table) {
            $table->string('setup_status', 30)->nullable()->index()->after('metadata');
            $table->text('setup_notes')->nullable()->after('setup_status');
            $table->timestamp('setup_updated_at')->nullable()->after('setup_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_features', function (Blueprint $table) {
            $table->dropColumn(['setup_status', 'setup_notes', 'setup_updated_at']);
        });
    }
};
