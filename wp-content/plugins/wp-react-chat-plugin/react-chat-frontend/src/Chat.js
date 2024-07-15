import React, { useState, useEffect, useRef } from 'react';
import io from 'socket.io-client';

const SOCKET_SERVER_URL = 'wss://juchheim.local:4000'; // Use your HTTPS server URL

const Chat = ({ token }) => {
    const [messages, setMessages] = useState([]);
    const [message, setMessage] = useState('');
    const socketRef = useRef(null);

    console.log('Chat component rendered with token:', token);

    useEffect(() => {
        console.log('Socket connection attempt');

        socketRef.current = io(SOCKET_SERVER_URL, {
            auth: { token },
            transports: ['websocket']
        });

        const socket = socketRef.current;

        socket.on('connect', () => {
            console.log('Socket connected');
        });

        socket.on('connect_error', (error) => {
            console.error('Socket connection error:', error);
        });

        socket.on('disconnect', (reason) => {
            console.log('Socket disconnected:', reason);
            if (reason === 'io server disconnect') {
                // The disconnection was initiated by the server, reconnect manually
                console.log('Server disconnected, attempting to reconnect');
                socket.connect();
            } else if (reason === 'io client disconnect') {
                // The disconnection was initiated by the client, do not reconnect
                console.log('Client disconnected, will not reconnect automatically');
            } else {
                console.log('Disconnected for another reason, attempting to reconnect');
                socket.connect();
            }
        });

        socket.on('error', (error) => {
            console.error('Socket error:', error);
        });

        socket.on('previous messages', (previousMessages) => {
            console.log('Received previous messages:', previousMessages);
            setMessages(previousMessages);
        });

        socket.on('chat message', (msg) => {
            console.log('Received message:', msg);
            setMessages((prevMessages) => [...prevMessages, msg]);
        });

        return () => {
            console.log('Socket connection closed');
            socket.disconnect();
        };
    }, [token]);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (message.trim()) {
            console.log('Sending message:', message);
            socketRef.current.emit('chat message', message);
            setMessage('');
        }
    };

    return (
        <div style={{ color: 'white' }}>
            <ul style={{ color: 'white' }}>
                {messages.map((msg, index) => (
                    <li key={index} style={{ color: 'white' }}>
                        <strong>{msg.user || 'Anonymous'}</strong>: {msg.message} <em>{new Date(msg.timestamp).toLocaleTimeString()}</em>
                    </li>
                ))}
            </ul>
            <form onSubmit={handleSubmit}>
                <input
                    type="text"
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    style={{ color: 'black' }} // Input text should be visible
                />
                <button type="submit">Send</button>
            </form>
        </div>
    );
};

export default Chat;
