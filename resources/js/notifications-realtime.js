import { io } from 'socket.io-client';

/**
 * Realtime cho chuông thông báo qua websocket (socket.io).
 *
 * Luồng: Laravel broadcast sự kiện NotificationReceived -> Redis pub/sub ->
 * server.cjs (psubscribe '*') -> io.emit(<tên kênh Redis>, payload) -> client.
 * Client chỉ nhận TÍN HIỆU (không có nội dung) rồi gọi callback để Livewire tải lại
 * dữ liệu chuông từ DB (đã scope theo tài khoản), nên nội dung không lộ qua kênh.
 */

let socket = null;

// channel -> handler đang gắn, để gỡ khi component re-mount (tránh nhân đôi).
const handlers = new Map();

function getSocket() {
    if (!socket) {
        // Cùng host với trang, cổng 3000 (server.cjs). Ưu tiên websocket.
        socket = io(`${window.location.hostname}:3000`, {
            transports: ['websocket', 'polling'],
        });
    }

    return socket;
}

/**
 * Đăng ký lắng nghe realtime cho một kênh thông báo.
 *
 * @param {string} channel  Tên kênh socket.io (đã kèm prefix Redis) do backend cấp.
 * @param {Function} onSignal  Callback chạy khi có thông báo mới (thường là $wire.$refresh()).
 */
export function listenNotifications(channel, onSignal) {
    if (!channel || typeof onSignal !== 'function') {
        return;
    }

    const sock = getSocket();

    // Gỡ handler cũ của kênh này (nếu component vừa re-mount qua wire:navigate).
    if (handlers.has(channel)) {
        sock.off(channel, handlers.get(channel));
    }

    const handler = () => onSignal();
    handlers.set(channel, handler);
    sock.on(channel, handler);
}

// Cho phép gọi từ Alpine x-init trong Blade.
window.listenNotifications = listenNotifications;
