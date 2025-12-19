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

    // 出勤しているかどうか
    public function getHasWorkedAttribute()
    {
        return !is_null($this->clock_in) && !is_null($this->clock_out);
    }

    // アクセサ：出勤合計時間（分）
    public function getWorkMinutesAttribute()
    {
        if (!$this->has_worked) {
            return 0;
        }

        $total = $this->clock_in
        ->diffInMinutes($this->clock_out);

        return max(0, $total - $this->break_minutes);
    }

    // アクセサ：出勤合計時間 HH:MM 表示
    public function getWorkTimeFormattedAttribute()
    {
        if (!$this->has_worked) {
            return '';
        }

        $hour = intdiv($this->work_minutes, 60);
        $mins = $this->work_minutes % 60;

        return $hour . ':' . sprintf('%02d', $mins);
    }

    // アクセサ：休憩合計時間（分）
    public function getBreakMinutesAttribute()
    {
        if (!$this->has_worked) {
            return 0;
        }

        return $this->breaks->sum(function ($break) {
            if (!$break->break_end) {
                return 0;
            }

            return $break->break_start
                ->diffInMinutes($break->break_end);
        });
    }

    // アクセサ：休憩合計時間 HH:MM 表示
    public function getBreakTimeFormattedAttribute()
    {
        if (!$this->has_worked) {
            return '';
        }

        $hour = intdiv($this->break_minutes, 60);
        $mins = $this->break_minutes % 60;

        return $hour . ':' . sprintf('%02d', $mins);
    }

    // アクセサ：出勤時間 HH:MM 表示
    public function getClockInFormattedAttribute()
    {
        return $this->clock_in ? $this->clock_in->format('H:i') : '';
    }

    // アクセサ：退勤時間 HH:MM 表示
    public function getClockOutFormattedAttribute()
    {
        return $this->clock_out ? $this->clock_out->format('H:i') : '';
    }
}
