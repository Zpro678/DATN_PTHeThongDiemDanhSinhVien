<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Services\BackupService;
use Livewire\Component;

class MaintenanceSettings extends Component
{
    public $maintenance_mode = false;
    public $start_time = '';
    public $end_time = '';
    public $backups = [];
    public $super_admin_password = '';
    public $showRestoreModal = false;
    public $selectedBackup = '';

    public function mount(BackupService $backupService)
    {
        $this->maintenance_mode = (bool) Setting::get('maintenance_mode', false);
        $this->start_time = Setting::get('maintenance_start', '');
        $this->end_time = Setting::get('maintenance_end', '');
        $this->loadBackups($backupService);
    }

    public function loadBackups(BackupService $backupService)
    {
        $this->backups = $backupService->getBackups();
    }

    public function rules()
    {
        if ($this->maintenance_mode) {
            return [
                'maintenance_mode' => 'boolean',
                'start_time' => ['required', 'date', 'after_or_equal:today'],
                'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            ];
        }

        return [
            'maintenance_mode' => 'boolean',
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required_if' => 'Vui lòng chọn thời gian bắt đầu bảo trì.',
            'start_time.after_or_equal' => 'Thời gian bắt đầu không được nằm trong quá khứ.',
            'end_time.required_if' => 'Vui lòng chọn thời gian kết thúc bảo trì.',
            'end_time.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
        ];
    }

    public function save(\App\Services\NotificationService $notificationService, BackupService $backupService)
    {
        $this->validate();

        // Check if turning ON maintenance mode
        if ($this->maintenance_mode) {
            if (!$backupService->hasRecentBackup(12)) {
                $this->maintenance_mode = false;
                $this->dispatch('toast', message: 'Bạn chưa sao lưu dữ liệu trong 12 giờ qua. Vui lòng tạo bản sao lưu trước khi bật bảo trì!', type: 'error');
                return;
            }
        } else {
            // Nếu TẮT bảo trì, tự động xóa trắng ngày giờ
            $this->start_time = null;
            $this->end_time = null;
        }
        
        Setting::set('maintenance_mode', $this->maintenance_mode);
        Setting::set('maintenance_start', $this->start_time);
        Setting::set('maintenance_end', $this->end_time);

        // Chỉ gửi thông báo nếu chế độ bảo trì được BẬT và cấu hình đầy đủ thời gian
        if ($this->maintenance_mode && $this->start_time && $this->end_time) {
            $notificationService->notifySystemMaintenance($this->start_time, $this->end_time);
            $this->dispatch('toast', message: 'Đã bật & phát thông báo bảo trì toàn hệ thống.', type: 'success');
        } else {
            $this->dispatch('toast', message: 'Đã cập nhật trạng thái bảo trì.', type: 'success');
        }
    }

    public function createBackup(BackupService $backupService)
    {
        try {
            $backupService->createBackup();
            $this->loadBackups($backupService);
            $this->dispatch('toast', message: 'Tạo bản sao lưu thành công.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Lỗi sao lưu: ' . $e->getMessage(), type: 'error');
        }
    }

    public function deleteBackup(string $fileName, BackupService $backupService)
    {
        $backupService->deleteBackup($fileName);
        $this->loadBackups($backupService);
        $this->dispatch('toast', message: 'Đã xóa bản sao lưu.', type: 'success');
    }

    public function openRestoreModal($fileName)
    {
        $this->selectedBackup = $fileName;
        $this->super_admin_password = '';
        $this->showRestoreModal = true;
    }

    public function restoreBackup(BackupService $backupService)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user->isSuperAdmin()) {
            $this->dispatch('toast', message: 'Chỉ Super Admin mới có quyền phục hồi dữ liệu.', type: 'error');
            $this->showRestoreModal = false;
            return;
        }

        if (!\Illuminate\Support\Facades\Hash::check($this->super_admin_password, $user->password)) {
            $this->addError('super_admin_password', 'Mật khẩu không chính xác.');
            return;
        }

        try {
            $backupService->restoreBackup($this->selectedBackup);
            $this->showRestoreModal = false;
            $this->super_admin_password = '';
            
            // Xóa session để login lại? Tuỳ chọn, nhưng tốt nhất cứ báo thành công.
            $this->dispatch('toast', message: 'Phục hồi dữ liệu thành công!', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Lỗi phục hồi: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.maintenance-settings')->title('Quản lý Bảo trì & Sao lưu');
    }
}
