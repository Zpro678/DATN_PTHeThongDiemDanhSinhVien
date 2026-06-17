<?php

namespace App\Livewire\User;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Classes extends Component
{
    public function render(): View
    {
        return view('livewire.user.classes')
            ->layout('layouts.user', ['title' => 'Lớp học']);
    }
}
