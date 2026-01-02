<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceCorrectionSeeder extends Seeder
{
    public function run()
    {
        $userIds    = [2, 3, 4];
        $oneWeekAgo = now()->subDays(7)->startOfDay();
        $today      = now()->endOfDay();

        //テスト用の人3分の申請ダミーデータ
        foreach ($userIds as $userId) {

            $attendance = AttendanceRecord::where('user_id', $userId)
                ->whereBetween('work_date', [$oneWeekAgo, $today])
                ->inRandomOrder()
                ->first();

            if (!$attendance) {
                continue;
            }

            AttendanceCorrection::create([
                'attendance_record_id'  => $attendance->id,
                'user_id'               => $userId,

                'requested_changes'     => [
                    'clock_in'  => '10:00',
                    'clock_out' => '19:00',
                    'breaks'    => [
                        [
                            'break_start'   => '13:00',
                            'break_end'     => '14:00',
                        ],
                    ],
                ],

                'reason'                => '遅延のため',
                'status'                => AttendanceCorrection::STATUS_PENDING,
                'processed_by'          => null,
                'processed_at'          => null,
            ]);
        }
    }
}
