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
        Schema::create('user_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('grant_type', 20);
            $table->string('item_slug', 100)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'grant_type', 'item_slug']);
            $table->index(['grant_type', 'item_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_grants');
    }
};
