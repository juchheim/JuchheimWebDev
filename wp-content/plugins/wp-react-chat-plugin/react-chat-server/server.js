const express = require('express');
const https = require('https');
const fs = require('fs');
const socketIo = require('socket.io');
const jwt = require('jsonwebtoken');

const app = express();

// Load SSL certificates from Cloudways' default location
const privateKey = fs.readFileSync('/etc/ssl/private/ssl-cert-snakeoil.key', 'utf8');
const certificate = fs.readFileSync('/etc/ssl/certs/ssl-cert-snakeoil.pem', 'utf8');
const credentials = { key: privateKey, cert: certificate };

const server = https.createServer(credentials, app);
const io = socketIo(server, {
    pingTimeout: 60000,
    maxHttpBufferSize: 1e8,
});

const JWT_SECRET = 'Shoe1/2/3';

io.use((socket, next) => {
    const token = socket.handshake.auth.token;
    if (!token) {
        return next(new Error('Authentication error: No token provided'));
    }
    jwt.verify(token, JWT_SECRET, (err, decoded) => {
        if (err) {
            return next(new Error('Authentication error: Invalid token'));
        }
        socket.user = decoded.data.user;
        next();
    });
});

io.on('connection', (socket) => {
    socket.on('chat message', (msg) => {
        const timestamp = new Date().toISOString();
        const messageData = {
            user: socket.user.username,
            message: msg,
            timestamp: timestamp,
        };
        io.emit('chat message', messageData);
    });

    socket.on('disconnect', () => {});
    socket.on('error', (err) => {});
});

const PORT = process.env.PORT || 4000;
server.listen(PORT, () => console.log(`Server running on port ${PORT}`));
