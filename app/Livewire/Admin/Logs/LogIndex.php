<?php

namespace App\Livewire\Admin\Logs;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class LogIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDate = '';
    public ?AuditLog $selectedLog = null;

    public $perPage = 10;

    public function mount()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function updatingSearch()
    {
        $this->perPage = 10;
    }

    public function updatingSelectedDate()
    {
        $this->perPage = 10;
    }

    public function viewLog($id)
    {
        $this->selectedLog = AuditLog::with(['user', 'courseClass'])->find($id);
        $this->dispatch('open-log-modal');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $query = AuditLog::query()->with(['user', 'courseClass']);

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('table_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($this->selectedDate)) {
            $query->whereDate('created_at', $this->selectedDate);
        }

        $totalLogs = $query->count();
        $logs = $query->latest('created_at')->take($this->perPage)->get();

        return view('livewire.admin.logs.log-index', [
            'logs' => $logs,
            'hasMore' => $totalLogs > $this->perPage,
        ])->title('Nhật ký Hoạt động');
    }
}
