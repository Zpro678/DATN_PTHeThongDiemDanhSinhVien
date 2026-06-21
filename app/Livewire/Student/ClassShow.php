<?php

namespace App\Livewire\Student;

use App\Models\CourseClass;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ClassShow extends Component
{
    public CourseClass $class;

    public function mount(CourseClass $courseClass): void
    {
        $this->class = $courseClass;
    }

    public function render(): View
    {
        return view('livewire.student.class-show', [
            'class' => $this->class,
        ])->layout('layouts.user');
    }
}
