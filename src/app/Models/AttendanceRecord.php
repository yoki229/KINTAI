<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','work_date','clock_in','clock_out','status','note'
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'off_work' => '勤務外',
            'working'  => '出勤中',
            'on_break' => '休憩中',
            'finished' => '退勤済',
            default    => '不明',
        };
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakRecord::class);
    }

    // スコープ：特定月のレコード
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('work_date', $year)
                    ->whereMonth('work_date', $month);
    }

    // スコープ：ユーザーの特定日
    public function scopeForUserDate($query, $userId, $workDate)
    {
        return $query->where('user_id', $userId)->where('work_date', $workDate);
    }

    // アクセサ：出勤合計時間（分）
    public function getWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) return 0;

        $total = Carbon::parse($this->clock_in)
            ->diffInMinutes(Carbon::parse($this->clock_out));

        return max(0, $total - $this->break_minutes);
    }

    // アクセサ：出勤合計時間 HH:MM 表示
    public function getWorkTimeFormattedAttribute()
    {
        $work_minutes = $this->work_minutes;
        $hour = intdiv($work_minutes, 60);
        $mins = $work_minutes % 60;
        return $hour . ':' . sprintf('%02d', $mins);
    }

    // アクセサ：休憩合計時間（分）
    public function getBreakMinutesAttribute()
    {
        return $this->breaks->sum(function ($break) {
            if (!$break->break_end) {
                return 0;
            }

            return Carbon::parse($break->break_start)
            ->diffInMinutes(Carbon::parse($break->break_end));
        });
    }

    // アクセサ：休憩合計時間 HH:MM 表示
    public function getBreakTimeFormattedAttribute()
    {
        $break_minutes = $this->break_minutes;

        $hour = intdiv($break_minutes, 60);
        $mins = $break_minutes % 60;

        return $hour . ':' . sprintf('%02d', $mins);
    }

    // アクセサ：出勤時間 HH:MM 表示
    public function getClockInFormattedAttribute()
    {
        return $this->clock_in ? $this->clock_in->format('H:i') : '-';
    }

    // アクセサ：退勤時間 HH:MM 表示
    public function getClockOutFormattedAttribute()
    {
        return $this->clock_out ? $this->clock_out->format('H:i') : '-';
    }
}
