<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gift_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('receiver_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('room_id')
                  ->constrained('rooms')
                  ->cascadeOnDelete();

            $table->foreignId('gift_id')
                  ->constrained('gifts')
                  ->cascadeOnDelete();

            $table->integer('price'); // 送信時の価格スナップショット

            $table->timestamps();

            $table->index(['room_id']);
            $table->index(['sender_id']);
            $table->index(['receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_transactions');
    }
};
