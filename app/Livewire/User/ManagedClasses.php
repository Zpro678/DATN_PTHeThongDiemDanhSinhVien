<?php

namespace App\Livewire\User;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class ManagedClasses extends Component
{
    public string $statusFilter = 'Tất cả';

    public string $semesterFilter = 'Tất cả học kỳ';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function render(): View
    {
        return view('livewire.user.managed-classes')
            ->layout('layouts.user', ['title' => 'Lớp tôi quản lý']);
    }
}
