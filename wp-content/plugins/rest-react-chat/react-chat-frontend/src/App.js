import React from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import './App.css';
import SubscriberHome from './SubscriberHome';
import AdminChat from './AdminChat';
import ChatRoom from './ChatRoom';

function App() {
    return (
        <Router>
            <Routes>
                <Route path="/chat" element={<SubscriberHome />} />
                <Route path="/admin-chat" element={<AdminChat />} />
                <Route path="/chat/:roomId" element={<ChatRoom />} />
                <Route path="*" element={<NotFound />} />
            </Routes>
        </Router>
    );
}

function NotFound() {
    console.log('Rendering NotFound component');
    return <div>Page Not Found</div>;
}

export default App;
