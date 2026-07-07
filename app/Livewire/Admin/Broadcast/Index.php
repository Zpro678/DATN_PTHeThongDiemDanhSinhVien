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

    protected function rules()
    {
        $planIds = \App\Models\Plan::pluck('id')->toArray();
        $planKeys = array_map(fn($id) => "plan_{$id}", $planIds);
        
        $validTargets = array_merge(['all'], $planKeys);

        return [
            'subject' => 'required|min:5|max:255',
            'content' => 'required|min:10',
            'target' => ['required', \Illuminate\Validation\Rule::in($validTargets)],
        ];
    }

    public function sendBroadcast()
    {
        $this->validate();

        $query = User::query()->where('status', 'active');

        if (str_starts_with($this->target, 'plan_')) {
            $planId = str_replace('plan_', '', $this->target);
            $selectedPlan = \App\Models\Plan::find($planId);
            
            if ($selectedPlan && $selectedPlan->plan_tier === \App\Models\Plan::TIER_FREE) {
                $query->where(function($q) use ($planId) {
                    $q->whereDoesntHave('subscriptions', function($subQ) {
                        $subQ->where('status', 'active')->where(function($sq) {
                            $sq->whereNull('end_date')->orWhere('end_date', '>=', now());
                        });
                    })->orWhereHas('subscriptions', function($subQ) use ($planId) {
                        $subQ->where('plan_id', $planId)
                             ->where('status', 'active')
                             ->where(function($sq) {
                                 $sq->whereNull('end_date')->orWhere('end_date', '>=', now());
                             });
                    });
                });
            } else {
                $query->whereHas('subscriptions', function($q) use ($planId) {
                    $q->where('plan_id', $planId)
                      ->where('status', 'active')
                      ->where(function($sq) {
                          $sq->whereNull('end_date')->orWhere('end_date', '>=', now());
                      });
                });
            }
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
