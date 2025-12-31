<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BreakRecord;
use App\Models\AttendanceCorrection;
use App\Http\Requests\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CorrectionController extends Controller
{
    // 申請一覧画面（一般・管理者）
    public function correctionList(Request $request)
    {
        $tab = $request->input('tab','pending');
        $user = auth()->user();

        $query = AttendanceCorrection::query();

        // 一般ユーザーなら「自分の分だけ」
        if ($user->is_user) {
            $query->where('user_id', $user->id);
        }

        // タブ切り替え
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

        return view('stamp_correction_request_list', compact('corrections', 'tab', 'user'));
    }

    // 修正申請承認画面（管理者）
    public function adminCorrection($attendance_correct_request_id)
    {
        $correction = AttendanceCorrection::with(
            'attendanceRecord.user',
            'attendanceRecord.breaks',
            )->findOrFail($attendance_correct_request_id);

        $attendance = $correction->attendanceRecord;

        $breaks = $attendance->breaks()->orderBy('id')->get();
        $breaks->push(new BreakRecord());

        // 申請内容
        $changes = $correction->requested_changes ?? [];

        return view(
            'admin.admin_stamp_correction_request_approve',
            compact('correction', 'attendance', 'changes', 'breaks')
        );
    }

    // 修正申請承認画面（管理者）
    public function adminApprove(int $attendance_correct_request_id)
    {
        $correction = AttendanceCorrection::with('attendanceRecord.breaks')
            ->findOrFail($attendance_correct_request_id);

        $attendance = $correction->attendanceRecord;
        $changes = $correction->requested_changes;

        \DB::transaction(function () use ($attendance, $correction, $changes){

            // 勤怠を更新
            $attendance->update([
                'clock_in'  => $changes['clock_in'] ?? $attendance->clock_in,
                'clock_out' => $changes['clock_out'] ?? $attendance->clock_out,
                'note'      => $changes['note'] ?? $attendance->note,
            ]);

            // 休憩を一度削除 → 再生成
            $attendance->breaks()->delete();

            foreach ($changes['breaks'] ?? [] as $break){
                $attendance->breaks()->create([
                    'break_start'   => $break['start'],
                    'break_end'     => $break['end'],
                ]);
            }

            // 修正申請を承認済みに
            $correction->update([
                'status'        => AttendanceCorrection::STATUS_APPROVED,
                'processed_by'  => auth()->id(),
                'processed_at'  => now(),
            ]);
        });

        return redirect()->back()->with('success', '修正申請を承認しました');
    }
}
