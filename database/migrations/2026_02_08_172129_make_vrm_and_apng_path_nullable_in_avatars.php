<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->string('vrm_path')->nullable()->change();
            $table->string('apng_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->string('vrm_path')->nullable(false)->change();
            $table->string('apng_path')->nullable(false)->change();
        });
    }
};
