const express = require('express');
const https = require('https');
const fs = require('fs');
const socketIo = require('socket.io');
const cors = require('cors');
const jwt = require('jsonwebtoken');

const app = express();

// Load SSL certificates
const privateKey = fs.readFileSync('./certificates/server.key', 'utf8');
const certificate = fs.readFileSync('./certificates/server.crt', 'utf8');
const credentials = { key: privateKey, cert: certificate };

const server = https.createServer(credentials, app);
const io = socketIo(server, {
    cors: {
        origin: 'https://juchheim.local',
        methods: ["GET", "POST"],
        allowedHeaders: ["Authorization"],
        credentials: true
    },
});

const JWT_SECRET = 'Shoe1/2/3'; // Ensure this matches the WordPress plugin

app.use(cors());

io.use((socket, next) => {
    const token = socket.handshake.auth.token;
    console.log('Received token:', token); // Log the received token
    if (!token) {
        console.error('Authentication error: No token provided');
        return next(new Error('Authentication error: No token provided'));
    }
    jwt.verify(token, JWT_SECRET, (err, decoded) => {
        if (err) {
            console.error('Authentication error: Invalid token', err);
            return next(new Error('Authentication error: Invalid token'));
        }
        console.log('Decoded JWT Token:', decoded); // Log the decoded token
        socket.user = decoded.data.user;
        console.log('Authenticated user:', socket.user);
        next();
    });
});

io.on('connection', (socket) => {
    console.log('New client connected:', socket.user);

    socket.on('chat message', (msg) => {
        const timestamp = new Date().toISOString();
        const messageData = {
            user: socket.user.username, // Ensure this is set
            message: msg,
            timestamp: timestamp,
        };
        console.log('Broadcasting message:', messageData);
        io.emit('chat message', messageData);
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected:', socket.user);
    });

    socket.on('error', (err) => {
        console.error('Socket error:', err);
    });
});

const PORT = process.env.PORT || 4000;
server.listen(PORT, () => console.log(`Server running on port ${PORT}`));
