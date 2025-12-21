<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    // 12．勤怠一覧情報取得機能（管理者）(その日になされた全ユーザーの勤怠情報が正確に確認できる)
    public function testAdminSeesAllUsersAttendanceForToday()
    {
        // 管理者と一般ユーザー2名作成
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $today = today();

        // 勤怠を作成
        $attendance1 = AttendanceRecord::factory()->create([
            'user_id' => $user1->id,
            'work_date' => $today,
            'clock_in' => $today->copy()->hour(9)->minute(0),
            'clock_out' => $today->copy()->hour(17)->minute(0),
        ]);

        $attendance2 = AttendanceRecord::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $today,
            'clock_in' => $today->copy()->hour(10)->minute(0),
            'clock_out' => $today->copy()->hour(18)->minute(0),
        ]);

        // 管理者でログイン
        $this->actingAs($admin);

        // 勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list?date=' . $today->format('Y-m-d'));
        $response->assertStatus(200);

        // 今日の日付が表示されているか確認
        $response->assertSee($today->format('Y/m/d'));

        // ユーザー名と出勤・退勤時間（全角コロン）を確認
        foreach ([$attendance1, $attendance2] as $attendance) {
            $response->assertSee($attendance->user->name);
            $response->assertSee($attendance->clock_in->format('H:i'));
            $response->assertSee($attendance->clock_out->format('H:i'));
        }

    /** 12-2. 勤怠一覧画面に現在の日付が表示される */
    public function testAdminSeesCurrentDateOnAttendanceList()
    {
        $admin = User::factory()->admin()->create();
        $today = today();

        $this->actingAs($admin);
        $response = $this->get('/admin/attendance/list?date=' . $today->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee($today->format('Y年n月j日'));
    }

    /** 12-3. 「前日」ボタンで前日の日付の勤怠情報が表示される */
    public function testAdminSeesPreviousDayAttendance()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $today = today();
        $yesterday = $today->copy()->subDay();

        $attYesterday = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'clock_in' => $yesterday->copy()->hour(9)->minute(0),
            'clock_out' => $yesterday->copy()->hour(17)->minute(0),
        ]);

        $this->actingAs($admin);
        $response = $this->get('/admin/attendance/list?date=' . $yesterday->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee($attYesterday->clock_in->format('H:i'));
        $response->assertSee($attYesterday->clock_out->format('H:i'));
    }

    /** 12-4. 「翌日」ボタンで翌日の日付の勤怠情報が表示される */
    public function testAdminSeesNextDayAttendance()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $today = today();
        $tomorrow = $today->copy()->addDay();

        $attTomorrow = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'clock_in' => $tomorrow->copy()->hour(9)->minute(0),
            'clock_out' => $tomorrow->copy()->hour(17)->minute(0),
        ]);

        $this->actingAs($admin);
        $response = $this->get('/admin/attendance/list?date=' . $tomorrow->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee($attTomorrow->clock_in->format('H:i'));
        $response->assertSee($attTomorrow->clock_out->format('H:i'));
    }
}
