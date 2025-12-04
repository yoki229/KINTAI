<?php

use Illuminate\Support\Facades\Route;

// 一般ユーザー
Route::middleware(['auth'])->group(function () {
    // 勤怠登録画面（一般ユーザー）
    Route::get('/attendance', [AttendanceController::class,'index']);
    // 出勤・退勤・休憩
    Route::post('/attendance/clock-in', [AttendanceController::class,'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class,'clockOut']);
    // 勤怠一覧画面（一般ユーザー）
    Route::get('/attendance/list', [AttendanceController::class,'list']);
    // 勤怠詳細画面（一般ユーザー）
    Route::get('/attendance/detail/{id}', [AttendanceController::class,'detail']);
    // 勤怠詳細画面から申請（一般ユーザー）
    Route::post('/attendance/detail/{id}/Correction', [AttendanceController::class,'requestCorrection']);

    // 申請一覧（ユーザー側）
    Route::get('/stamp_correction_request/list', [AttendanceController::class,'myCorrection']);
});

// 管理者用
Route::middleware(['auth','is_admin'])->prefix('admin')->name('admin.')->group(function () {
    // ログイン画面（管理者）/admin/login

    // 勤怠一覧画面（管理者）/admin/attendance/list
    Route::get('/admin/attendance/list', [Admin\AttendanceController::class,'adminList']);
    // 勤怠詳細画面（管理者）admin/attendance/{id}

    // スタッフ一覧画面（管理者）/admin/staff/list

    // スタッフ別勤怠一覧画面（管理者）/admin/attendance/staff/{id}

    // 申請一覧画面（管理者）/stamp_correction_request/list

    // 修正申請承認画面（管理者）/stamp_correction_request/approve/{attendance_correct_request_id}


    Route::get('/attendances', [Admin\AttendanceController::class,'index']);
    Route::get('/staffs', [Admin\StaffController::class,'index']);
    Route::get('/corrections', [Admin\CorrectionController::class,'index']);
    Route::post('/corrections/{id}/approve', [Admin\CorrectionController::class,'approve']);
    Route::post('/corrections/{id}/reject', [Admin\CorrectionController::class,'reject']);
});
