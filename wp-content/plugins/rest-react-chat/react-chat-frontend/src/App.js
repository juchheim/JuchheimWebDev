import React from 'react';
import { BrowserRouter as Router, Route, Routes, useParams, useNavigate } from 'react-router-dom';
import './App.css';
import Home from './Home';
import ChatRoom from './ChatRoom';
import AdminComponent from './AdminComponent';
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

export default App;
