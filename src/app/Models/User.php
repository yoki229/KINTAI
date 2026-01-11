<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // リレーション
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function attendanceCorrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    // role分け
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }

    public function getIsUserAttribute()
    {
        return $this->role === 'user';
    }

    // 「今日」の勤怠を取得するリレーション
    public function todayAttendance()
    {
        return $this->hasOne(AttendanceRecord::class)
            ->whereDate('work_date', today());
    }

    // アクセサ：今日の勤怠ラベルを取得
    public function getAttendanceStatusAttribute()
    {
        return $this->todayAttendance?->status_label ?? '勤務外';
    }

    // 管理者として処理した申請を取得するなら使う(今回は未使用)
    public function processedCorrections()
    {
        return $this->hasMany(AttendanceCorrection::class, 'processed_by');
    }
}
