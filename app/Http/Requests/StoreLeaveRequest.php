<?php

namespace App\Http\Requests;

use App\Models\ClassMember;
use App\Models\ClassSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Kiểm tra xem user có phải là thành viên active của lớp học đó hay không
        $classId = $this->input('class_id');
        $user = Auth::user();

        if (!$classId) {
            return false;
        }

        return ClassMember::where('class_id', $classId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->student_code) {
                    $q->orWhere('student_code', $user->student_code);
                }
            })
            ->where('status', 'active')
            ->exists();
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'class_session_id' => [
                'required',
                'exists:class_sessions,id',
                function ($attribute, $value, $fail) {
                    // Kiểm tra xem class_session có thuộc class_id đã chọn không
                    $session = ClassSession::find($value);
                    if ($session && $session->class_id != $this->input('class_id')) {
                        $fail('Buổi học chọn không hợp lệ.');
                    }
                }
            ],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'proof_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'Vui lòng chọn môn học/lớp học.',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'class_session_id.required' => 'Vui lòng chọn buổi học xin nghỉ.',
            'class_session_id.exists' => 'Buổi học không tồn tại.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ.',
            'reason.min' => 'Lý do xin nghỉ quá ngắn (tối thiểu 10 ký tự).',
            'reason.max' => 'Lý do xin nghỉ quá dài (tối đa 1000 ký tự).',
            'proof_image.file' => 'Minh chứng phải là một file hợp lệ.',
            'proof_image.mimes' => 'Minh chứng chỉ chấp nhận định dạng JPG, PNG, PDF.',
            'proof_image.max' => 'Dung lượng file minh chứng không vượt quá 5MB.',
        ];
    }
}
