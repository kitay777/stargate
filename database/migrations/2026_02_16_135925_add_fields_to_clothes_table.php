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
        Schema::table('clothes', function (Blueprint $table) {
            $table->integer('price')->default(0)->after('thumbnail');
            $table->integer('sort_order')->default(0)->after('price');
            $table->boolean('is_visible')->default(true)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clothes', function (Blueprint $table) {
            //
            $table->dropColumn('price');
            $table->dropColumn('sort_order');
            $table->dropColumn('is_visible');
        });
    }
};
