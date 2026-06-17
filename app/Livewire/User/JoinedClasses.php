<?php

namespace App\Livewire\User;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class JoinedClasses extends Component
{
    public string $statusFilter = 'Tất cả';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function render(): View
    {
        return view('livewire.user.joined-classes')
            ->layout('layouts.user', ['title' => 'Lớp tôi tham gia']);
    }
}
