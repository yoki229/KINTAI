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
            'clock_in'      => ['nullable', 'date_format:H:i'],
            'clock_out'     => ['nullable', 'date_format:H:i'],
            'breaks.*.start'  => ['nullable', 'date_format:H:i'],
            'breaks.*.end'    => ['nullable', 'date_format:H:i'],
            'note'          => ['required', 'string'],
        ];
    }

    private function validateBreak($validator, $start, $end, $startKey, $endKey, $clockOut)
    {
        if ($start || $end) {
            if (!$start || !$end) {
                $validator->errors()->add(
                    $startKey,
                    '休憩開始と終了はセットで入力してください'
                );
                return;
            }

                // 開始 >= 終了
            if ($start >= $end) {
                $validator->errors()->add(
                    $startKey,
                    '休憩時間が不適切な値です'
                );
            }

            // 休憩開始が退勤後
            if ($clockOut && $start > $clockOut) {
                $validator->errors()->add(
                    $startKey,
                    '休憩時間が不適切な値です'
                );
            }

            // 休憩終了が退勤後
            if ($clockOut && $end > $clockOut) {
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
            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;

            // 出勤・退勤チェック
            if ($clockIn && $clockOut) {
                if ($clockIn >= $clockOut) {
                    $validator->errors()->add(
                        'clock_in',
                        '出勤時間が不適切な値です'
                    );
                }
            }

            // 休憩
            foreach ($this->breaks ?? [] as $index => $break){
                $this->validateBreak(
                    $validator,
                    $break['start'] ?? null,
                    $break['end'] ?? null,
                    "breaks.$index.start",
                    "breaks.$index.end",
                    $clockOut
                );
            }
        });
    }

    public function messages()
    {
        return [
            'clock_in.date_format'  => '出勤時間の形式が正しくありません',
            'clock_out.date_format' => '退勤時間の形式が正しくありません',
            'breaks.*.start.date_format' => '休憩開始の形式が正しくありません',
            'breaks.*.end.date_format'   => '休憩終了の形式が正しくありません',
            'note.required'            => '備考を記入してください',
        ];
    }
}
