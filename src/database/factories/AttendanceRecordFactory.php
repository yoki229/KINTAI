<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceRecordFactory extends Factory
{
    protected $model = AttendanceRecord::class;

    public function definition()
    {
        return [
            'work_date' => $this->faker
                            ->dateTimeBetween('-2 months','now')
                            ->format('Y-m-d'),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'status'    => 'finished',
            'note'      => null,
        ];
    }
}
