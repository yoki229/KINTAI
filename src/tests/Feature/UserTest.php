<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ４.日時取得機能（現在の日時情報がUIと同じ形式で出力されている）
    // 「Laravelの Feature テストだけでは「JSで表示された現在日時」を取得・検証することはできません。」ということで困っている
    public function testCurrentDateTimeDisplaysCorrectly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $now = Carbon::now();
        $weekdays = ['日','月','火','水','木','金','土'];
        $formatted = $now->format('Y年n月j日') . '(' . $weekdays[$now->dayOfWeek] . ') ' . $now->format('H:i');

        $response->assertSee($formatted);
    }

    // ５.ステータス確認機能（勤務外の場合、勤怠ステータスが正しく表示される）

    public function testStatusOffWorkDisplaysCorrectly()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'off_work',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    // ５.ステータス確認機能（出勤中の場合、勤怠ステータスが正しく表示される）
    public function testStatusWorkingDisplaysCorrectly()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'working',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    // ５.ステータス確認機能（休憩中の場合、勤怠ステータスが正しく表示される）
    public function testStatusOnBreakDisplaysCorrectly()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'on_break',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    // ５.ステータス確認機能（退勤済の場合、勤怠ステータスが正しく表示される）

    public function testStatusFinishedDisplaysCorrectly()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'finished',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    // ６.出勤機能（出勤ボタンが正しく機能する）
    public function testClockInButtonFunctionsCorrectly()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'off_work',
            'work_date' => today(),
            'clock_in'  => null,
        ]);

        $this->actingAs($user);

        // 勤怠画面表示
        $response = $this->get('/attendance');
        $response->assertSee('出勤');

        // 出勤処理
        $this->post('/attendance/clock-in');

        $record = AttendanceRecord::forUserDate($user->id, today())->first();
        $this->assertEquals('working', $record->status);
    }

    // ６.出勤機能（出勤は一日一回のみできる）
    public function testCannotClockInTwiceInOneDay()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'finished',
            'work_date' => today(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertDontSee('出勤');
    }

    // ６.出勤機能（出勤時刻が勤怠一覧画面で確認できる）
    public function testClockInTimeRecordedInAttendanceList()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'off_work',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $this->post('/attendance/clock-in');

        $response = $this->get('/attendance/list');
        $record = AttendanceRecord::forUserDate($user->id, today())->first();

        $response->assertSee($record->clock_in_formatted);
    }

    // ７.休憩機能（休憩ボタンが正しく機能する）
    public function testBreakInButtonFunctionsCorrectly()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'working',
            'work_date' => today(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/break-in');

        $record = AttendanceRecord::forUserDate($user->id, today())->first();
        $this->assertEquals('on_break', $record->status);
    }

    // ７.休憩機能（休憩は一日に何回でもできる）
    public function testBreakCanBeTakenMultipleTimes()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'working',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    // ７.休憩機能（休憩戻ボタンが正しく機能する）
    public function testBreakOutButtonFunctionsCorrectly()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'working',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');

        $record = AttendanceRecord::forUserDate($user->id, today())->first();
        $this->assertEquals('working', $record->status);
    }

    // ７.休憩機能（休憩戻は一日に何回でもできる）
    public function testBreakOutCanBeRepeated()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'working',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');
        $this->post('/attendance/break-in');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    // ７.休憩機能（休憩時刻が勤怠一覧画面で確認できる）
    public function testBreakTimeRecordedInAttendanceList()
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status'  => 'working',
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $this->post('/attendance/break-in');
        $this->post('/attendance/break-out');

        $response = $this->get('/attendance/list');
        $record = AttendanceRecord::forUserDate($user->id, today())->first();

        $record->refresh();
        foreach ($record->breaks as $break) {
            $this->assertNotNull($break->break_start);
            $this->assertNotNull($break->break_end);
            $response->assertSee($record->break_time_formatted);
        }
    }

    // ８.退勤機能（退勤ボタンが正しく機能する）
    public function testClockOutButtonWorksAndUpdatesStatus()
    {
        $user = User::factory()->create();

        // 出勤済みの勤怠を作成
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status' => 'working',  // 出勤中
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 退勤ボタン押下
        $response = $this->from('/attendance')
                        ->post('/attendance/clock-out');

        $response->assertStatus(302); // リダイレクト確認
        $response->assertRedirect('/attendance');

        // データベース上でステータスが退勤済になっているか確認
        $attendance->refresh();
        $this->assertEquals('finished', $attendance->status);
        $this->assertNotNull($attendance->clock_out);
    }

    // ８.退勤機能（退勤時刻が勤怠一覧画面で確認できる）
    public function testClockOutTimeAppearsInAttendanceList()
    {
        $user = User::factory()->create();

        // 勤怠レコード作成（出勤済み）
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'status' => 'working',
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 退勤処理
        $this->post('/attendance/clock-out');

        // 勤怠一覧取得
        $response = $this->get('/attendance/list');
        $attendance->refresh();

        $clockOutTime = $attendance->clock_out->format('H:i');

        // 退勤時刻が画面に表示されていることを確認
        $response->assertStatus(200);
        $response->assertSee($clockOutTime);
    }

    // ９. 勤怠一覧情報取得機能（一般ユーザー）（自分が行った勤怠情報が全て表示されている）
    public function testUserSeesAllOwnAttendanceRecords()
    {
        $user = User::factory()->create();

        // 自分の勤怠を3日分作成
        $ownAttendances = AttendanceRecord::factory()
            ->count(3)
            ->sequence(
                ['work_date' => now()->subDays(2)->toDateString()],
                ['work_date' => now()->subDays()->toDateString()],
                ['work_date' => now()->toDateString()],
            )
            ->create([
                'user_id' => $user->id,
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
            ]);

        $this->actingAs($user);
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 自分の勤怠の日付が表示されている
        foreach ($ownAttendances as $attendance) {
            $response->assertSee(
                $attendance->work_date->translatedFormat('m/d(D)')
            );
        }

        // 自分の打刻情報が反映されている
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    // ９. 勤怠一覧情報取得機能（一般ユーザー）（勤怠一覧画面に遷移した際に現在の月が表示される）
    public function testCurrentMonthIsDisplayedOnAttendanceList()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $currentMonth = Carbon::now()->format('Y/m');
        $response->assertSee($currentMonth);
    }

    // ９. 勤怠一覧情報取得機能（一般ユーザー）（「前月」を押下した時に表示月の前月の情報が表示される）
    public function testPreviousMonthAttendanceIsDisplayed()
    {
        $user = User::factory()->create();

        // 前月の勤怠
        $prevMonthDate = Carbon::now()->subMonth()->startOfMonth();
        $attendancePrev = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => $prevMonthDate,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list?month=' . $prevMonthDate->format('Y-m'));
        $response->assertStatus(200);
        $response->assertSee($attendancePrev->work_date->format('y/m'));
    }

    // ９. 勤怠一覧情報取得機能（一般ユーザー）（「翌月」を押下した時に表示月の前月の情報が表示される）
    public function testNextMonthAttendanceIsDisplayed()
    {
        $user = User::factory()->create();

        // 翌月の勤怠
        $nextMonthDate = Carbon::now()->addMonth()->startOfMonth();
        $attendanceNext = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonthDate,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list?month=' . $nextMonthDate->format('Y-m'));
        $response->assertStatus(200);
        $response->assertSee($attendanceNext->work_date->format('y/m'));
    }

    // ９. 勤怠一覧情報取得機能（一般ユーザー）（「詳細」を押下すると、その日の勤怠詳細画面に遷移する）
    public function testDetailButtonRedirectsToAttendanceDetail()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $this->actingAs($user);

        // 詳細画面へアクセス
        $response = $this->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
    }

    // 10. 勤怠詳細情報取得機能（一般ユーザー）（勤怠詳細画面の「名前」がログインユーザーの氏名になっている）
    public function testAttendanceDetailShowsUserName()
    {
        $user = User::factory()->create(['name' => '山田太郎']);
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    // 10. 勤怠詳細情報取得機能（一般ユーザー）（勤怠詳細画面の「日付」が選択した日付になっている）
    public function testAttendanceDetailShowsCorrectDate()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $this->actingAs($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));
    }

    // 10. 勤怠詳細情報取得機能（一般ユーザー）（「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している）
    public function testAttendanceDetailShowsClockInAndClockOut()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
            'clock_in'  => now()->setHour(9)->setMinute(0),
            'clock_out' => now()->setHour(18)->setMinute(0),
        ]);

        $this->actingAs($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    // 10. 勤怠詳細情報取得機能（一般ユーザー）（「休憩」にて記されている時間がログインユーザーの打刻と一致している）
    public function testAttendanceDetailShowsBreakTimes()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
        ]);

        $attendance->breaks()->create([
            'break_start' => now()->setHour(12)->setMinute(0),
            'break_end'   => now()->setHour(13)->setMinute(0),
        ]);

        $this->actingAs($user);
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);

        foreach ($attendance->breaks as $break) {
            $response->assertSee($break->break_start->format('H:i'));
            $response->assertSee($break->break_end->format('H:i'));
        }
    }

    // 11. 勤怠詳細情報修正機能（一般ユーザー）（出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される）
    public function testClockInAfterClockOutShowsError()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '17:00',
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/detail/{$attendance->id}/correction", [
            'clock_in'  => '18:00', // 退勤より後
            'clock_out' => '17:00',
            'note'      => 'テスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['clock_in' => '出勤時間が不適切な値です']);
    }

    // 11. 勤怠詳細情報修正機能（一般ユーザー）（休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される）
    public function testBreakStartAfterClockOutShowsError()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/detail/{$attendance->id}/correction", [
            'clock_out' => '17:00',
            'breaks' => [
                    [
                        'start' => '18:00', // 退勤後
                        'end'   => '18:30',
                    ],
                ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['breaks.0.start' => '休憩時間が不適切な値です']);
    }

    // 11．勤怠詳細情報修正機能（一般ユーザー）（休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される）
    public function testBreakEndAfterClockOutShowsError()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/detail/{$attendance->id}/correction", [
            'clock_out' => '17:00',
            'breaks' => [
                [
                    'start' => '16:00',
                    'end'   => '18:00',
                ],
            ],
            'note' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    // 11．勤怠詳細情報修正機能（一般ユーザー）(備考欄が未入力の場合のエラーメッセージが表示される)
    public function testEmptyNoteShowsError()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->post("/attendance/detail/{$attendance->id}/correction", [
            'note' => '',
        ]);

        $response->assertSessionHasErrors(['note' => '備考を記入してください']);
    }

    // 11．勤怠詳細情報修正機能（一般ユーザー）(「承認待ち」にログインユーザーが行った申請が全て表示されていること)
    public function testCorrectionListShowsPendingCorrections()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $this->post("/attendance/detail/{$attendance->id}/correction",[
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'note'      => '修正申請テスト',
        ]);

        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('修正申請テスト');
    }

    // 11．勤怠詳細情報修正機能（一般ユーザー）(「承認済み」に管理者が承認した修正申請が全て表示されている)
    public function testApprovedCorrectionsAreShownForUser()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id
        ]);

        $correction = AttendanceCorrection::create([
            'attendance_record_id'  => $attendance->id,
            'user_id'               => $user->id,
            'requested_changes'     =>
            [
                ['break_start' => '12:00', 'break_end' => '13:00'],
            ],
            'reason'                => '修正申請テスト',
            'status'                => AttendanceCorrection::STATUS_APPROVED,
        ]);

        $this->actingAs($user);
        $response = $this->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('修正申請テスト');
    }

    // 11．勤怠詳細情報修正機能（一般ユーザー）(各申請の「詳細」を押下すると勤怠詳細画面に遷移する)
    public function testCorrectionDetailButtonRedirectsToAttendanceDetail()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->post("/attendance/detail/{$attendance->id}/correction",[
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'note'      => '修正申請テスト',
        ]);

        $this->actingAs($user);
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
    }
}
