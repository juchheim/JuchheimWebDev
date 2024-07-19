import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

function HomeComponent() {
    const [showChat, setShowChat] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        console.log('Rendering HomeComponent');
    }, []);

    const handleEnterChat = async () => {
        console.log('Enter Chat button clicked');
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
            console.log('Creating new chat room with ID:', newRoomId);
            navigate(`/chat/${newRoomId}`);
        } catch (error) {
            console.error('Error creating chat room:', error);
        }
    };

    if (!showChat) {
        console.log('Displaying Enter Chat button');
        return (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                <button onClick={handleEnterChat}>Enter Chat</button>
            </div>
        );
    }

    return null;
}

export default HomeComponent;
