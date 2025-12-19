<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    protected $fillable = [
        'attendance_record_id','user_id','requested_changes',
        'reason','status','processed_by','processed_at'
    ];

    protected $casts = [
        'requested_changes' => 'array',
    ];

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
