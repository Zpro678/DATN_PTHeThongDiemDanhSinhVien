import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import io from 'socket.io-client';

window.io = io;
window.Echo = new Echo({
    broadcaster: 'socket.io',
    // Xem ghi chú ở resources/js/notifications-realtime.js: local cần trỏ thẳng
    // sang cổng 3000, production đi cùng origin qua Caddy.
    host: import.meta.env.VITE_SOCKET_URL || window.location.origin,
});
