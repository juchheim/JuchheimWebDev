import React, { useEffect, useState } from 'react';
import './App.css';

function App() {
    const [messages, setMessages] = useState([]);
    const [message, setMessage] = useState('');

    useEffect(() => {
        async function fetchMessages() {
            try {
                if (!window.wpRestChat || !window.wpRestChat.apiUrl) {
                    throw new Error('Missing API URL or user data.');
                }
                const response = await fetch(`${window.wpRestChat.apiUrl}messages`, {
                    headers: {
                        'X-WP-Nonce': window.wpRestChat.nonce,
                    },
                });
                const data = await response.json();
                if (Array.isArray(data)) {
                    setMessages(data);
                } else {
                    console.error('Unexpected response data:', data);
                }
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        }

        fetchMessages();

        // Fetch new messages every 5 seconds
        const interval = setInterval(fetchMessages, 5000);
        return () => clearInterval(interval);
    }, []);

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (message.trim() === '') return;

        try {
            const response = await fetch(`${window.wpRestChat.apiUrl}messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wpRestChat.nonce,
                },
                body: JSON.stringify({
                    content: message,
                    user: window.wpRestChat.user,
                }),
            });
            const newMessage = await response.json();
            setMessages((prevMessages) => [...prevMessages, newMessage]);
            setMessage('');
        } catch (error) {
            console.error('Error sending message:', error);
        }
    };

    return (
        <div>
            <ul>
                {messages.map((msg, index) => (
                    <li key={index} style={{ color: 'white' }}>
                        <strong>{msg.user}</strong>: {msg.content}
                    </li>
                ))}
            </ul>
            <form onSubmit={handleSubmit}>
                <input
                    type="text"
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                />
                <button type="submit">Send</button>
            </form>
        </div>
    );
}

export default App;
