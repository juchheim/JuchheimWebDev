const express = require('express');
const https = require('https');
const fs = require('fs');
const socketIo = require('socket.io');
const cookieParser = require('cookie-parser');
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args));

const app = express();
app.use(cookieParser());

const server = https.createServer({
  key: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.key'), // Update with your actual key path
  cert: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.crt') // Update with your actual certificate path
}, app);

const io = socketIo(server, {
  cors: {
    origin: "https://juchheim.online",
    methods: ["GET", "POST"],
    credentials: true
  }
});

io.use((socket, next) => {
  const cookies = socket.handshake.headers.cookie;
  if (cookies) {
    next();
  } else {
    next(new Error('Authentication error'));
  }
});

io.on('connection', (socket) => {
  console.log('New client connected');

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
      socket.emit('receiveMessage', data);
    } catch (error) {
      console.error('Error during fetch operation:', error);
    }
  });

  socket.on('disconnect', () => {
    console.log('Client disconnected');
  });
});

server.listen(4000, () => {
  console.log('Server running on port 4000');
});
