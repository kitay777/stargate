<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficialLive2DAvatarSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('avatars')->insert([
            'user_id' => null,
            'name' => 'Robot Official Live2D',
            'type' => 'live2d',
            'role' => 'official',
            'sort' => 1,
            'is_active' => 1,
            'vrm_path' => null,
            'apng_path' => null,
            'thumbnail_path' => 'live2d/thumb_robot.png',
            'width' => 640,
            'height' => 480,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}