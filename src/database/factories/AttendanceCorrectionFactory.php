<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {

        return [
            'attendance_record_id'  => AttendanceRecord::factory(),
            'status'                => AttendanceCorrection::STATUS_PENDING,
            'requested_changes'    => [
                'clock_in'  => '10:00',
                'clock_out' => '19:00',
            ],
            'reason'                => '遅延のため',
        ];
    }
}
