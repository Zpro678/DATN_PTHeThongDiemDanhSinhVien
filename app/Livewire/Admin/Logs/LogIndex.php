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
    public $dateFilter = 'all'; // all, 1_month, 3_months, 6_months
    public ?AuditLog $selectedLog = null;

    public function mount()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFilter()
    {
        $this->resetPage();
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

        if ($this->dateFilter !== 'all') {
            $now = \Carbon\Carbon::now();
            if ($this->dateFilter === '1_month') {
                $query->where('created_at', '>=', $now->subMonth());
            } elseif ($this->dateFilter === '3_months') {
                $query->where('created_at', '>=', $now->subMonths(3));
            } elseif ($this->dateFilter === '6_months') {
                $query->where('created_at', '>=', $now->subMonths(6));
            }
        }

        $logs = $query->latest('created_at')->paginate(20);

        return view('livewire.admin.logs.log-index', [
            'logs' => $logs,
        ])->title('Nhật ký Hoạt động');
    }
}
