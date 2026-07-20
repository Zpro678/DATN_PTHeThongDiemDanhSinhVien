const { createServer } = require('http');
const { Server } = require('socket.io');
const Redis = require('ioredis');

// Cấu hình lấy từ biến môi trường để chạy được cả ở local (127.0.0.1) lẫn trong
// Docker (host = tên service `redis`). Có giá trị mặc định nên chạy tay vẫn OK.
const PORT = Number(process.env.SOCKET_PORT || 3000);
const REDIS_HOST = process.env.REDIS_HOST || '127.0.0.1';
const REDIS_PORT = Number(process.env.REDIS_PORT || 6379);
const REDIS_PASSWORD = process.env.REDIS_PASSWORD && process.env.REDIS_PASSWORD !== 'null'
    ? process.env.REDIS_PASSWORD
    : undefined;

// Ở production web và socket cùng origin (Caddy proxy /socket.io) nên không cần
// mở CORS. Ở local web chạy :8000 còn socket :3000 -> phải cho phép cross-origin.
const CORS_ORIGIN = process.env.SOCKET_CORS_ORIGIN || '*';

const httpServer = createServer();

const io = new Server(httpServer, {
    cors: {
        origin: CORS_ORIGIN === '*' ? '*' : CORS_ORIGIN.split(','),
    },
});

const redis = new Redis({
    host: REDIS_HOST,
    port: REDIS_PORT,
    password: REDIS_PASSWORD,
});

redis.on('error', (err) => {
    console.error('[socket] Lỗi kết nối Redis:', err.message);
});

redis.psubscribe('*');

redis.on('pmessage', (pattern, channel, message) => {
    io.emit(channel, JSON.parse(message));
});

io.on('connection', () => {
    console.log('Client Connected');
});

httpServer.listen(PORT, () => {
    console.log(`Socket.IO Running On Port ${PORT} (Redis ${REDIS_HOST}:${REDIS_PORT})`);
});
