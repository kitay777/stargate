<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tip_images', function (Blueprint $table) {

            $table->id();

            // 表示名
            $table->string('name');

            // 画像パス
            $table->string('image_path');

            // サムネイル（任意）
            $table->string('thumbnail_path')->nullable();

            // 価格（ポイント）
            $table->unsignedBigInteger('price');

            // 表示順
            $table->integer('sort')->default(0);

            // 有効フラグ
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tip_images');
    }
};
