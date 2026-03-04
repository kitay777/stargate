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
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('auction_bid_id')->nullable()->constrained('auction_bids')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->integer('shipping_fee')->default(0);
            $table->integer('fee')->default(0);
            $table->integer('quantity')->default(1);
            $table->enum('status', ['in_cart', 'ordered', 'cancelled'])->default('in_cart');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
