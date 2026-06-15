<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $classId = $this->route('class')?->id;

        return [
            'student_code' => [
                'required',
                'string',
                'max:50',
                // MSSV không trùng trong cùng 1 lớp
                Rule::unique('class_members', 'student_code')
                    ->where('class_id', $classId)
                    ->whereNull('deleted_at'),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'status'    => ['sometimes', 'in:active,dropped'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_code.required' => 'MSSV là bắt buộc.',
            'student_code.unique'   => 'MSSV này đã có trong lớp.',
            'full_name.required'    => 'Họ tên sinh viên là bắt buộc.',
        ];
    }
}
