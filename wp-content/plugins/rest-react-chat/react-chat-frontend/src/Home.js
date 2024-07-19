import React from 'react';
import { useNavigate } from 'react-router-dom';

function Home() {
    const navigate = useNavigate();

    const handleEnterChat = async () => {
        try {
            const response = await fetch(`${window.wpRestChat.apiUrl}rooms`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': window.wpRestChat.nonce,
                    'Content-Type': 'application/json',
                },
            });
            if (!response.ok) {
                console.error('Failed to create chat room', response);
                return;
            }
            const data = await response.json();
            const newRoomId = data.room_id;
            navigate(`/chat/${newRoomId}`);
        } catch (error) {
            console.error('Error creating chat room:', error);
        }
    };

    return (
        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
            <button onClick={handleEnterChat}>Enter Chat</button>
        </div>
    );
}

export default Home;
