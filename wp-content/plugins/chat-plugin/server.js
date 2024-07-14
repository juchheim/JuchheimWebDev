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

const io = socketIo(server);

io.use(async (socket, next) => {
  const cookies = socket.handshake.headers.cookie;
  console.log('Cookies received:', cookies);

  if (!cookies) {
    console.log('No cookies found');
    return next(new Error('Authentication error: No cookies found'));
  }

  const wpLoggedInCookie = cookies.split(';').find(c => c.trim().startsWith('wordpress_logged_in_'));
  console.log('WordPress cookie found:', wpLoggedInCookie);

  if (!wpLoggedInCookie) {
    console.log('No WordPress authentication cookie found');
    return next(new Error('Authentication error: No WordPress authentication cookie found'));
  }

  const [cookieName, cookieValue] = wpLoggedInCookie.trim().split('=');
  console.log('Cookie Name:', cookieName);
  console.log('Cookie Value:', cookieValue);

  try {
    const response = await fetch('https://juchheim.online/wp-json/wp/v2/users/me', {
      headers: {
        'Cookie': `${cookieName}=${cookieValue}`
      }
    });

    console.log('Response status:', response.status);
    if (!response.ok) {
      console.log('WordPress authentication failed');
      return next(new Error('Authentication error: WordPress authentication failed'));
    }

    const user = await response.json();
    socket.user = user;
    console.log(`User authenticated: ${user.id} - ${user.name}`);
    next();
  } catch (error) {
    console.error('Error verifying WordPress authentication:', error);
    next(new Error('Authentication error: Error verifying WordPress authentication'));
  }
});

io.on('connection', (socket) => {
  console.log('New client connected');

  if (socket.user) {
    socket.emit('userAuthenticated', { username: socket.user.name });
    console.log('User authenticated:', socket.user.name);
  } else {
    console.log('User is not authenticated');
  }

  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.user ? socket.user.name : 'unknown user');
  });
});

server.listen(4000, () => {
  console.log('Server running on port 4000');
});
