<?php

namespace App\Livewire\User;

use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Trang "Tất cả thông báo" của người dùng.
 *
 * Bố cục 2 cột: rail lọc theo danh mục (kèm số đếm) bên trái và danh sách
 * thông báo nhóm theo ngày bên phải. Có phân trang (tái dùng theme tailwind
 * đã publish) và cho phép đánh dấu đã đọc.
 */
class NotificationIndex extends Component
{
    use WithPagination;

    /** Bộ lọc đang chọn: all | unread | system | <khóa danh mục>. */
    #[Url(as: 'loc')]
    public string $filter = 'all';

    /**
     * Đổi bộ lọc và quay về trang đầu.
     */
    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    /**
     * Đánh dấu tất cả thông báo chưa đọc của người dùng là đã đọc.
     */
    public function markAllAsRead(): void
    {
        auth()->user()?->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render(): View
    {
        $service = app(NotificationService::class);
        $user = auth()->user();

        $navItems = $service->getNavItems($user);
        $notifications = $service->paginateForUser($user, $this->filter);

        return view('livewire.user.notifications', compact('navItems', 'notifications'))
            ->layout('layouts.user', ['title' => 'Thông báo']);
    }
}
