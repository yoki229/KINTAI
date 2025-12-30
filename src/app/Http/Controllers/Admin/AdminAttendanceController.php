<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\BreakRecord;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    // 勤怠一覧画面（管理者）
    public function adminList(Request $request)
    {
        $user = Auth::user();

        // 日付表示（デフォルトは今日）
        $day = $request->day ? Carbon::createFromFormat('Y-m-d', $request->day) : Carbon::today();

        // 前日・翌日（リンク）
        $prevDay = $day->copy()->subDay()->format('Y-m-d');
        $nextDay = $day->copy()->addDay()->format('Y-m-d');

        // 前日・翌日（表示用）
        $currentDay = $day->format('Y年n月j日');
        $currentDayInput = $day->format('Y-m-d');

        $attendances = AttendanceRecord::with('user', 'breaks')
            ->where('work_date', $day->toDateString())
            ->get()
            ->map(function ($record){
                $record->name = $record->user->name;
                return $record;
            });

        return view('admin.admin_attendance_list', compact(
            'attendances', 'prevDay', 'nextDay', 'currentDay', 'currentDayInput'
        ));
    }

    // 勤怠詳細画面（管理者）
    public function adminAttendance(Request $request, $id)
    {
        $attendance = AttendanceRecord::findOrFail($id);

        // 既存の休憩を取得
        $breaks = $attendance->breaks()->orderBy('id')->get();
        // 一件分の休憩追加用
        $breaks->push(new BreakRecord());

        return view('admin.admin_attendance_detail', compact('attendance', 'breaks'));
    }

    //勤怠詳細画面から修正（管理者）
    public function adminAttendanceAdd(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = AttendanceRecord::findOrFail($id);

        // 勤怠を更新
        $attendance->clock_in = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->note = $request->note;
        $attendance->save();

        // 休憩を更新
        $attendance->breaks()->delete();
        if($request->breaks){
            foreach($request->breaks as $break){
                if(!empty($break['start']) || !empty($break['end'])){
                    $attendance->breaks()->create([
                        'break_start'   => $break['start'] ?: null,
                        'break_end'     => $break['end'] ?: null,
                    ]);
                }
            }
        }

        // 休憩追加用を含めて再取得
        $breaks = $attendance->breaks()->orderBy('id')->get();
        $breaks->push(new BreakRecord());

        return redirect()->back()->with('success', '勤怠を修正しました');
    }
}