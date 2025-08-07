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
                    if (data.success && data.messages) {
                        data.messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            messageElement.classList.add('mb-2', 'clear-both');

                            // is_admin_sender is now a boolean provided by the API
                            if (message.is_admin_sender) {
                                // Admin's message: right-aligned, blue background
                                messageElement.classList.add('text-right');
                                messageElement.innerHTML = `<span class="bg-blue-500 text-white py-1 px-3 rounded-lg inline-block max-w-xs">${message.message}</span>`;
                            } else {
                                // User's message: left-aligned, gray background
                                messageElement.classList.add('text-left');
                                messageElement.innerHTML = `<span class="bg-gray-300 py-1 px-3 rounded-lg inline-block max-w-xs">${message.message}</span>`;
                            }
                            chatMessages.appendChild(messageElement);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    } else if (data.message) {
                         chatMessages.innerHTML = `<p class="text-center text-gray-500">${data.message}</p>`;
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