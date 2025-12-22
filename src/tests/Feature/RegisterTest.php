<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    //１.認証機能（一般ユーザー）（名前が未入力の場合、バリデーションメッセージが表示される）
    public function testNameIsRequired()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'hujitani@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    // １.認証機能（一般ユーザー）（メールアドレスが未入力の場合、バリデーションメッセージが表示される）
    public function testEmailIsRequired()
    {
        $response = $this->post('/register', [
            'name' => '藤谷次郎',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    // １.認証機能（一般ユーザー）（パスワードが8文字未満の場合、バリデーションメッセージが表示される）
    public function testPasswordMustBeMinimum8Characters()
    {
        $response = $this->post('/register', [
            'name' => '藤谷次郎',
            'email' => 'hujitani@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    // １.認証機能（一般ユーザー）（パスワードが一致しない場合、バリデーションメッセージが表示される）
    public function testPasswordAndConfirmationMustMatch()
    {
        $response = $this->post('/register', [
            'name' => '藤谷次郎',
            'email' => 'hujitani@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password321',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    // １.認証機能（一般ユーザー）（パスワードが未入力の場合、バリデーションメッセージが表示される）
    public function testPasswordIsRequired()
    {
        $response = $this->post('/register', [
            'name' => '藤谷次郎',
            'email' => 'hujitani@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    // １.認証機能（一般ユーザー）（フォームに内容が入力されていた場合、データが正常に保存される）
    public function testSuccessfulRegister()
    {
        $response = $this->post('/register', [
            'name' => '藤谷次郎',
            'email' => 'hujitani@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'email' => 'hujitani@example.com',
            'name'  => '藤谷次郎',
        ]);
    }
}
