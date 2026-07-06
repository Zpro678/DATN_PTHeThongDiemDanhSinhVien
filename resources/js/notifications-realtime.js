import { io } from 'socket.io-client';

/**
 * Realtime dùng chung qua websocket (socket.io thô, khớp với server.cjs).
 *
 * Luồng: Laravel broadcast -> Redis pub/sub -> server.cjs (psubscribe '*') ->
 * io.emit(<tên kênh Redis>, payload) -> client. Client chỉ nhận TÍN HIỆU rồi gọi
 * callback (thường là $wire.$refresh()) để Livewire tải lại dữ liệu từ DB (đã scope),
 * nên nội dung không lộ qua kênh. Dùng cho: chuông thông báo & bảng điểm danh QR.
 */

let socket = null;

// channel -> handler đang gắn, để gỡ khi component re-mount (tránh nhân đôi).
const handlers = new Map();

function getSocket() {
    if (!socket) {
        socket = io(`${window.location.hostname}:3000`, {
            transports: ['websocket', 'polling'],
        });
    }

    return socket;
}

function debounce(fn, ms) {
    if (!ms) {
        return fn;
    }

    let timer = null;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), ms);
    };
}

/**
 * Đăng ký lắng nghe realtime cho một kênh.
 *
 * @param {string} channel      Tên kênh socket.io (đã kèm prefix Redis) do backend cấp.
 * @param {Function} onSignal   Callback khi có sự kiện (vd $wire.$refresh()).
 * @param {number} debounceMs   Gộp các sự kiện dồn dập (0 = gọi ngay).
 */
export function listenRealtime(channel, onSignal, debounceMs = 0) {
    if (!channel || typeof onSignal !== 'function') {
        return;
    }

    const sock = getSocket();

    // Gỡ handler cũ của kênh này (nếu component vừa re-mount qua wire:navigate).
    if (handlers.has(channel)) {
        sock.off(channel, handlers.get(channel));
    }

    const handler = debounce(() => onSignal(), debounceMs);
    handlers.set(channel, handler);
    sock.on(channel, handler);
}

// Alias giữ tương thích với chuông thông báo (gọi ngay, không debounce).
export const listenNotifications = (channel, onSignal) => listenRealtime(channel, onSignal, 0);

// Cho phép gọi từ Alpine x-init trong Blade.
window.listenRealtime = listenRealtime;
window.listenNotifications = listenNotifications;
