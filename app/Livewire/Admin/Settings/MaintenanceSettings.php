<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;

class MaintenanceSettings extends Component
{
    public $maintenance_mode = false;
    public $start_time = '';
    public $end_time = '';

    public function mount()
    {
        $this->maintenance_mode = (bool) Setting::get('maintenance_mode', false);
        $this->start_time = Setting::get('maintenance_start', '');
        $this->end_time = Setting::get('maintenance_end', '');
    }

    protected $rules = [
        'maintenance_mode' => 'boolean',
        'start_time' => 'nullable|date',
        'end_time' => 'nullable|date|after_or_equal:start_time',
    ];

    public function save(\App\Services\NotificationService $notificationService)
    {
        $this->validate();
        
        Setting::set('maintenance_mode', $this->maintenance_mode);
        Setting::set('maintenance_start', $this->start_time);
        Setting::set('maintenance_end', $this->end_time);

        // Chỉ gửi thông báo nếu chế độ bảo trì được BẬT và cấu hình đầy đủ thời gian
        if ($this->maintenance_mode && $this->start_time && $this->end_time) {
            $notificationService->notifySystemMaintenance($this->start_time, $this->end_time);
        }

        $this->dispatch('toast', message: 'Đã lưu & phát thông báo bảo trì toàn hệ thống.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings.maintenance-settings');
    }
}
