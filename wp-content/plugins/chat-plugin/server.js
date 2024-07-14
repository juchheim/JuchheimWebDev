const https = require('https');
const fs = require('fs');
const WebSocket = require('ws');
const cookieParser = require('cookie-parser');
const express = require('express');
const fetch = (...args) => import('node-fetch').then(({ default: fetch }) => fetch(...args));

const app = express();
app.use(cookieParser());

const server = https.createServer({
  key: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.key'),
  cert: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.crt')
});

const wss = new WebSocket.Server({ server });

wss.on('connection', (ws, req) => {
  const cookies = req.headers.cookie;
  console.log('Cookies received:', cookies);

  if (!cookies) {
    ws.close(1008, 'No cookies found');
    return;
  }

  const wpLoggedInCookie = cookies.split(';').find(c => c.trim().startsWith('wordpress_logged_in_'));
  console.log('WordPress cookie found:', wpLoggedInCookie);

  if (!wpLoggedInCookie) {
    ws.close(1008, 'No WordPress authentication cookie found');
    return;
  }

  const [cookieName, cookieValue] = wpLoggedInCookie.trim().split('=');
  console.log('Cookie Name:', cookieName);
  console.log('Cookie Value:', cookieValue);

  fetch('https://juchheim.online/wp-json/wp/v2/users/me', {
    headers: {
      'Cookie': `${cookieName}=${cookieValue}`
    }
  })
    .then(response => response.json())
    .then(user => {
      console.log(`User authenticated: ${user.id} - ${user.name}`);
      ws.user = user;

      ws.send(JSON.stringify({ type: 'auth', success: true, username: user.name }));
    })
    .catch(error => {
      console.error('Error verifying WordPress authentication:', error);
      ws.close(1008, 'Authentication error');
    });

  ws.on('message', message => {
    console.log(`Received message: ${message}`);
    ws.send(JSON.stringify({ type: 'message', message }));
  });

  ws.on('close', () => {
    console.log('Client disconnected');
  });
});

server.listen(4000, () => {
  console.log('Server running on port 4000');
});
