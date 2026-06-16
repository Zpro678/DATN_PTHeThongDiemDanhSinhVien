<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'code'                => ['required', 'string', 'max:50'],
            'subject_code'        => ['nullable', 'string', 'max:50'],
            'semester'            => ['nullable', 'string', 'max:50'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'total_sessions'      => ['required', 'integer', 'min:1', 'max:100'],
            'lessons_per_session' => ['required', 'integer', 'min:1', 'max:10'],
            'require_approval'    => ['boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $totalSessions = (int) $this->total_sessions;
        $totalLessons = (int) $this->total_lessons;

        if ($totalSessions > 0 && $totalLessons > 0) {
            $this->merge([
                'lessons_per_session' => max(1, (int) round($totalLessons / $totalSessions)),
            ]);
        } else {
            $this->merge([
                'lessons_per_session' => $this->lessons_per_session ?? 3,
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'name.required'               => 'Tên lớp học là bắt buộc.',
            'code.required'               => 'Mã lớp học là bắt buộc.',
            'total_sessions.required'     => 'Tổng số buổi là bắt buộc.',
            'total_sessions.min'          => 'Tổng số buổi phải ít nhất 1.',
            'lessons_per_session.required'=> 'Số tiết mỗi buổi là bắt buộc.',
            'lessons_per_session.min'     => 'Số tiết mỗi buổi phải ít nhất 1.',
        ];
    }
}
