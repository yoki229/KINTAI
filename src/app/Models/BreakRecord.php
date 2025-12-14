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

    public function attendance()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

}
