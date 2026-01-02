<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        //テストログイン用の人1分の管理者テストダミーデータ
        User::create([
            'id'                => 1,
            'name'              => '佐藤　花子',
            'email'             => 'hanako.s@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => null,
            'role'              => 'admin',
        ]);

        //テストログイン用の6人分の一般テストダミーデータ
        User::create([
            'id'                => 2,
            'name'              => '西　伶奈',
            'email'             => 'reina.n@coachtech.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'user',
        ]);

        User::create([
            'id'                => 3,
            'name'              => '山田　太郎',
            'email'             => 'taro.y@coachtech.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'user',
        ]);

        User::create([
            'id'                => 4,
            'name'              => '増田　一世',
            'email'             => 'issei.m@coachtech.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'user',
        ]);

        User::create([
            'id'                => 5,
            'name'              => '山本　敬吉',
            'email'             => 'keikichi.y@coachtech.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'user',
        ]);

        User::create([
            'id'                => 6,
            'name'              => '秋田　朋美',
            'email'             => 'tomomi.a@coachtech.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'user',
        ]);

        User::create([
            'id'                => 7,
            'name'              => '中西　教夫',
            'email'             => 'norio.n@coachtech.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
            'role'              => 'user',
        ]);

    }
}
