<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_record_id',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'break_start' => 'datetime:H:i',
        'break_end'   => 'datetime:H:i',
    ];

    public function attendance()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    // アクセサ：休憩開始 HH:MM（input用）
    public function getBreakStartFormattedAttribute()
    {
        return $this->break_start ? $this->break_start->format('H:i') : '';
    }

    // アクセサ：休憩終了 HH:MM（input用）
    public function getBreakEndFormattedAttribute()
    {
        return $this->break_end ? $this->break_end->format('H:i') : '';
    }

}
