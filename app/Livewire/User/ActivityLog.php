<?php

namespace App\Livewire\User;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $actionFilter = 'all';
    public string $dateFrom = '';
    public string $dateTo = '';

    /**
     * Whitelist các action nghiệp vụ được phép hiển thị trong Lịch sử hoạt động.
     *
     * Chỉ những action có ý nghĩa với người dùng mới xuất hiện — loại bỏ các bản ghi
     * kỹ thuật/nhiễu (created/updated/deleted tự sinh, hoặc action cũ không hợp lệ).
     */
    public const ALLOWED_ACTIONS = [
        'login',
        'class_created',
        'class_joined',
        'session_created',
        'session_closed',
        'attendance_check_in',
        'manual_attendance',
        'leave_request_submitted',
        'leave_request_edited',
        'leave_request_approved',
        'leave_request_rejected',
        'profile_updated',
        'password_changed',
        'feedback_submitted',
    ];

    /**
     * Danh sách action có thể lọc — tên thân thiện để hiển thị trong dropdown.
     */
    public function getActionLabels(): array
    {
        return [
            'all'                      => 'Tất cả hành động',
            'login'                    => 'Đăng nhập',
            'class_created'            => 'Tạo lớp học',
            'class_joined'             => 'Tham gia lớp',
            'session_created'          => 'Tạo buổi điểm danh',
            'session_closed'           => 'Chốt buổi điểm danh',
            'attendance_check_in'      => 'Điểm danh QR',
            'manual_attendance'        => 'Điểm danh thủ công',
            'leave_request_submitted'  => 'Gửi đơn xin nghỉ',
            'leave_request_edited'     => 'Sửa đơn xin nghỉ',
            'leave_request_approved'   => 'Duyệt đơn xin nghỉ',
            'leave_request_rejected'   => 'Từ chối đơn xin nghỉ',
            'profile_updated'          => 'Cập nhật hồ sơ',
            'password_changed'         => 'Đổi mật khẩu',
            'feedback_submitted'       => 'Gửi phản hồi',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
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
        $this->reset(['search', 'actionFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render(): View
    {
        $query = AuditLog::query()
            ->where('user_id', auth()->id())
            ->whereIn('action', self::ALLOWED_ACTIONS)
            ->with('courseClass:id,name,join_key')
            ->latest('created_at');

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where('action', 'like', "%{$search}%");
        }

        if ($this->actionFilter !== 'all') {
            $query->where('action', $this->actionFilter);
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->paginate(20);
        $actionLabels = $this->getActionLabels();

        return view('livewire.user.activity-log', compact('logs', 'actionLabels'))
            ->layout('layouts.user', ['title' => 'Lịch sử hoạt động']);
    }
}
