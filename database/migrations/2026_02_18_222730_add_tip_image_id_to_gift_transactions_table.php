<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_transactions', function (Blueprint $table) {

            // 既存環境でエラー回避のため存在チェック
            if (!Schema::hasColumn('gift_transactions', 'tip_image_id')) {

                $table->foreignId('tip_image_id')
                      ->after('room_id')
                      ->constrained('tip_images')
                      ->cascadeOnDelete();

            }
        });
    }

    public function down(): void
    {
        Schema::table('gift_transactions', function (Blueprint $table) {

            if (Schema::hasColumn('gift_transactions', 'tip_image_id')) {

                $table->dropForeign(['tip_image_id']);
                $table->dropColumn('tip_image_id');

            }
        });
    }
};
