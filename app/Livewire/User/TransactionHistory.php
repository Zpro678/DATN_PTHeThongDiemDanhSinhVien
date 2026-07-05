<?php

namespace App\Livewire\User;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionHistory extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function getStatusLabels(): array
    {
        return [
            'all'       => 'Tất cả trạng thái',
            'pending'   => 'Đang chờ',
            'success'   => 'Hoàn thành',
            'failed'    => 'Thất bại',
        ];
    }

    public function updatingStatusFilter(): void
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
        $this->reset(['statusFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function continuePayment(int $transactionId)
    {
        $transaction = \App\Models\Transaction::where('user_id', auth()->id())->find($transactionId);

        if (!$transaction) {
            session()->flash('error', 'Không tìm thấy giao dịch.');
            return;
        }

        if (strtolower($transaction->status) !== 'pending') {
            session()->flash('error', 'Giao dịch này đã được xử lý.');
            return;
        }

        if ($transaction->expired_at && $transaction->expired_at->isPast()) {
            $transaction->update(['status' => 'failed', 'failure_reason' => 'Hết hạn thanh toán']);
            session()->flash('error', 'Giao dịch đã hết hạn.');
            return;
        }

        if (!$transaction->payment_url) {
            session()->flash('error', 'Không tìm thấy link thanh toán.');
            return;
        }

        return $this->redirect($transaction->payment_url);
    }

    public function render(): View
    {
        $query = Transaction::query()
            ->where('user_id', auth()->id())
            ->with('plan')
            ->latest('created_at');

        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'success') {
                $query->whereIn('status', ['success', 'SUCCESS', 'PAID', 'paid']);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $transactions = $query->paginate(15);
        $statusLabels = $this->getStatusLabels();

        return view('livewire.user.transaction-history', compact('transactions', 'statusLabels'))
            ->layout('layouts.user', [
                'title' => 'Lịch sử giao dịch',
                'activeNav' => 'transaction-history'
            ]);
    }
}
