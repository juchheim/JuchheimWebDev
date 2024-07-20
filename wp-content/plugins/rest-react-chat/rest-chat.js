jQuery(document).ready(function($) {
    console.log('REST Chat script loaded.');

    let intervalId = null;

    // Create chat room button for subscribers
    $('#create-room-button').on('click', function() {
        $.ajax({
            url: restChatSettings.apiUrl + 'rooms',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', restChatSettings.nonce);
            },
            success: function(response) {
                console.log('Room created:', response);
                // Hide the create room button after creation
                $('#create-room-button').hide();
                // Display the new room
                $('#room-id').val(response.room_id);
                $('#chat-room-container').show();
                addRoomToList(response.room_id);
                startPolling(response.room_id); // Start polling for new messages
            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    });

    // List chat rooms for administrators
    if ($('#chat-rooms-list').length > 0) {
        $.ajax({
            url: restChatSettings.apiUrl + 'rooms',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', restChatSettings.nonce);
            },
            success: function(response) {
                console.log('Rooms listed:', response);
                var roomsHtml = '<ul>';
                response.forEach(function(room) {
                    roomsHtml += '<li><button class="room-button" data-room-id="' + room.id + '">Room ' + room.id + ' - Created At: ' + room.created_at + '</button></li>';
                });
                roomsHtml += '</ul>';
                $('#chat-rooms-list').html(roomsHtml);
            },
            error: function(error) {
                console.log('Error:', error);
            }
        });

        // Handle room button click
        $(document).on('click', '.room-button', function() {
            var roomId = $(this).data('room-id');
            $('#room-id').val(roomId);
            $('#chat-room-container').show();
            $('#chat-rooms-list').hide(); // Hide the chat room buttons
            fetchMessages(roomId);
            startPolling(roomId); // Start polling for new messages
        });
    }

    // Fetch messages in a chat room
    function fetchMessages(roomId) {
        $.ajax({
            url: restChatSettings.apiUrl + 'messages/' + roomId,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', restChatSettings.nonce);
            },
            success: function(response) {
                console.log('Messages fetched:', response);
                var messagesHtml = '<ul>';
                response.forEach(function(message) {
                    messagesHtml += '<li><strong>' + message.user + ':</strong> ' + message.content + ' <span class="timestamp">(' + message.timestamp + ')</span></li>';
                });
                $('#chat-messages').html(messagesHtml);
            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    }

    // Post a message in a chat room
    $('#post-message-button').on('click', function() {
        var roomId = $('#room-id').val();
        var messageContent = $('#message-content').val();
        $.ajax({
            url: restChatSettings.apiUrl + 'messages/' + roomId,
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', restChatSettings.nonce);
            },
            data: JSON.stringify({
                content: messageContent,
                user: restChatSettings.currentUser,
                room_id: roomId
            }),
            contentType: 'application/json',
            success: function(response) {
                console.log('Message posted:', response);
                fetchMessages(roomId); // Fetch messages immediately after posting
                $('#message-content').val('');
            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    });

    // Add new room to the list for administrators
    function addRoomToList(roomId) {
        var roomHtml = '<li><button class="room-button" data-room-id="' + roomId + '">Room ' + roomId + ' - Created At: Just now</button></li>';
        $('#chat-rooms-list ul').prepend(roomHtml); // Add new room to the top of the list
    }

    // Start polling for new messages every 3 seconds
    function startPolling(roomId) {
        if (intervalId) {
            clearInterval(intervalId);
        }
        intervalId = setInterval(function() {
            fetchMessages(roomId);
        }, 3000); // Poll every 3 seconds
    }

    // Automatically fetch messages for a specific room if roomId is set
    var roomId = $('#room-id').val();
    if (roomId) {
        fetchMessages(roomId);
        startPolling(roomId); // Start polling for new messages
    }
});
