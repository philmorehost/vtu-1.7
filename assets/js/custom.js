 document.getElementById('chat-with-admin-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('chat-modal').classList.remove('hidden');
            loadChatMessages();
        });

        document.getElementById('close-chat-modal').addEventListener('click', () => {
            document.getElementById('chat-modal').classList.add('hidden');
        });

        document.getElementById('chat-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const message = document.getElementById('chat-message-input').value;
            if (message.trim() === '') {
                return;
            }

            fetch('api/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('chat-message-input').value = '';
                    loadChatMessages();
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('An error occurred while sending the message.');
            });
        });

        function loadChatMessages() {
            fetch('api/get_messages.php')
                .then(response => response.json())
                .then(data => {
                    const chatMessages = document.getElementById('chat-messages');
                    chatMessages.innerHTML = '';
                    if (data.messages) {
                        data.messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            messageElement.classList.add('mb-2');
                            // Fix: Check if message is from admin (sender_id == 1) to show admin prefix
                            if (message.sender_id == 1) {
                                // Admin message - show with admin prefix
                                messageElement.innerHTML = `<span class="bg-gray-300 py-1 px-3 rounded-lg"> ${message.message}</span>`;
                            } else if (message.sender_id == data.user_id) {
                                // User's own message - show on right side
                                messageElement.classList.add('text-right');
                                messageElement.innerHTML = `<span class="bg-blue-500 text-white py-1 px-3 rounded-lg">${message.message}</span>`;
                            } else {
                                // Other user message (should not happen in 1-on-1 chat with admin)
                                messageElement.innerHTML = `<span class="bg-gray-300 py-1 px-3 rounded-lg">${message.message}</span>`;
                            }
                            chatMessages.appendChild(messageElement);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                });
        }

        document.getElementById('withdraw-to-wallet-btn').addEventListener('click', function() {
            const form = document.getElementById('withdraw-form');
            form.action = 'withdrawal_actions.php?action=wallet';
            form.submit();
        });

        document.getElementById('close-referral-details-modal').addEventListener('click', () => {
            document.getElementById('referral-details-modal').classList.add('hidden');
        });