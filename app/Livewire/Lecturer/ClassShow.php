<?php

namespace App\Livewire\Lecturer;

use App\Models\CourseClass;
use Livewire\Component;

class ClassShow extends Component
{
    public CourseClass $class;

    public function mount(CourseClass $courseClass)
    {
        $this->class = $courseClass;
    }

    public function render()
    {
        return view('livewire.lecturer.class-show')->layout('layouts.user');
    }
}
