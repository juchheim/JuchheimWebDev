const express = require('express');
const https = require('https');
const fs = require('fs');
const socketIo = require('socket.io');
const cookieParser = require('cookie-parser');
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args));

const app = express();
app.use(cookieParser());

// Create the HTTPS server with SSL certificates
const server = https.createServer({
  key: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.key'), // Update with your actual key path
  cert: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.crt') // Update with your actual certificate path
}, app);

// Set up Socket.IO to use the HTTPS server
const io = socketIo(server, {
  cors: {
    origin: "https://juchheim.online",
    methods: ["GET", "POST"],
    credentials: true
  }
});

// Middleware to check for cookies and authenticate the connection
io.use((socket, next) => {
  const cookies = socket.handshake.headers.cookie;
  if (cookies) {
    next();
  } else {
    next(new Error('Authentication error'));
  }
});

// Event listener for new client connections
io.on('connection', (socket) => {
  console.log('New client connected');

  // Event listener for receiving messages from clients
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
      socket.emit('receiveMessage', data); // Send the message back to the client
    } catch (error) {
      console.error('Error during fetch operation:', error);
    }
  });

  // Event listener for client disconnections
  socket.on('disconnect', () => {
    console.log('Client disconnected');
  });
});

// Start the server and listen on port 4000
server.listen(4000, () => {
  console.log('Server running on port 4000');
});
