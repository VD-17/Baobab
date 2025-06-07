let currentReceiverId = null;
let messageInterval = null;

function startChat(receiverId, receiverName) {
    console.log('Starting chat with:', receiverName, 'ID:', receiverId);
    
    if (!receiverId || !receiverName) {
        console.error('Invalid receiver ID or name');
        alert('Invalid receiver information');
        return;
    }
    
    currentReceiverId = receiverId;
    document.getElementById('receiverName').textContent = receiverName;
    
    // Clear any existing interval
    if (messageInterval) {
        clearInterval(messageInterval);
    }
    
    // Load initial messages
    loadMessages();
    
    // Poll for new messages every 3 seconds
    messageInterval = setInterval(loadMessages, 3000);
    // document.querySelector('.message-input button').onclick = sendMessage;
}

function loadMessages() {
    if (!currentReceiverId) {
        console.error('No current receiver ID');
        return;
    }
    
    const url = `../api/messaging.php?action=get_messages&other_user_id=${currentReceiverId}`;
    console.log('Loading messages from:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Get as text first to see what we're receiving
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                if (data.success) {
                    const messagesList = document.getElementById('messagesList');
                    messagesList.innerHTML = '';
                    
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            const messageDiv = document.createElement('div');
                            const isCurrentUser = message.sender_id == getCurrentUserId();
                            messageDiv.className = `message ${isCurrentUser ? 'sent' : 'received'}`;
                            
                            const messageTime = new Date(message.sent_at).toLocaleTimeString([], {
                                hour: '2-digit', 
                                minute: '2-digit'
                            });
                            
                            messageDiv.innerHTML = `
                                <div>${escapeHtml(message.message)}</div>
                                <small style="opacity: 0.7; font-size: 0.8em;">${messageTime}</small>
                            `;
                            messagesList.appendChild(messageDiv);
                        });
                    } else {
                        messagesList.innerHTML = '<div class="no-messages">No messages yet. Start the conversation!</div>';
                    }
                    
                    // Scroll to bottom
                    messagesList.scrollTop = messagesList.scrollHeight;
                } else {
                    console.error('Error loading messages:', data.error);
                    const messagesList = document.getElementById('messagesList');
                    messagesList.innerHTML = `<div class="no-messages" style="color: red;">Error: ${data.error}</div>`;
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Raw text was:', text);
                const messagesList = document.getElementById('messagesList');
                messagesList.innerHTML = '<div class="no-messages" style="color: red;">Error loading messages</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
            const messagesList = document.getElementById('messagesList');
            messagesList.innerHTML = '<div class="no-messages" style="color: red;">Network error loading messages</div>';
        });
}

// function sendMessage() {
//     const messageText = document.getElementById('messageText');
//     const message = messageText.value.trim();
    
//     console.log('Attempting to send message:', message);
//     console.log('Current receiver ID:', currentReceiverId);
//     console.log('Current user ID:', getCurrentUserId());
    
//     if (!message) {
//         alert('Please enter a message');
//         return;
//     }
    
//     if (!currentReceiverId) {
//         alert('No recipient selected');
//         return;
//     }
    
//     if (getCurrentUserId() === null) {
//         alert('You must be logged in to send messages');
//         return;
//     }
    
//     if (getCurrentUserId() === currentReceiverId) {
//         alert('You cannot send messages to yourself');
//         return;
//     }
    
//     // Disable send button temporarily
//     const sendButton = document.querySelector('#messageInput button');
//     const originalText = sendButton.textContent;
//     sendButton.disabled = true;
//     sendButton.textContent = 'Sending...';
    
//     const formData = new FormData();
//     formData.append('action', 'send_message');
//     formData.append('receiver_id', currentReceiverId);
//     formData.append('message', message);
    
//     // Debug: Log what we're sending
//     console.log('Sending data:', {
//         action: 'send_message',
//         receiver_id: currentReceiverId,
//         message: message
//     });
    
//     fetch('../api/messaging.php', {
//         method: 'POST',
//         body: formData
//     })
//     .then(response => {
//         console.log('Send response status:', response.status);
        
//         if (!response.ok) {
//             throw new Error(`HTTP error! status: ${response.status}`);
//         }
//         return response.text(); // Get as text first
//     })
//     .then(text => {
//         console.log('Send response text:', text);
        
//         try {
//             const data = JSON.parse(text);
//             console.log('Send response data:', data);
            
//             if (data.success) {
//                 messageText.value = '';
//                 loadMessages(); // Reload messages to show the new one
//                 console.log('Message sent successfully');
//             } else {
//                 console.error('Server error:', data.error);
//                 alert('Failed to send message: ' + (data.error || 'Unknown error'));
//             }
//         } catch (parseError) {
//             console.error('JSON parse error in send response:', parseError);
//             console.error('Raw response was:', text);
//             alert('Error: Invalid response from server');
//         }
//     })
//     .catch(error => {
//         console.error('Error sending message:', error);
//         alert('Error sending message: ' + error.message);
//     })
//     .finally(() => {
//         // Re-enable send button
//         sendButton.disabled = false;
//         sendButton.textContent = originalText;
//     });
// }

function sendMessage() {
    const messageText = document.getElementById('messageText');
    const message = messageText.value.trim();
    console.log('Attempting to send message:', message);
    console.log('Current receiver ID:', currentReceiverId);
    console.log('Current user ID:', getCurrentUserId());
    if (!message) {
        alert('Please enter a message');
        return;
    }
    console.log('Checking currentReceiverId before send:', currentReceiverId);
    if (!currentReceiverId || isNaN(currentReceiverId)) {
        alert('No recipient selected');
        return;
    }
    if (getCurrentUserId() === null) {
        alert('You must be logged in to send messages');
        return;
    }
    if (getCurrentUserId() === currentReceiverId) {
        alert('You cannot send messages to yourself');
        return;
    }

    const sendButton = document.querySelector('.message-input button');
    if (!sendButton) {
        console.error('Send button not found in DOM');
        alert('Error: Send button not available');
        return;
    }

    const originalText = sendButton.textContent;
    sendButton.disabled = true; 
    sendButton.textContent = 'Sending...';
    const formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('receiver_id', currentReceiverId);
    formData.append('message', message);
    console.log('Sending data:', {
        action: 'send_message',
        receiver_id: currentReceiverId,
        message: message
    });
    fetch('../api/messaging.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Send response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Send response text:', text);
        try {
            const data = JSON.parse(text);
            console.log('Send response data:', data);
            if (data.success) {
                messageText.value = '';
                loadMessages();
                console.log('Message sent successfully');
            } else {
                console.error('Server error:', data.error);
                alert('Failed to send message: ' + (data.error || 'Unknown error'));
            }
        } catch (parseError) {
            console.error('JSON parse error in send response:', parseError, 'Raw response:', text);
            alert('Error: Invalid response from server');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message: ' + error.message);
    })
    .finally(() => {
        sendButton.disabled = false;
        sendButton.textContent = originalText;
    });
}

function checkEnter(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        sendMessage();
    }
}

function closeChat() {
    if (messageInterval) {
        clearInterval(messageInterval);
        messageInterval = null;
    }
    currentReceiverId = null;
    document.getElementById('messageContainer').style.display = 'none';
}

// function close() {
//     if (messageInterval) {
//         clearInterval(messageInterval);
//         messageInterval = null;
//     }
//     currentReceiverId = null;
//     document.getElementById('message-container').style.display = 'none';
// }

// Helper function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
    if (messageInterval) {
        clearInterval(messageInterval);
    }
});