import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';

function ChatRoomComponent() {
    const { roomId } = useParams();
    const navigate = useNavigate();
    const [messages, setMessages] = useState([]);
    const [message, setMessage] = useState('');

    useEffect(() => {
        console.log(`Entering ChatRoomComponent for roomId: ${roomId}`);

        async function fetchMessages() {
            try {
                const response = await fetch(`${window.wpRestChat.apiUrl}messages/${roomId}`, {
                    headers: {
                        'X-WP-Nonce': window.wpRestChat.nonce,
                    },
                });
                const data = await response.json();
                console.log(`Fetched messages for roomId ${roomId}:`, data);
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
        const interval = setInterval(fetchMessages, 5000);
        return () => clearInterval(interval);
    }, [roomId]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (message.trim() === '') return;

        try {
            const response = await fetch(`${window.wpRestChat.apiUrl}messages/${roomId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wpRestChat.nonce,
                },
                body: JSON.stringify({
                    content: message,
                    user: window.wpRestChat.user,
                    room_id: roomId,
                }),
            });
            const newMessage = await response.json();
            console.log('Message sent:', newMessage);
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
                        <strong>{msg.user}</strong>: {msg.content} <em>({new Date(msg.timestamp).toLocaleTimeString()})</em>
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

export default ChatRoomComponent;
