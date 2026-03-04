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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('auction_type', ['none', 'auction', 'reverse'])->default('none'); // ← auction種別
            $table->unsignedInteger('min_price')->nullable(); // ← auction時の最低金額
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropColumn('auction_type');
            $table->dropColumn('min_price');
        });
    }
};
