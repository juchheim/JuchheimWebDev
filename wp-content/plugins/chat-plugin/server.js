const express = require('express');
const https = require('https');
const fs = require('fs');
const socketIo = require('socket.io');
const cookieParser = require('cookie-parser');
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args));

const app = express();
app.use(cookieParser());

// HTTPS server with SSL certificate
const server = https.createServer({
  key: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.key'),
  cert: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.crt')
}, app);

const io = socketIo(server, {
  cors: {
    origin: "https://juchheim.online",
    methods: ["GET", "POST"],
    credentials: true
  }
});

// Middleware to check for cookies (authentication)
io.use((socket, next) => {
  const cookies = socket.handshake.headers.cookie;
  console.log('Cookies received:', cookies);
  if (cookies) {
    next();
  } else {
    next(new Error('Authentication error'));
  }
});

// Handle socket connection
io.on('connection', (socket) => {
  console.log('New client connected');

  // Handle incoming messages
  socket.on('sendMessage', async (data) => {
    console.log(`Received message: ${data.message} from user: ${data.userId} for chat: ${data.chatId}`);

    const postData = {
      chat_id: data.chatId,
      message: data.message,
      user_id: data.userId,
    };

    try {
      console.log('Sending data to WordPress API:', postData);
      const response = await fetch('https://juchheim.online/wp-json/chat/v1/messages', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData),
      });
      const result = await response.json();
      console.log('Message saved:', result);
      socket.broadcast.emit('receiveMessage', data);
      console.log('Broadcasted message to clients:', data);
    } catch (error) {
      console.error('Error during fetch operation:', error);
    }
  });

  // Handle client disconnect
  socket.on('disconnect', () => {
    console.log('Client disconnected');
  });
});

// Start the server
server.listen(4000, () => {
  console.log('Server running on port 4000');
});
