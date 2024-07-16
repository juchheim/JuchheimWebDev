import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('wp-rest-chat-app');
    if (rootElement) {
        const root = createRoot(rootElement); // Use createRoot to mount the app
        root.render(<App />);
    } else {
        console.error('Error: No root element found to mount React app.');
    }
});
