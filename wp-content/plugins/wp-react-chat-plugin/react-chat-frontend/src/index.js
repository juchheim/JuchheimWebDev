import React from 'react';
import { createRoot } from 'react-dom/client';
import Chat from './Chat';

// Ensure reactChat is defined
if (typeof window.reactChat === 'undefined') {
    window.reactChat = { token: '', nonce: '' }; // or provide default values
}

console.log('reactChat:', window.reactChat);

const token = window.reactChat.token;
const nonce = window.reactChat.nonce;

const container = document.getElementById('react-chat-app');
console.log('container:', container);

if (container) {
    const root = createRoot(container);

    root.render(
        <React.StrictMode>
            <Chat token={token} nonce={nonce} />
        </React.StrictMode>
    );
} else {
    console.error('React container not found');
}
