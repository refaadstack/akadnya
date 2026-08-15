<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('payment_gateway_fee', 12, 2)->default(0)->after('subtotal_amount');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('payment_gateway_fee');
        });

        // Backfill: existing orders never charged fees, so their subtotal
        // equals the previously stored total.
        DB::table('orders')->update([
            'subtotal_amount' => DB::raw('total_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal_amount', 'payment_gateway_fee', 'tax_amount']);
        });
    }
};