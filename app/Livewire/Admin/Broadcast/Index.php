<?php

namespace App\Livewire\Admin\Broadcast;

use Livewire\Component;
use App\Models\User;
use App\Jobs\SendBroadcastEmailJob;

class Index extends Component
{
    public $subject;
    public $content;
    public $target = 'all';

    protected $rules = [
        'subject' => 'required|min:5|max:255',
        'content' => 'required|min:10',
        'target' => 'required|in:all,free,pro',
    ];

    public function sendBroadcast()
    {
        $this->validate();

        $query = User::query()->where('status', 'active');

        if ($this->target === 'free') {
            $query->whereDoesntHave('subscriptions', function($q) {
                $q->where('status', 'active')->where(function($sq) {
                    $sq->whereNull('end_date')->orWhere('end_date', '>=', now());
                });
            });
        } elseif ($this->target === 'pro') {
            $query->whereHas('subscriptions', function($q) {
                $q->where('status', 'active')->where(function($sq) {
                    $sq->whereNull('end_date')->orWhere('end_date', '>=', now());
                });
            });
        }

        $emails = $query->pluck('email')->toArray();
        $totalEmails = count($emails);

        if ($totalEmails === 0) {
            $this->dispatch('toast', message: 'Không tìm thấy người dùng nào phù hợp với điều kiện.', type: 'error');
            return;
        }

        $chunks = array_chunk($emails, 50);

        foreach ($chunks as $chunk) {
            dispatch(new SendBroadcastEmailJob($chunk, $this->subject, $this->content));
        }

        $this->reset(['subject', 'content', 'target']);
        
        $this->dispatch('toast', message: "Đã đưa vào hàng đợi gửi đến {$totalEmails} người dùng.", type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.broadcast.index')->layout('components.admin-layout', ['title' => 'Gửi thông báo hàng loạt']);
    }
}
