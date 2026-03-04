<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avatars', function (Blueprint $table) {
            $table->id();

            // 表示名（管理画面・選択画面用）
            $table->string('name');

            // VRMファイルパス（DBには vrm/xxx.vrm のみ）
            $table->string('vrm_path')
                  ->comment('例: vrm/xxx.vrm');

            // サムネイル（任意）
            $table->string('thumbnail_path')
                  ->nullable()
                  ->comment('例: vrm/thumbs/xxx.png');

            // 将来拡張用
            $table->boolean('is_active')
                  ->default(true)
                  ->comment('選択可能フラグ');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avatars');
    }
};
