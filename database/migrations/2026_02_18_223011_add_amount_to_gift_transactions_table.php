<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_transactions', function (Blueprint $table) {

            if (!Schema::hasColumn('gift_transactions', 'amount')) {
                $table->bigInteger('amount')->after('tip_image_id');
            }

        });
    }

    public function down(): void
    {
        Schema::table('gift_transactions', function (Blueprint $table) {

            if (Schema::hasColumn('gift_transactions', 'amount')) {
                $table->dropColumn('amount');
            }

        });
    }
};
