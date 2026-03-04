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
        Schema::create('room_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('room_id')->index();
            $table->integer('user_id')->index();
            $table->string('message');
            $table->string('type')->default('text'); // text, image, video, audio
            $table->string('file_path')->nullable(); // For storing file paths for images, videos, etc.
            $table->string('thumbnail_path')->nullable(); // For storing thumbnail paths for images, videos, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_messages');
    }
};
