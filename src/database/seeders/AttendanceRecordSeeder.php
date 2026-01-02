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
        $users = User::where('role', 'user')->get();

        // 今日から過去3か月分
        $startDate = Carbon::today()->subMonths(3);
        $endDate   = Carbon::today();

        foreach ($users as $user) {

            $currentWeek = null;
            $weeklyHolidays = [];

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

                // 週が変わったら「休暇2日」を決め直す
                if ($currentWeek !== $date->weekOfYear) {
                    $currentWeek = $date->weekOfYear;

                    // その週の平日（月〜金）から2日ランダム
                    $weeklyHolidays = collect(range(1, 5))
                        ->random(2)
                        ->toArray();
                }

                // 休暇の日ならスキップ（勤怠データを作らない）
                if (in_array($date->dayOfWeekIso, $weeklyHolidays)) {
                    continue;
                }

                // ===== 勤務日の処理（ほぼそのまま） =====
                $attendance = AttendanceRecord::create([
                    'user_id'   => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in'  => '09:00',
                    'clock_out' => '18:00',
                    'status'    => 'finished',
                ]);

                // 休憩1
                $attendance->breaks()->create([
                    'break_start' => '12:00',
                    'break_end'   => '13:00',
                ]);

                // 休憩2（ランダム）
                if (rand(0, 1)) {
                    $attendance->breaks()->create([
                        'break_start' => '15:00',
                        'break_end'   => '15:30',
                    ]);
                }
            }
        }
    }
}
