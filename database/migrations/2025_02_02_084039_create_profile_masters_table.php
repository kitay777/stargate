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
        Schema::create('profile_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('type')->default(0)->comment('0:string 1:select');
            $table->string('select_value1')->nullable();
            $table->string('select_value2')->nullable();
            $table->string('select_value3')->nullable();
            $table->string('select_value4')->nullable();
            $table->string('select_value5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_masters');
    }
};
