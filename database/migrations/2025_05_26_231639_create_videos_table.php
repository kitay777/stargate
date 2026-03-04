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
Schema::create('videos', function (Blueprint $table) {
    $table->id();
    $table->string('original_name'); // 元のファイル名
    $table->string('file_path');     // 保存先パス
    $table->bigInteger('size');      // バイトサイズ
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
