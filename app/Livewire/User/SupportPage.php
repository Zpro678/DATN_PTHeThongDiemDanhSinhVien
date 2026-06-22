<?php

namespace App\Livewire\User;

use Livewire\Component;

class SupportPage extends Component
{
    public function render()
    {
        return view('livewire.user.support-page')->layout('layouts.user', ['title' => 'Hỗ trợ']);
    }
}
