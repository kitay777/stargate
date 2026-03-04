<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();

            $table->string('name');                 // ギフト名
            $table->string('image_path');           // 画像パス
            $table->integer('price');               // 必要ポイント

            $table->boolean('is_active')->default(true); // 表示制御
            $table->integer('sort')->default(0);         // 並び順

            $table->timestamps();

            $table->index(['is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
