<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\AttendanceRecord;

class AttendanceController extends Controller
{
    // 勤怠登録画面（一般ユーザー）
    public function index()
    {
        $user = Auth::user();
        $today =Carbon::today();

        $attendance = AttendanceRecord::forUserDate($user->id, $today)->first();

        return view('attendance', compact('attendance'));
    }

    // 出勤
    public function clockIn()
    {
        $attendance = AttendanceRecord::firstOrCreate(
            [
                'user_id'   => Auth::id(),
                'work_date' => today(),
            ]
        );

        if ($attendance->clock_in) {
            return back();
        }

        $attendance->update([
            'clock_in' => now(),
            'status'   => 'working',
        ]);

        return redirect()->back();
    }

    // 休憩
    public function breakIn()
    {
        $attendance = AttendanceRecord::forUserDate(Auth::id(), today())->firstOrFail();

        $attendance->breaks()->create([
            'break_start' => now(),
        ]);

        $attendance->update(['status' => 'on_break']);

        return redirect()->back();
    }

    public function breakOut()
    {
        $attendance = AttendanceRecord::forUserDate(Auth::id(), today())->firstOrFail();

        $break = $attendance->breaks()->whereNull('break_end')->latest()->first();
        if (!$break) {
            return back();
        }

        $break->update([
            'break_end' => now(),
        ]);

        $attendance->update(['status' => 'working']);

        return redirect()->back();
    }

    // 退勤
    public function clockOut()
    {
        $attendance = AttendanceRecord::forUserDate(Auth::id(), today())->firstOrFail();

        $attendance->update([
            'clock_out' => now(),
            'status'    => 'finished',
        ]);

        return redirect()->back()->with('message', 'お疲れさまでした。');
    }

    // 勤怠一覧画面（一般ユーザー）
    public function list(Request $request)
    {
        $user = Auth::user();

        // 月表示（デフォルトは今月）
        $month = $request->input('month') ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();

        // 年・月
        $year = $month->year;
        $monthNumber = $month->month;

        //前月・翌月（リンク）
        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        // 表示用
        $currentMonth = $month->format('Y/m');
        $currentMonthInput = $month->format('Y-m');

        // 勤怠データ取得（モデルスコープ使用）
        $attendances = AttendanceRecord::forMonth($year, $monthNumber)
            ->where('user_id', $user->id)
            ->orderBy('work_date')
            ->with('breaks')
            ->get();

        return view('attendance_list', compact(
            'attendances', 'prevMonth', 'nextMonth', 'currentMonth', 'currentMonthInput'
        ));
    }

    // 勤怠詳細画面（一般ユーザー）
    public function detail()
    {
        return view('attendance_detail');
    }

    // 勤怠詳細画面から申請（一般ユーザー）
    public function requestCorrection()
    {
        return redirect()->back();
    }

    // 申請一覧（ユーザー側）
    public function myCorrection()
    {
        return view('stamp_correction_request_list');
    }
}
