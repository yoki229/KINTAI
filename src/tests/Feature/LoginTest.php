<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    //２.ログイン認証機能（一般ユーザー）（メールアドレスが未入力の場合、バリデーションメッセージが表示される）
    public function testEmailIsRequired()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    //２.ログイン認証機能（一般ユーザー）（パスワードが未入力の場合、バリデーションメッセージが表示される）
    public function testPasswordIsRequired()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    //２.ログイン認証機能（一般ユーザー）（登録内容と一致しない場合、バリデーションメッセージが表示される）
    public function testInvalidCredentialsShowError()
    {
        // ダミーユーザーを作成
        User::factory()->create([
            'email' => 'real@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 間違った情報でログインを試みる
        $response = $this->post('/login', [
            'email' => 'fake@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
        $this->assertGuest(); // ログインしていないことを確認
    }

    //３.ログイン認証機能（管理者）（メールアドレスが未入力の場合、バリデーションメッセージが表示される）
    public function testAdminEmailIsRequired()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    //３.ログイン認証機能（管理者）（パスワードが未入力の場合、バリデーションメッセージが表示される）
    public function testAdminPasswordIsRequired()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    //３.ログイン認証機能（管理者）（登録内容と一致しない場合、バリデーションメッセージが表示される）
    public function testAdminInvalidCredentialsShowError()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest();
    }
}
