<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {

            if (!Schema::hasColumn('point_transactions', 'tip_image_id')) {
                $table->unsignedBigInteger('tip_image_id')->nullable()->after('room_id');
            }

            $table->foreign('tip_image_id')
                ->references('id')
                ->on('tip_images')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {

            $table->dropForeign(['tip_image_id']);
            $table->dropColumn('tip_image_id');
        });
    }
};
