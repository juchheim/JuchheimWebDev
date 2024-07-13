const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');
const cookieParser = require('cookie-parser');
const { getWordPressUser } = require('./wordpressAuth');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: 'https://juchheim.local',
    methods: ['GET', 'POST'],
    credentials: true,
  }
});

app.use(cors({ origin: 'https://juchheim.local', credentials: true }));
app.use(cookieParser());

// Disable SSL verification for fetch
process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

io.use(async (socket, next) => {
  const cookies = socket.handshake.headers.cookie;
  console.log('Headers:', socket.handshake.headers);
  console.log('Cookies received:', cookies);
  if (cookies) {
    const user = await getWordPressUser(cookies);
    if (user) {
      socket.user = user;
      console.log('Authenticated user:', user);
      return next();
    } else {
      console.log('User not authenticated');
    }
  } else {
    console.log('No cookies found');
  }
  next(new Error('Authentication error'));
});

io.on('connection', (socket) => {
  console.log('New client connected with user:', socket.user ? socket.user.username : 'unauthenticated');

  socket.on('sendMessage', async (data) => {
    if (!socket.user) {
      socket.emit('errorMessage', 'User not authenticated');
      return;
    }
    console.log(`Received message: ${data.message} from user: ${socket.user.username} for chat: ${data.chatId}`);

    const postData = {
      chat_id: data.chatId,
      message: data.message,
      user_id: socket.user.id,
    };

    try {
      console.log('Sending data to WordPress API:', postData);
      const fetch = await import('node-fetch');
      const response = await fetch.default('https://juchheim.local/wp-json/chat/v1/messages', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(postData),
      });
      const result = await response.json();
      console.log('Message saved:', result);
      socket.emit('receiveMessage', { ...data, userId: socket.user.username });
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
