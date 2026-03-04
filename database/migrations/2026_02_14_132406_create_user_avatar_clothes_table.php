<?php

// database/migrations/xxxx_create_user_avatar_clothes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_avatar_clothes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('avatar_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('clothes_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            // 同一ユーザー×同一アバターは1着のみ
            $table->unique(['user_id', 'avatar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_avatar_clothes');
    }
};
