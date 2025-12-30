<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CorrectionController extends Controller
{
    public function adminCorrectionList(Request $request)
    {
        return view('admin_attendance_list', compact());
    }

    public function adminCorrection(Request $request)
    {

        foreach ($correction->requested_changes['breaks'] as $break) {
            BreakRecord::create([
                'attendance_record_id' => $attendance->id,
                'clock_in'     => $request->clock_in,
                'break_start' => $break['start'],
                'break_end'   => $break['end'],
                'clock_out'    => $request->clock_out,
                'note'         => $request->note,
            ]);
        }

        return view('admin_attendance_list', compact());
    }
}
