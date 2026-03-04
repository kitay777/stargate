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
        Schema::table('sales', function (Blueprint $table) {
            //
            $table->foreignId('auction_bid_id')->nullable()->after('product_id');
            $table->integer('fee')->default(0)->after('shipping_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            //
            $table->dropForeign(['auction_bid_id']);
            $table->dropColumn('auction_bid_id');
            $table->dropColumn('fee');
        });
    }
};
