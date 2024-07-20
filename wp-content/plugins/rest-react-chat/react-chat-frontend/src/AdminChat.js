import React, { useState, useEffect } from 'react';
import axios from 'axios';

const AdminChat = () => {
    const [chatRooms, setChatRooms] = useState([]);
    const [error, setError] = useState('');

    const fetchChatRooms = async () => {
        try {
            const response = await axios.get(`${window.wpRestChat.apiUrl}rooms`, {
                headers: {
                    'X-WP-Nonce': window.wpRestChat.nonce
                }
            });
            setChatRooms(response.data);
        } catch (err) {
            setError('Failed to fetch chat rooms');
        }
    };

    useEffect(() => {
        fetchChatRooms();
    }, []);

    return (
        <div>
            <h1>Active Chat Rooms</h1>
            <ul>
                {chatRooms.map((room, index) => (
                    <li key={index}>
                        <a href={`/chat/${room.id}`}>Chat Room {room.id}</a>
                    </li>
                ))}
            </ul>
            {error && <p style={{color: 'red'}}>{error}</p>}
        </div>
    );
};

export default AdminChat;
