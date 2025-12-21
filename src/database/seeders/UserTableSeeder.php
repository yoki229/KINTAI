<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //テストログイン用の一人分の管理者テストダミーデータ
        User::create([
            'id'                => 1,
            'name'              => '山田 太郎',
            'email'             => 'yamada@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => null,
            'role'              => 'admin',
        ]);

        //テストログイン用の一人分の一般テストダミーデータ
        User::create([
            'id'                => 2,
            'name'              => '佐藤 花子',
            'email'             => 'satou@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'user',
        ]);

        User::factory()->admin()->count(3)->create();
        User::factory()->count(7)->create();
    }
}
