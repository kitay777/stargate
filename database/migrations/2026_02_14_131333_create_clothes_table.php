<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clothes', function (Blueprint $table) {
            $table->id();

            // この服が対応するアバター
            $table->foreignId('avatar_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');              // 服名
            $table->string('file_path');         // glb / vrm パス
            $table->string('thumbnail')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clothes');
    }
};