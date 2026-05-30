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
        Schema::table('invitations', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['template_id']);
            
            // Make template_id nullable
            $table->foreignId('template_id')->nullable()->change();
            
            // Re-add the foreign key with SET NULL on delete
            $table->foreign('template_id')
                ->references('id')
                ->on('templates')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Drop the SET NULL foreign key
            $table->dropForeign(['template_id']);
            
            // Make template_id not nullable again
            $table->foreignId('template_id')->nullable(false)->change();
            
            // Re-add the original RESTRICT foreign key
            $table->foreign('template_id')
                ->references('id')
                ->on('templates')
                ->onDelete('restrict');
        });
    }
};

