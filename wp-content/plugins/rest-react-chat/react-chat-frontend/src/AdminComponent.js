import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

function AdminComponent() {
    const [rooms, setRooms] = useState([]);
    const navigate = useNavigate();

    useEffect(() => {
        async function fetchRooms() {
            try {
                const response = await fetch(`${window.wpRestChat.apiUrl}rooms`, {
                    headers: {
                        'X-WP-Nonce': window.wpRestChat.nonce,
                    },
                });
                const data = await response.json();
                if (Array.isArray(data)) {
                    setRooms(data);
                } else {
                    console.error('Unexpected response data:', data);
                }
            } catch (error) {
                console.error('Error fetching chat rooms:', error);
            }
        }

        fetchRooms();
    }, []);

    const handleJoinRoom = (roomId) => {
        navigate(`/chat/${roomId}`);
    };

    return (
        <div>
            <h2>Admin Dashboard</h2>
            <ul>
                {rooms.map((room) => (
                    <li key={room.id}>
                        Room ID: {room.id} - Created At: {new Date(room.created_at).toLocaleString()}
                        <button onClick={() => handleJoinRoom(room.id)}>Join</button>
                    </li>
                ))}
            </ul>
        </div>
    );
}

export default AdminComponent;
