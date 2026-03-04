<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Agency;
use App\Models\AgencyUser;
use App\Models\AgencyUserRole;
use App\Models\User;

class AgencySeeder extends Seeder
{
    public function run(): void
    {
        // 代理店を作成
        $agency = Agency::create([
            'name' => '株式会社スター代理店',
            'contact_person' => '山田 太郎',
            'contact_email' => 'contact@agency.com',
            'notes' => 'テスト用の代理店です',
        ]);

        // 管理者ユーザー作成（ログイン可）


    }
}
