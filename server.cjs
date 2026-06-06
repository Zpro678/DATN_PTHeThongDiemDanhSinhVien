const { createServer } = require('http');
const { Server } = require('socket.io');
const Redis = require('ioredis');

const httpServer = createServer();

const io = new Server(httpServer, {
    cors: {
        origin: '*'
    }
});

const redis = new Redis();

redis.psubscribe('*');

redis.on('pmessage', (pattern, channel, message) => {
    io.emit(channel, JSON.parse(message));
});

io.on('connection', (socket) => {
    console.log('Client Connected');
});

httpServer.listen(3000, () => {
    console.log('Socket.IO Running On Port 3000');
});
