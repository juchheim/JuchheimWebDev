const https = require('https');
const fs = require('fs');
const WebSocket = require('ws');
const express = require('express');

const app = express();
app.use(express.static(__dirname));

// Create HTTPS server
const server = https.createServer({
  key: fs.readFileSync(`${__dirname}/private.key`), // Adjust path if needed
  cert: fs.readFileSync(`${__dirname}/certificate.crt`) // Adjust path if needed
}, app);

// Initialize WebSocket server instance
const wss = new WebSocket.Server({ server });

wss.on('connection', (ws) => {
  console.log('New client connected');

  ws.on('message', (message) => {
    console.log('Received message:', message);
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
