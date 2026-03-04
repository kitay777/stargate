<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('avatars', function (Blueprint $table) {

            // アバター種別
            $table->enum('type', ['vrm', 'apng'])
                ->default('vrm')
                ->after('name');

            // APNG 用パス
            $table->string('apng_path')
                ->nullable()
                ->after('vrm_path');

            // 描画用（任意・将来用）
            $table->unsignedInteger('width')
                ->nullable()
                ->after('apng_path');

            $table->unsignedInteger('height')
                ->nullable()
                ->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'apng_path',
                'width',
                'height',
            ]);
        });
    }
};
