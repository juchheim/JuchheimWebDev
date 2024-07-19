import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Route, Routes, useParams, useNavigate } from 'react-router-dom';
import './App.css';
import Home from './Home';
import ChatRoom from './ChatRoom';
import AdminComponent from './AdminComponent'; // Ensure the import is correct
import NotFound from './NotFound';

function App() {
    return (
        <Router>
            <Routes>
                <Route path="/chat" element={<Home />} />
                <Route path="/chat/:roomId" element={<ChatRoom />} />
                <Route path="/admin" element={<AdminComponent />} />
                <Route path="*" element={<NotFound />} />
            </Routes>
        </Router>
    );
}

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
                const errorText = await response.text(); // Get the response text for debugging
                console.error('Failed to create chat room:', errorText);
                return;
            }
            const data = await response.json();
            const newRoomId = data.room_id;
            console.log('Created new chat room:', newRoomId);
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

function ChatRoom() {
    const { roomId } = useParams();
    const [messages, setMessages] = useState([]);
    const [message, setMessage] = useState('');

    useEffect(() => {
        console.log('Rendering ChatRoom component for room:', roomId);

        async function fetchMessages() {
            console.log('Fetching messages for room:', roomId);
            try {
                const response = await fetch(`${window.wpRestChat.apiUrl}messages/${roomId}`, {
                    headers: {
                        'X-WP-Nonce': window.wpRestChat.nonce,
                    },
                });
                const data = await response.json();
                console.log('Fetched messages:', data);
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
        const interval = setInterval(fetchMessages, 5000); // Poll for new messages every 5 seconds
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

function NotFound() {
    return <div>Page Not Found</div>;
}

export default App;
