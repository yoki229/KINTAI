<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CorrectionController;

// 一般ユーザー
Route::middleware(['auth','verified'])->group(function () {
    // 勤怠登録画面（一般ユーザー）
    Route::get('/attendance', [AttendanceController::class,'index']);
    // 出勤・退勤・休憩
    Route::post('/attendance/clock-in', [AttendanceController::class,'clockIn']);
    Route::post('/attendance/break-in', [AttendanceController::class,'breakIn']);
    Route::post('/attendance/break-out', [AttendanceController::class,'breakOut']);
    Route::post('/attendance/clock-out', [AttendanceController::class,'clockOut']);
    // 勤怠一覧画面（一般ユーザー）
    Route::get('/attendance/list', [AttendanceController::class,'list']);
    // 勤怠一覧画面（一般ユーザー）月指定用のルート
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.month');
    // 勤怠詳細画面（一般ユーザー）
    Route::get('/attendance/detail/{id}', [AttendanceController::class,'detail']);
    // 勤怠詳細画面から申請（一般ユーザー）
    Route::post('/attendance/detail/{id}/Correction', [AttendanceController::class,'requestCorrection']);

    // 申請一覧（ユーザー側）
    Route::get('/stamp_correction_request/list', [AttendanceController::class,'myCorrection']);
});

// 管理者用
    // ログイン画面（管理者）
    Route::get('/admin/login', [AdminLoginController::class, 'adminLoginForm']);
    Route::post('/admin/login', [AdminLoginController::class, 'adminLoginPost']);
    Route::post('/admin/logout', [AdminLoginController::class, 'adminLogout'])->name('admin.logout');;

    Route::middleware(['admin'])->group(function () {
        // 勤怠一覧画面（管理者）
        Route::get('/admin/attendance/list', [AdminAttendanceController::class,'adminList']);
        // 勤怠詳細画面（管理者）
        Route::get('admin/attendance/{id}', [AdminAttendanceController::class,'adminAttendance']);
        // スタッフ一覧画面（管理者）
        Route::get('/admin/staff/list', [StaffController::class,'adminStaffList']);
        // スタッフ別勤怠一覧画面（管理者）
        Route::get('/admin/attendance/staff/{id}', [StaffController::class,'adminStaffDetail']);
        // 申請一覧画面（管理者）
        Route::get('/admin/stamp_correction_request/list', [CorrectionController::class,'adminCorrectionList']);
        // 修正申請承認画面（管理者）
        Route::get('/admin/stamp_correction_request/approve', [CorrectionController::class,'adminCorrection']);
    });

// メール認証画面の表示
Route::get('/email',[MailController::class, 'email'])->name('verification.notice');
// メール認証のリンクをクリックしたときの処理
Route::get('/email/{id}/{hash}',[MailController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
// 認証はこちらからをクリックしたときの処理
Route::get('/email/check',[MailController::class, 'emailCheck'])->name('verification.handle');
// メール再送信処理
Route::post('/email/resend',[MailController::class, 'resend'])->middleware(['throttle:6,1'])->name('verification.send');