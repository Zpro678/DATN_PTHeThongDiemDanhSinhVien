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

    public function mount()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $query = AuditLog::query()->with(['user', 'courseClass']);

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where('action', 'like', "%{$search}%")
                ->orWhere('table_name', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $logs = $query->latest('created_at')->paginate(20);

        return view('livewire.admin.logs.log-index', [
            'logs' => $logs,
        ])->title('Nhật ký Hoạt động');
    }
}
