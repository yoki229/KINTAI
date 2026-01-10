<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrection;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    // 11．勤怠詳細情報修正機能（一般ユーザー）(修正申請処理が実行される)
    public function testAttendanceCorrectionRequestIsCreated()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $attendance = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        // 申請する
        $response = $this->post("/attendance/detail/{$attendance->id}/correction", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'note' => '勤務時間修正',
        ]);
        $response->assertSessionHasNoErrors();

        // ＤＢに保存されているのを確認
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        $correction = AttendanceCorrection::where(
            'attendance_record_id',
            $attendance->id
        )->firstOrFail();

        // 管理者ユーザーでログインし、確認する
        $this->actingAs($admin, 'admin');

        $response = $this->get("/stamp_correction_request/list");
        $response->assertStatus(200);
        $response->assertSee('勤務時間修正');

        $response = $this->get("/stamp_correction_request/approve/{$correction->id}");
        $response->assertStatus(200);
        $response->assertSee('勤務時間修正');
    }

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
        $this->actingAs($admin, 'admin');

        // 勤怠一覧画面にアクセス
        $response = $this->get('/admin/attendance/list?date=' . $today->format('Y-m-d'));
        $response->assertStatus(200);

        // ユーザー名と出勤・退勤時間（全角コロン）を確認
        foreach ([$attendance1, $attendance2] as $attendance) {
            $response->assertSee($attendance->user->name);
            $response->assertSee($attendance->clock_in->format('H:i'));
            $response->assertSee($attendance->clock_out->format('H:i'));
        }
    }

    // 12．勤怠一覧情報取得機能（管理者）(遷移した際に現在の日付が表示される)
    public function testAdminSeesCurrentDateOnAttendanceList()
    {
        $admin = User::factory()->admin()->create();
        $today = today();

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list?date=' . $today->format('Y-m-d'));
        $response->assertStatus(200);

        // 今日の日付が表示されているか確認
        $response->assertSee($today->format('Y年n月j日'));
    }

    // 12．勤怠一覧情報取得機能（管理者）(「前日」を押下した時に前の日の勤怠情報が表示される)
    public function testAdminSeesPreviousDayAttendance()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $today = today();
        $yesterday = $today->copy()->subDay();

        $attendanceYesterday = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'clock_in' => $yesterday->copy()->hour(9)->minute(0),
            'clock_out' => $yesterday->copy()->hour(17)->minute(0),
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list?day=' . $yesterday->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee($yesterday->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee($attendanceYesterday->clock_in->format('H:i'));
        $response->assertSee($attendanceYesterday->clock_out->format('H:i'));
    }

    // 12．勤怠一覧情報取得機能（管理者）(「翌日」を押下した時に次の日の勤怠情報が表示される)
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

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/attendance/list?day=' . $tomorrow->format('Y-m-d'));
        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y年n月j日'));
        $response->assertSee($user->name);
        $response->assertSee($attTomorrow->clock_in->format('H:i'));
        $response->assertSee($attTomorrow->clock_out->format('H:i'));
    }

    //13．勤怠詳細情報取得・修正機能（管理者）(勤怠詳細画面に表示されるデータが選択したものになっている)
    public function testAdminSeesCorrectAttendanceDetail()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
            'clock_in'  => today()->setTime(9, 0),
            'clock_out' => today()->setTime(18, 0),
            'note'      => 'テスト備考',
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('テスト備考');
    }

    //13．勤怠詳細情報取得・修正機能（管理者）(出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される)
    public function testAdminGetsErrorWhenClockInIsAfterClockOut()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->post("/admin/attendance/{$attendance->id}", [
            'clock_in'  => '18:00',
            'clock_out' => '09:00',
            'note'      => '修正理由',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    //13．勤怠詳細情報取得・修正機能（管理者）(休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される)
    public function testAdminGetsErrorWhenBreakStartIsAfterClockOut()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_out' => today()->setTime(18, 0),
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->post("/admin/attendance/{$attendance->id}", [
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'breaks'    => [
                ['start' => '19:00', 'end' => '19:30']
            ],
            'note'      => '修正理由',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    //13．勤怠詳細情報取得・修正機能（管理者）(休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される)
    public function testAdminGetsErrorWhenBreakEndIsAfterClockOut()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_out' => today()->setTime(18, 0),
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->post("/admin/attendance/{$attendance->id}", [
            'clock_in'     => '09:00',
            'clock_out'    => '18:00',
            'breaks'    => [
                ['start' => '17:30', 'end' => '18:30']
            ],
            'note'         => '修正理由',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    //13．勤怠詳細情報取得・修正機能（管理者）(備考欄が未入力の場合のエラーメッセージが表示される)
    public function testAdminGetsErrorWhenNoteIsEmpty()
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->post("/admin/attendance/{$attendance->id}", [
            'user_id' => $user->id,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'note'      => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    //14．ユーザー情報取得機能（管理者）(管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる)
    public function testAdminCanSeeAllUsersNameAndEmail()
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->count(3)->create();

        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/staff/list');

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    //14．ユーザー情報取得機能（管理者）(ユーザーの勤怠情報が正しく表示される)
    public function testAdminCanSeeSelectedUsersAttendanceList()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
            'clock_in'  => today()->setTime(9, 0),
            'clock_out' => today()->setTime(18, 0),
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    //14．ユーザー情報取得機能（管理者）(「前月」を押下した時に表示月の前月の情報が表示される)
    public function testAdminCanSeePreviousMonthAttendance()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();
        $previousMonth = today()->subMonth();

        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $previousMonth,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get(
            "/admin/attendance/staff/{$user->id}?month=" . $previousMonth->format('Y-m')
        );

        $response->assertStatus(200);
        $weekdays = ['日','月','火','水','木','金','土'];
        $response->assertSee($previousMonth->format('m/d') . '(' . $weekdays[$previousMonth->dayOfWeek] . ')');
    }

    //14．ユーザー情報取得機能（管理者）(「翌月」を押下した時に表示月の翌月の情報が表示される)
    public function testAdminCanSeeNextMonthAttendance()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();
        $nextMonth = today()->addMonth();

        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $nextMonth,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get(
            "/admin/attendance/staff/{$user->id}?month=" . $nextMonth->format('Y-m')
        );

        $response->assertStatus(200);
        $weekdays = ['日','月','火','水','木','金','土'];
        $response->assertSee($nextMonth->format('m/d') . '(' . $weekdays[$nextMonth->dayOfWeek] . ')');
    }


    //14．ユーザー情報取得機能（管理者）(「詳細」を押下すると、その日の勤怠詳細画面に遷移する)
    public function testAdminCanNavigateToAttendanceDetailFromList()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));
    }

    //15．勤怠情報修正機能（管理者）(承認待ちの修正申請が全て表示されている)
    public function testAdminSeesAllPendingCorrectionRequests()
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = AttendanceRecord::factory()->create(['user_id' => $user1->id]);
        $attendance2 = AttendanceRecord::factory()->create(['user_id' => $user2->id]);

        $pending1 = AttendanceCorrection::factory()->create([
            'attendance_record_id' => $attendance1->id,
            'user_id' => $user1->id,
            'status'  => AttendanceCorrection::STATUS_PENDING,
        ]);

        $pending2 = AttendanceCorrection::factory()->create([
            'attendance_record_id' => $attendance2->id,
            'user_id' => $user2->id,
            'status'  => AttendanceCorrection::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee($pending1->attendanceRecord->work_date->format('Y/m/d'));
        $response->assertSee($pending2->attendanceRecord->work_date->format('Y/m/d'));
    }

    //15．勤怠情報修正機能（管理者）(承認済みの修正申請が全て表示されている)
    public function testAdminSeesAllApprovedCorrectionRequests()
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = AttendanceRecord::factory()->create(['user_id' => $user1->id]);
        $attendance2 = AttendanceRecord::factory()->create(['user_id' => $user2->id]);

        $approved1 = AttendanceCorrection::factory()->create([
            'attendance_record_id' => $attendance1->id,
            'user_id' => $user1->id,
            'status'  => AttendanceCorrection::STATUS_APPROVED,
        ]);

        $approved2 = AttendanceCorrection::factory()->create([
            'attendance_record_id' => $attendance2->id,
            'user_id' => $user2->id,
            'status'  => AttendanceCorrection::STATUS_APPROVED,
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee($approved1->attendanceRecord->work_date->format('Y/m/d'));
        $response->assertSee($approved2->attendanceRecord->work_date->format('Y/m/d'));
    }

    //15．勤怠情報修正機能（管理者）(修正申請の詳細内容が正しく表示されている)
    public function testAdminSeesCorrectionRequestDetail()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $correction = AttendanceCorrection::factory()->create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_changes' => [
                'clock_in' => '09:00',
                'clock_out'=> '18:00',
                'note'     => '修正理由',
            ],
        ]);

        $this->actingAs($admin, 'admin');
        $response = $this->get('/stamp_correction_request/approve/' . $correction->id);

        $response->assertStatus(200);
        $response->assertSee($correction->requested_changes['clock_in']);
        $response->assertSee($correction->requested_changes['clock_out']);
        $response->assertSee($correction->requested_changes['note']);
    }

    //15．勤怠情報修正機能（管理者）(修正申請の承認処理が正しく行われる)
    public function testAdminApprovesCorrectionAndAttendanceIsUpdated()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->create();

        $attendance = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '08:00',
            'clock_out' => '17:00',
        ]);

        $correction = AttendanceCorrection::factory()->create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_changes' => [
                'clock_in' => '09:00',
                'clock_out'=> '18:00',
            ],
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post('/stamp_correction_request/approve/' . $correction->id);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_corrections', [
            'id'     => $correction->id,
            'status' => AttendanceCorrection::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'id'        => $attendance->id,
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }

}
