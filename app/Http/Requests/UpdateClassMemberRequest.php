<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $classId  = $this->route('class')?->id;
        $memberId = $this->route('member')?->id;

        return [
            'student_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('class_members', 'student_code')
                    ->where('class_id', $classId)
                    ->whereNull('deleted_at')
                    ->ignore($memberId), // Bỏ qua chính sinh viên đang sửa
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'status'    => ['required', 'in:active,dropped'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_code.required' => 'MSSV là bắt buộc.',
            'student_code.unique'   => 'MSSV này đã được sử dụng bởi sinh viên khác trong lớp.',
            'full_name.required'    => 'Họ tên sinh viên là bắt buộc.',
            'status.required'       => 'Trạng thái là bắt buộc.',
            'status.in'             => 'Trạng thái phải là active hoặc dropped.',
        ];
    }
}
