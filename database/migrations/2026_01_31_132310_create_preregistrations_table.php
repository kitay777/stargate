<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('preregistrations', function (Blueprint $table) {
            $table->id();
            $table->string('nickname', 60)->unique(); // ★ ニックネーム重複NG
            $table->string('email', 255)->unique();   // ★ メール重複NG
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preregistrations');
    }
};
