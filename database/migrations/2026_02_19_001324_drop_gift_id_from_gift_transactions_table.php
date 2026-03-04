<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('gift_transactions', 'gift_id')) {
                $table->dropForeign(['gift_id']);
                $table->dropColumn('gift_id');
            }
        });
    }

    public function down(): void
    {
        //
    }
};
