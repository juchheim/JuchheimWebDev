const express = require('express');
const https = require('https');
const fs = require('fs');
const socketIo = require('socket.io');
const jwt = require('jsonwebtoken');

const app = express();

// Load SSL certificates
const privateKey = fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/public_html/wp-content/plugins/wp-react-chat-plugin/react-chat-server/certificates/server.key', 'utf8');
const certificate = fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/public_html/wp-content/plugins/wp-react-chat-plugin/react-chat-server/certificates/server.crt', 'utf8');
const credentials = { key: privateKey, cert: certificate };

const server = https.createServer(credentials, app);
const io = socketIo(server, {
    pingTimeout: 30000,
    maxHttpBufferSize: 1e8,
});

const JWT_SECRET = 'Shoe1/2/3';

io.use((socket, next) => {
    console.log('New connection attempt');
    const token = socket.handshake.auth.token;
    if (!token) {
        console.log('Authentication error: No token provided');
        return next(new Error('Authentication error: No token provided'));
    }
    jwt.verify(token, JWT_SECRET, (err, decoded) => {
        if (err) {
            console.log('Authentication error: Invalid token');
            return next(new Error('Authentication error: Invalid token'));
        }
        console.log('Authentication successful for user:', decoded.data.user.username);
        socket.user = decoded.data.user;
        next();
    });
});

io.on('connection', (socket) => {
    console.log(`User connected: ${socket.user.username}`);

    socket.on('chat message', (msg) => {
        console.log(`Message received from ${socket.user.username}: ${msg}`);
        const timestamp = new Date().toISOString();
        const messageData = {
            user: socket.user.username,
            message: msg,
            timestamp: timestamp,
        };
        io.emit('chat message', messageData);
    });

    socket.on('disconnect', (reason) => {
        console.log(`User disconnected: ${socket.user.username}. Reason: ${reason}`);
    });

    socket.on('error', (err) => {
        console.log(`Error on socket for user ${socket.user.username}: ${err.message}`);
    });
});

const PORT = process.env.PORT || 4000;
server.listen(PORT, '0.0.0.0', () => console.log(`Server running on port ${PORT}`));
