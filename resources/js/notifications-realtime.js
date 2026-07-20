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

// Map(channel -> Map(onSignal stringified -> handler)) to allow multiple different handlers,
// while avoiding duplicates for the exact same function text during wire:navigate re-mounts.
const channelHandlers = new Map();

/**
 * URL của server realtime.
 *
 * - Production: để trống VITE_SOCKET_URL -> dùng chính origin của web, Caddy
 *   proxy /socket.io sang container socketio. Không lộ cổng 3000 ra ngoài.
 * - Local: web chạy :8000 còn server.cjs chạy :3000 (php artisan serve không
 *   proxy được), nên phải đặt VITE_SOCKET_URL=http://127.0.0.1:3000 trong .env.
 */
const SOCKET_URL = import.meta.env.VITE_SOCKET_URL || window.location.origin;

function getSocket() {
    if (!socket) {
        socket = io(SOCKET_URL, {
            path: '/socket.io',
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

    if (!channelHandlers.has(channel)) {
        channelHandlers.set(channel, new Map());

        // Listen exactly once on the socket for this channel
        sock.on(channel, () => {
            const handlersMap = channelHandlers.get(channel);
            handlersMap.forEach(handler => handler());
        });
    }

    const handlersMap = channelHandlers.get(channel);
    // Use the stringified function as a signature to prevent duplicate registrations on re-mount
    const signature = onSignal.toString();

    if (!handlersMap.has(signature)) {
        handlersMap.set(signature, debounce(() => onSignal(), debounceMs));
    }
}

// Alias giữ tương thích với chuông thông báo (gọi ngay, không debounce).
export const listenNotifications = (channel, onSignal) => listenRealtime(channel, onSignal, 0);

// Cho phép gọi từ Alpine x-init trong Blade.
window.listenRealtime = listenRealtime;
window.listenNotifications = listenNotifications;
