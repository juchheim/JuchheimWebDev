document.addEventListener('DOMContentLoaded', function() {
    const adminElement = document.getElementById('admin-dashboard');

    if (adminElement) {
        fetch(`${wpRestChat.apiUrl}rooms`, {
            headers: {
                'X-WP-Nonce': wpRestChat.nonce,
            },
        })
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                const roomsList = document.createElement('ul');

                data.forEach(room => {
                    const roomItem = document.createElement('li');
                    roomItem.textContent = `Room ID: ${room.id} - Created At: ${new Date(room.created_at).toLocaleString()}`;
                    
                    const joinButton = document.createElement('button');
                    joinButton.textContent = 'Join';
                    joinButton.addEventListener('click', () => {
                        window.location.href = `/chat/${room.id}`;
                    });

                    roomItem.appendChild(joinButton);
                    roomsList.appendChild(roomItem);
                });

                adminElement.appendChild(roomsList);
            } else {
                console.error('Unexpected response data:', data);
            }
        })
        .catch(error => console.error('Error fetching chat rooms:', error));
    }
});
