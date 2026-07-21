<?php

namespace App\Livewire\Admin\Transactions;

use App\Livewire\Concerns\DeniesAccess;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionIndex extends Component
{
    use DeniesAccess, WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $paymentMethodFilter = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        if (! Auth::user()?->isAdmin()) {
            $this->denyAccess('user.dashboard', 'Bạn không có quyền truy cập khu vực quản trị.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentMethodFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'paymentMethodFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Transaction::query()
            ->with(['user:id,name,email,avatar', 'plan:id,name'])
            ->latest('created_at');

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'success') {
                $query->whereIn('status', ['success', 'SUCCESS', 'PAID', 'paid']);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        if ($this->paymentMethodFilter !== 'all') {
            $query->where('payment_method', $this->paymentMethodFilter);
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $transactions = $query->paginate(20);

        return view('livewire.admin.transactions.transaction-index', compact('transactions'))
            ->layout('components.admin-layout', ['title' => 'Quản lý giao dịch']);
    }
}
