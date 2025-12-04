<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'user_id','work_date','clock_in','clock_out',
        'break_minutes','break2_minutes','status','note'
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
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
        $in = Carbon::parse($this->clock_in);
        $out = Carbon::parse($this->clock_out);
        $total = $out->diffInMinutes($in);
        $breaks = ($this->break_minutes ?? 0) + ($this->break2_minutes ?? 0);
        return max(0, $total - $breaks);
    }

    // アクセサ：HH:MM 表示
    public function getWorkTimeFormattedAttribute()
    {
        $work_minutes = $this->work_minutes;
        $hour = intdiv($work_minutes, 60);
        $mins = $work_minutes % 60;
        return sprintf('%02d:%02d', $hour, $mins);
    }
}
