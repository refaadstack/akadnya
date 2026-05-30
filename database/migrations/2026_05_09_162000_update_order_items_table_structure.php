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
        Schema::table('order_items', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['product_id']);

            // Make product_id nullable
            $table->foreignId('product_id')->nullable()->change();

            // Add new columns
            $table->string('item_type', 50)->after('product_id'); // 'template' or 'product'
            $table->unsignedBigInteger('item_id')->after('item_type');
            $table->string('name')->after('item_id');
            $table->decimal('price', 12, 2)->after('name');

            // Rename price_snapshot to match if exists, or drop it
            $table->dropColumn('price_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'item_id', 'name', 'price']);
            $table->decimal('price_snapshot', 12, 2)->after('product_id');
            $table->foreignId('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });
    }
};
