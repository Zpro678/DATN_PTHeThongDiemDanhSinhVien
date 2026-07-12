/**
 * Khởi tạo icon Lucide bằng bản bundle CỤC BỘ (qua Vite) thay cho CDN unpkg.
 *
 * Trước đây các trang auth/admin/maintenance nạp `https://unpkg.com/lucide@latest`
 * rồi gọi createIcons() — khi mạng/CDN chậm hoặc lỗi thì `window.lucide` không tồn
 * tại nên icon không được render (hiện ô trống). Đó là lý do icon "lúc có lúc không".
 *
 * Ở đây chỉ import đúng những icon đang dùng (tree-shakeable, không kéo cả bộ) và
 * phơi `window.lucide.createIcons` để mã inline sẵn có (vd nút hiện/ẩn mật khẩu) vẫn chạy.
 */
import {
    createIcons,
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    BarChart3,
    Check,
    CheckCircle2,
    Eye,
    EyeOff,
    GraduationCap,
    Lock,
    LogOut,
    Mail,
    MailOpen,
    MapPin,
    QrCode,
    Send,
    Settings,
    ShieldAlert,
    User,
} from 'lucide';

const icons = {
    AlertCircle,
    ArrowLeft,
    ArrowRight,
    BarChart3,
    Check,
    CheckCircle2,
    Eye,
    EyeOff,
    GraduationCap,
    Lock,
    LogOut,
    Mail,
    MailOpen,
    MapPin,
    QrCode,
    Send,
    Settings,
    ShieldAlert,
    User,
};

const render = () => createIcons({ icons });

// Phơi API tương thích với mã inline đang gọi `window.lucide?.createIcons()`.
window.lucide = { createIcons: render, icons };

// Chạy ngay khi DOM sẵn sàng (và cả trường hợp script nạp sau khi DOM đã sẵn sàng).
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', render);
} else {
    render();
}

// Livewire/SPA điều hướng không tải lại trang -> phải render lại icon cho DOM mới.
document.addEventListener('livewire:navigated', render);
