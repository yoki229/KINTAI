<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StaffController extends Controller
{
    public function adminStaffList(Request $request)
    {
        // 月（デフォルト：今月）
        $month = $request->month
            ? Carbon::createFromFormat('Y-m', $request->month)
            : Carbon::now();

        // 前月・翌月（リンク用）
        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        // 表示用
        $currentMonth = $month->format('Y/m');
        $currentMonthInput = $month->format('Y-m');

        // スタッフ一覧（管理者以外）
        $staffs = User::where('role', 'user')
            ->orderBy('name')
            ->get();

        return view('admin.admin_staff_list', compact(
            'staffs',
            'prevMonth',
            'nextMonth',
            'currentMonth',
            'currentMonthInput'
        ));
    }

    public function adminStaffDetail(Request $request, $id)
    {
        $staff = User::findOrFail($id);

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
            ->map(function ($date) use ($staff) {
                return AttendanceRecord::firstOrCreate(
                    [
                        'user_id'   => $staff->id,
                        'work_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'clock_in'  => null,
                        'clock_out' => null,
                    ]
                );
            });

        return view('admin.admin_attendance_staff', compact(
            'staff', 'attendances', 'prevMonth', 'nextMonth', 'currentMonth', 'currentMonthInput'
        ));
    }
}
