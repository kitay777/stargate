<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            if (!Schema::hasColumn('avatars', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            if (Schema::hasColumn('avatars', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
