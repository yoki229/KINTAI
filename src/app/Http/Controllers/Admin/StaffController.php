<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{
    // スタッフ一覧画面（管理者）
    public function adminStaffList(Request $request)
    {
        // スタッフ一覧（管理者以外）
        $staffs = User::where('role', 'user')
            ->orderBy('name')
            ->get();

        return view('admin.admin_staff_list', compact(
            'staffs',
        ));
    }

    // スタッフ別勤怠一覧画面（管理者）
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

    // CSV出力用（スタッフ別勤怠一覧）
    public function exportStaffCsv(Request $request, $id) {
        $staff = User::findOrFail($id);

        // 月（表示中の月）
        $month = $request->month
        ? Carbon::createFromFormat('Y-m', $request->month) : Carbon::now();

        $start  = $month->copy()->startOfMonth();
        $end    = $month->copy()->endOfMonth();

        // １ヶ月分の勤怠（なければ空白）
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
        
        // CSV
        $response = new StreamedResponse(function () use ($attendances, $staff) {
            $handle = fopen('php://output', 'w');

            //文字化け防止（Excel用）
            fputcsv($handle, ['日付', '出勤', '退勤', '休息', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->work_date->format('Y-m-d'),
                    $attendance->clock_in_formatted,
                    $attendance->clock_out_formatted,
                    $attendance->bleak_time_formatted,
                    $attendance->work_time_formatted,
                ]);
            }

            fclose($handle);
        });

        $fileName = $staff->name . '_attendance_' . $month->format('Y_m') . '.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$fileName}");

        return $response;

    }
}
