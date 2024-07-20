import React from 'react';
import { useNavigate } from 'react-router-dom';

const SubscriberHome = () => {
    const navigate = useNavigate();

    const handleCreateChatRoom = async () => {
        try {
            const response = await fetch(`${window.wpRestChat.apiUrl}rooms`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': window.wpRestChat.nonce,
                    'Content-Type': 'application/json',
                },
            });
            const data = await response.json();
            navigate(`/chat/${data.room_id}`);
        } catch (error) {
            console.error('Error creating chat room:', error);
        }
    };

    return (
        <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
            <button onClick={handleCreateChatRoom}>Create Chat Room</button>
        </div>
    );
};

export default SubscriberHome;
