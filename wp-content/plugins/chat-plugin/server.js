const https = require('https');
const fs = require('fs');
const WebSocket = require('ws');
const express = require('express');
const cookieParser = require('cookie-parser');
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args));

const app = express();
app.use(cookieParser());

const server = https.createServer({
  key: fs.readFileSync('private.key'),
  cert: fs.readFileSync('certificate.crt')
}, app);

const wss = new WebSocket.Server({ server });

wss.on('connection', (ws, req) => {
  console.log('New client connected');
  const cookies = req.headers.cookie;
  console.log('Cookies received:', cookies);

  if (!cookies) {
    console.log('No cookies found, closing connection');
    ws.send(JSON.stringify({ type: 'auth', success: false, message: 'Authentication required' }));
    ws.close();
    return;
  }

  const cookie = cookies.split(';').find(c => c.trim().startsWith('wordpress_logged_in'));
  if (!cookie) {
    console.log('Authentication cookie not found, closing connection');
    ws.send(JSON.stringify({ type: 'auth', success: false, message: 'Authentication required' }));
    ws.close();
    return;
  }

  fetch('https://juchheim.local/wp-json/wp/v2/users/me', {
    headers: { 'Cookie': cookie }
  })
  .then(response => response.json())
  .then(user => {
    if (user && user.id) {
      console.log('User authenticated:', user.name);
      ws.send(JSON.stringify({ type: 'auth', success: true, username: user.name }));

      ws.on('message', (message) => {
        console.log('Received message:', message);
        const response = JSON.stringify({ type: 'message', message: `${user.name}: ${message}` });
        ws.send(response);
      });

      ws.on('close', () => { console.log('Client disconnected'); });

      ws.on('error', (error) => { console.error('WebSocket error:', error); });
    } else {
      console.log('User authentication failed, closing connection');
      ws.send(JSON.stringify({ type: 'auth', success: false, message: 'Authentication required' }));
      ws.close();
    }
  })
  .catch(error => {
    console.error('Error fetching user data:', error);
    ws.send(JSON.stringify({ type: 'auth', success: false, message: 'Authentication required' }));
    ws.close();
  });
});

server.listen(4000, () => {
  console.log('Server running on port 4000');
});
