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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0); // Owner of the room
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->integer('type')->default(0); // 0: public, 1: private
            $table->integer('status')->default(0); // 0: inactive, 1: active
            $table->integer('is_deleted')->default(0); // 0: not deleted, 1: deleted
            $table->integer('is_blocked')->default(0); // 0: not blocked, 1: blocked
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
