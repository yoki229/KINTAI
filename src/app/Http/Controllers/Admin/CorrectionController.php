<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakRecord;
use App\Models\AttendanceCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CorrectionController extends Controller
{
    public function adminCorrectionList(Request $request)
    {
        $tab = $request->input('tab','pending');

        $query = AttendanceCorrection::query();

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
            'admin.stamp_correction_request/list',
            compact('corrections', 'tab')
        );
    }

    public function adminCorrection($attendance_correct_request_id)
    {
        $correction = AttendanceCorrection::with('attendanceRecord')
        ->findOrFail($attendance_correct_request_id);

        $attendance = $correction->attendanceRecord;

        foreach ($correction->requested_changes['breaks'] as $break) {
            BreakRecord::create([
                'attendance_record_id' => $attendance->id,
                'break_start' => $break['start'],
                'break_end'   => $break['end'],
            ]);
        }

        return view('admin.stamp_correction_request.approve', compact('correction',
        'attendance'));
    }
}
