<?php

namespace App\Livewire\Student;

use Livewire\Component;

class Warnings extends Component
{
    public function render()
    {
        return view('livewire.student.warnings')->layout('layouts.user', ['title' => 'Cảnh báo học tập']);
    }
}
