<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'subject_code'        => ['nullable', 'string', 'max:50'],
            'semester'            => ['nullable', 'string', 'max:50'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'total_sessions'      => ['required', 'integer', 'min:1', 'max:100'],
            'lessons_per_session' => ['required', 'integer', 'min:1', 'max:10'],
            'require_approval'    => ['boolean'],
            'status'              => ['sometimes', 'in:active,archived'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'               => 'Tên lớp học là bắt buộc.',
            'total_sessions.required'     => 'Tổng số buổi là bắt buộc.',
            'lessons_per_session.required'=> 'Số tiết mỗi buổi là bắt buộc.',
        ];
    }
}
