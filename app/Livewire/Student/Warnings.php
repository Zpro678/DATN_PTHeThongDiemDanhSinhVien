<?php

namespace App\Livewire\Student;

use App\Services\StudentsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Component hiển thị trang cảnh báo học tập của học viên.
 *
 * Component chỉ chịu trách nhiệm gọi service lấy dữ liệu và truyền sang view;
 * phần tính toán cảnh báo nằm trong StudentsService để dễ tái sử dụng.
 */
class Warnings extends Component
{
    public function render(): View
    {
        // Lấy các cảnh báo của tài khoản đang đăng nhập từ database.
        $warnings = app(StudentsService::class)->getWarningsForStudent((int) auth()->id());

        // Truyền $warnings sang resources/views/livewire/student/warnings.blade.php để render card cảnh báo.
        return view('livewire.student.warnings', compact('warnings'))
            ->layout('layouts.user', ['title' => 'Cảnh báo học tập']);
    }
}
