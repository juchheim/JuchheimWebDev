/* global wpRestChat */

import React, { useState, useEffect } from 'react';
import axios from 'axios';

const Chat = () => {
    const [messages, setMessages] = useState([]);
    const [message, setMessage] = useState('');
    const [error, setError] = useState('');

    const fetchMessages = async () => {
        try {
            const response = await axios.get(wpRestChat.apiUrl, {
                headers: {
                    'X-WP-Nonce': wpRestChat.nonce
                }
            });
            setMessages(response.data);
        } catch (err) {
            setError('Failed to fetch messages');
        }
    };

    const postMessage = async () => {
        if (!message.trim()) return;
        try {
            const response = await axios.post(wpRestChat.apiUrl, { message }, {
                headers: {
                    'X-WP-Nonce': wpRestChat.nonce
                }
            });
            setMessages([...messages, response.data]);
            setMessage('');
        } catch (err) {
            setError('Failed to send message');
        }
    };

    useEffect(() => {
        fetchMessages();
        const interval = setInterval(fetchMessages, 3000);
        return () => clearInterval(interval);
    }, []);

    const handleSubmit = (e) => {
        e.preventDefault();
        postMessage();
    };

    return (
        <div>
            <ul>
                {messages.map((msg, index) => (
                    <li key={index}>
                        <strong>{msg.user}</strong>: {msg.message} <em>{new Date(msg.timestamp).toLocaleTimeString()}</em>
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
            {error && <p style={{color: 'red'}}>{error}</p>}
        </div>
    );
};

export default Chat;
