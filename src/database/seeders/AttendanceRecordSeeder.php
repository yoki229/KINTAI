<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

class AttendanceRecordSeeder extends Seeder
{
    public function run()
    {
        // userID2の1日～10日分の勤怠データ作成
        $userId = 2;

        for ($day = 1; $day <= 10; $day++) {

            $date = Carbon::create(2025, 12, $day);

            $attendance = AttendanceRecord::create([
                'user_id'       => $userId,
                'work_date'     => $date->toDateString(),
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'status'        => 'finished',
            ]);

            // 休憩
            $attendance->breaks()->create([
                'break_start'   => '12:00',
                'break_end'     => '13:00',
            ]);

            // 休憩2
            if ($day % 2 === 0) {
                $attendance->breaks()->create([
                    'break_start'   => '15:00',
                    'break_end'     => '15:30',
                ]);
            }
        }

        // Factory （上記userID2以外5人のuserに勤怠データを作成）
        $users = User::where('role', 'user')
                ->where('id', '!=', 2)
                ->inRandomOrder()
                ->limit(5)
                ->get();

        foreach ($users as $user) {
            $attendances = AttendanceRecord::factory()
                    ->count(rand(3, 7))
                    ->for($user)
                    ->create();

            foreach ($attendances as $attendance) {
                $attendance->breaks()->create([
                    'break_start' => '12:00',
                    'break_end'   => '13:00',
                ]);
            }
        }
    }
}
