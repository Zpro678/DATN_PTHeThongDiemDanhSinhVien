<?php

namespace App\Livewire\User;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateClass extends Component
{
    public string $activeTab = 'info';

    public bool $gpsEnabled = false;

    public bool $qrEnabled = true;

    public bool $manualEnabled = true;

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['info', 'attendance', 'warning'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function toggleGps(): void
    {
        $this->gpsEnabled = ! $this->gpsEnabled;
    }

    public function toggleQr(): void
    {
        $this->qrEnabled = ! $this->qrEnabled;
    }

    public function toggleManual(): void
    {
        $this->manualEnabled = ! $this->manualEnabled;
    }

    public function render(): View
    {
        return view('livewire.user.create-class')
            ->layout('layouts.user', ['title' => 'Tạo lớp mới']);
    }
}
