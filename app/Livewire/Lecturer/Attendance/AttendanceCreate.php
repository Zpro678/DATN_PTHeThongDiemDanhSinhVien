<?php

namespace App\Livewire\Lecturer\Attendance;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendanceCreate extends Component
{
    public function render(): View
    {
        return view('livewire.lecturer.attendance.create')
            ->layout('layouts.user', ['title' => 'Tạo buổi điểm danh']);
    }
}
