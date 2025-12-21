<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use App\Models\AttendanceCorrection;
use App\Http\Requests\AttendanceCorrectionRequest;

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
        $month = $request->month ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();

        // 前月・翌月（リンク）
        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        // 前月・翌月（表示用）
        $currentMonth = $month->format('Y/m');
        $currentMonthInput = $month->format('Y-m');

        // 月初～月末
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // １か月分の勤怠を作成（勤怠レコードがなければ空のレコードを作る）
        $attendances = collect(CarbonPeriod::create($start, $end))
            ->map(function ($date) use($user) {
                return AttendanceRecord::firstOrCreate(
                    [
                        'user_id'   => $user->id,
                        'work_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'clock_in'  => null,
                        'clock_out' => null,
                    ]
                );
            });

        return view('attendance_list', compact(
            'attendances', 'prevMonth', 'nextMonth', 'currentMonth', 'currentMonthInput'
        ));
    }

    // 勤怠詳細画面（一般ユーザー）
    public function detail($id)
    {
        $attendance = AttendanceRecord::findOrFail($id);
        if ($attendance->user_id !== auth()->id()) {
            return redirect('/attendance/list')->with('error', '不正なアクセスです');
        }

        // 既存の休憩を取得
        $breaks = $attendance->breaks()->orderBy('id')->get();

        // 必ず2件になるように補完
        while ($breaks->count() < 2) {
            $breaks->push(new BreakRecord());
        }

        $isPending = $attendance->is_correction_pending;
        $changes = $attendance->latestCorrection?->requested_changes ?? [];

        return view('attendance_detail', compact('attendance', 'breaks', 'isPending', 'changes'));
    }

    // 勤怠詳細画面から申請（一般ユーザー）
    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = AttendanceRecord::with('breaks')->findOrFail($id);

        // 本人チェック
        if($attendance->user_id !== Auth::id()) {
            return redirect()->back()->with('error', '不正なアクセスです');
        }

        // 修正申請
        AttendanceCorrection::create([
            'attendance_record_id'  => $attendance->id,
            'user_id'               => Auth::id(),
            'requested_changes'    => [
                'clock_in'     => $request->clock_in,
                'clock_out'    => $request->clock_out,
                'break1_start' => $request->break1_start,
                'break1_end'   => $request->break1_end,
                'break2_start' => $request->break2_start,
                'break2_end'   => $request->break2_end,
                'note'         => $request->note,
            ],
            'reason' => $request->note,
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        return redirect()->back()->with('success', '勤怠修正を申請しました');
    }

    // 申請一覧（ユーザー側）
    public function myCorrection(Request $request)
    {
        $user = auth()->user();
        $tab = $request->input('tab','pending');

        $query = AttendanceCorrection::where('user_id', $user->id);

        if ($tab === 'approved') {
            $query->approved();
        } else {
            $query->pending();
        }

        $corrections = $query
            ->with([
                'user:id,name',
                'attendanceRecord:id,work_date',
            ])
            ->latest()
            ->get();

        return view(
            'stamp_correction_request_list',
            compact('corrections', 'tab')
        );
    }
}
