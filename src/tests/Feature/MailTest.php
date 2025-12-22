<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;

class MailTest extends TestCase
{
    use RefreshDatabase;

    //16.メール認証機能（会員登録後、認証メールが送信される）
    public function testRegistrationSendsVerificationEmail()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => '藤谷次郎',
            'email' => 'hujitani@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where(
            'email', 'hujitani@example.com')->first();

        $this->assertNotNull($user, 'ユーザーが登録されていません');
        $this->assertDatabaseHas('users', ['email' => 'hujitani@example.com']);

        Notification::assertSentTo(User::where(
            'email', 'hujitani@example.com')->first(),
            VerifyEmail::class
        );
    }

    //16.メール認証機能（メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する）
    public function testEmailCheckRedirectsToVerificationPageIfNotVerified()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);
        $response = $this->get('/email/check');
        $response->assertRedirect('/email');
    }

    //16.メール認証機能（メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する）
    public function testEmailVerificationCompletesAndRedirects()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        // 通知を明示的に送る
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        $response->assertRedirect('/attendance');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
