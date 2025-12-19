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
            'break1_start'  => ['nullable', 'date_format:H:i'],
            'break1_end'    => ['nullable', 'date_format:H:i'],
            'break2_start'  => ['nullable', 'date_format:H:i'],
            'break2_end'    => ['nullable', 'date_format:H:i'],
            'note'          => ['nullable', 'string'],
        ];
    }

    private function validateBreak($validator, $start, $end, $startKey, $endKey) {
        if ($start || $end) {
            if (!$start || !$end) {
                $validator->errors()->add(
                    $startKey,
                    '休憩開始と終了はセットで入力してください'
                );
                return;
            }

            if ($start >= $end) {
                $validator->errors()->add(
                    $endKey,
                    '休憩終了は開始より後にしてください'
                );
            }
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 出勤・退勤
            if ($this->clock_in && $this->clock_out) {
                if ($this->clock_in >= $this->clock_out) {
                    $validator->errors()->add(
                        'clock_out',
                        '退勤時間は出勤時間より後にしてください'
                    );
                }
            }

            // 休憩1
            $this->validateBreak(
                $validator,
                $this->break1_start,
                $this->break1_end,
                'break1_start',
                'break1_end'
            );

            // 休憩2
            $this->validateBreak(
                $validator,
                $this->break2_start,
                $this->break2_end,
                'break2_start',
                'break2_end'
            );
        });
    }

    public function messages()
    {
        return [
            'clock_in.date_format'  => '出勤時間の形式が正しくありません',
            'clock_out.date_format' => '退勤時間の形式が正しくありません',
            'break1_start.date_format' => '休憩開始の形式が正しくありません',
            'break1_end.date_format'   => '休憩終了の形式が正しくありません',
            'break2_start.date_format' => '休憩開始の形式が正しくありません',
            'break2_end.date_format'   => '休憩終了の形式が正しくありません',
        ];
    }
}
