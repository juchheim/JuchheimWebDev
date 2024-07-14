const express = require('express');
const https = require('https');
const fs = require('fs');
const socketIo = require('socket.io');
const cookieParser = require('cookie-parser');
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args));

const app = express();
app.use(cookieParser());

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

// Middleware to verify WordPress user authentication
io.use(async (socket, next) => {
  const cookies = socket.handshake.headers.cookie;
  if (!cookies) {
    console.log('No cookies found');
    return next(new Error('Authentication error'));
  }

  const wpLoggedInCookie = cookies.split(';').find(c => c.trim().startsWith('wordpress_logged_in_'));
  if (!wpLoggedInCookie) {
    console.log('No WordPress authentication cookie found');
    return next(new Error('Authentication error'));
  }

  const [cookieName, cookieValue] = wpLoggedInCookie.trim().split('=');

  try {
    const response = await fetch('https://juchheim.online/wp-json/wp/v2/users/me', {
      headers: {
        'Cookie': `${cookieName}=${cookieValue}`
      }
    });

    if (!response.ok) {
      console.log('WordPress authentication failed');
      return next(new Error('Authentication error'));
    }

    const user = await response.json();
    socket.user = user;
    next();
  } catch (error) {
    console.error('Error verifying WordPress authentication:', error);
    next(new Error('Authentication error'));
  }
});

io.on('connection', (socket) => {
  console.log('New client connected:', socket.user);

  socket.on('sendMessage', async (data) => {
    console.log(`Received message: ${data.message} from user: ${socket.user.id} for chat: ${data.chatId}`);

    const postData = {
      chat_id: data.chatId,
      message: data.message,
      user_id: socket.user.id,
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
    } catch (error) {
      console.error('Error during fetch operation:', error);
    }
  });

  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.user);
  });
});

server.listen(4000, () => {
  console.log('Server running on port 4000');
});
