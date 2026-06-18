<?php

namespace App\Livewire\User;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public string $workspace = 'admin';

    public bool $showCreateModal = false;

    public bool $showJoinModal = false;

    public string $joinStep = 'input';

    public function mount(): void
    {
        if (auth()->user()?->is_admin) {
            $this->redirectRoute('admin.dashboard', navigate: true);
        }
    }

    public function setWorkspace(string $workspace): void
    {
        if (in_array($workspace, ['admin', 'student'], true)) {
            $this->workspace = $workspace;
        }
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function openJoinModal(): void
    {
        $this->joinStep = 'input';
        $this->showJoinModal = true;
    }

    public function closeJoinModal(): void
    {
        $this->showJoinModal = false;
    }

    public function previewJoinClass(): void
    {
        $this->joinStep = 'preview';
    }

    public function backToJoinInput(): void
    {
        $this->joinStep = 'input';
    }

    public function render(): View
    {
        return view('livewire.user.dashboard')
            ->layout('layouts.user', ['title' => 'Tổng quan']);
    }
}
