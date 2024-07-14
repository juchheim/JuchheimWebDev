const express = require('express');
const https = require('https');
const fs = require('fs');
const WebSocket = require('ws');

const app = express();

// Serve static files (index.html, etc.)
app.use(express.static(__dirname));

// Create HTTPS server
const server = https.createServer({
  key: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.key'),
  cert: fs.readFileSync('/home/1260594.cloudwaysapps.com/whtqgbwgsb/private_html/server.crt')
}, app);

// Initialize WebSocket server instance
const wss = new WebSocket.Server({ server });

wss.on('connection', (ws, req) => {
  console.log('New client connected');

  ws.on('message', (message) => {
    console.log('Received message:', message);
    // Here you can add authentication logic and other message handling
    const response = JSON.stringify({ type: 'message', message: `You: ${message}` });
    ws.send(response);
  });

  ws.on('close', () => {
    console.log('Client disconnected');
  });

  ws.on('error', (error) => {
    console.error('WebSocket error:', error);
  });

  // Send a welcome message
  ws.send(JSON.stringify({ type: 'auth', success: true, username: 'TestUser' }));
});

server.listen(4000, () => {
  console.log('Server running on port 4000');
});
