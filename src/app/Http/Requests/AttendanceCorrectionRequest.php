<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in'          => 'nullable|date_format:H:i',
            'clock_out'         => 'nullable|date_format:H:i',
            'breaks.*.break_start'    => 'nullable|date_format:H:i',
            'breaks.*.break_end'      => 'nullable|date_format:H:i',
            'note'              => 'required|string',
        ];
    }

    private function validateBreak($validator, $start, $end, $startKey, $endKey, $clockIn, $clockOut)
    {
        $startTime = $start ? \Carbon\Carbon::createFromFormat('H:i', $start) : null;
        $endTime   = $end ? \Carbon\Carbon::createFromFormat('H:i', $end) : null;

        if ($startTime) {
            if (($clockIn && $startTime < $clockIn) || ($clockOut && $startTime > $clockOut)) {
                $validator->errors()->add(
                    $startKey,
                    '休憩時間が不適切な値です'
                );
            }
        }

        if ($endTime) {
            if ($clockOut && $endTime > $clockOut) {
                $validator->errors()->add(
                    $endKey,
                    '休憩時間もしくは退勤時間が不適切な値です'
                );
            }
    }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->clock_in ? \Carbon\Carbon::createFromFormat('H:i', $this->clock_in) : null;
            $clockOut = $this->clock_out ? \Carbon\Carbon::createFromFormat('H:i', $this->clock_out) : null;

            // 出勤・退勤チェック
            if ($clockIn && $clockOut) {
                if ($clockIn >= $clockOut) {
                    $validator->errors()->add(
                        'clock_in',
                        '出勤時間もしくは退勤時間が不適切な値です'
                    );
                }
            }

            // 休憩のチェック
            foreach ($this->breaks ?? [] as $index => $break) {
                $this->validateBreak(
                    $validator,
                    $break['break_start'] ?? null,
                    $break['break_end'] ?? null,
                    "breaks.$index.break_start",
                    "breaks.$index.break_end",
                    $clockIn,
                    $clockOut
                );
            }
        });
    }

    public function messages()
    {
        return [
            'clock_in.date_format'       => '出勤時間の形式が正しくありません',
            'clock_out.date_format'      => '退勤時間の形式が正しくありません',
            'breaks.*.break_start.date_format' => '休憩開始の形式が正しくありません',
            'breaks.*.break_end.date_format'   => '休憩終了の形式が正しくありません',
            'note.required'              => '備考を記入してください',
        ];
    }
}
