<?php
$title = 'Dashboard';
require_once('includes/header.php');

// Fetch stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_transactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE amount < 0")->fetchColumn();

// Fetch recent transactions
$stmt = $pdo->query("SELECT t.*, u.name as user_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5");
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent chat messages for the quick view
$recentMessages = [];
try {
    // Fetch the 5 most recent unread messages for the admin (assuming admin ID is 1)
    $stmt = $pdo->prepare("
        SELECT c.id, c.sender_id, c.message, c.created_at, u.name as username
        FROM chat c
        LEFT JOIN users u ON c.sender_id = u.id
        WHERE c.recipient_id = ? AND c.is_read = 0
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([1]); // Replace 1 with the actual admin ID if different
    $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dashboard - Error fetching recent chats: " . $e->getMessage());
}

// --- Determine initial user for quick chat (optional) ---
// Example: Get the user ID of the most recent message
$initialChatUserId = null;
$initialChatUserName = "Select a user";
if (!empty($recentMessages)) {
    $initialChatUserId = $recentMessages[0]['sender_id'];
    $initialChatUserName = $recentMessages[0]['username'] ?? "User ID: " . $initialChatUserId;
}
// --- End Determine initial user ---
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold">Total Users</h3>
        <p class="text-3xl mt-2"><?= htmlspecialchars($total_users) ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold">Total Transactions</h3>
        <p class="text-3xl mt-2"><?= htmlspecialchars($total_transactions) ?></p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold">Total Revenue</h3>
        <p class="text-3xl mt-2">₦<?= htmlspecialchars(number_format(abs((float)($total_revenue ?? 0)), 2)) ?></p>
    </div>
</div>

<!-- Chat Section Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Recent Chats Section -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Recent Chats</h2>
            <a href="chat.php" class="text-blue-500 hover:underline text-sm">View All Chats</a>
        </div>

        <?php if (empty($recentMessages)): ?>
            <p class="text-gray-500">No recent messages.</p>
        <?php else: ?>
            <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                <?php foreach ($recentMessages as $msg): ?>
                    <div class="border-b pb-3 last:border-0 last:pb-0 cursor-pointer hover:bg-gray-50 p-2 rounded chat-user-selector"
                         data-user-id="<?= htmlspecialchars($msg['sender_id']) ?>"
                         data-user-name="<?= htmlspecialchars($msg['username'] ?? 'User ID: ' . $msg['sender_id']) ?>">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>
                                <?php if (!empty($msg['username'])): ?>
                                    <strong><?= htmlspecialchars($msg['username']) ?>:</strong>
                                <?php else: ?>
                                    <strong>User ID: <?= htmlspecialchars($msg['sender_id']) ?>:</strong>
                                <?php endif; ?>
                            </span>
                            <span><?= date('M j, g:i A', strtotime($msg['created_at'])); ?></span>
                        </div>
                        <p class="mt-1 break-words truncate"><?= htmlspecialchars($msg['message']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Chat Response Box -->
    <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-full">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Quick Chat</h2>
            <span id="quick-chat-user" class="text-sm text-gray-600">
                <?php if ($initialChatUserId): ?>
                    Chatting with: <?= htmlspecialchars($initialChatUserName) ?>
                    <input type="hidden" id="quick-chat-user-id" value="<?= htmlspecialchars($initialChatUserId) ?>">
                <?php else: ?>
                    No user selected
                <?php endif; ?>
            </span>
        </div>

        <?php if ($initialChatUserId): ?>
            <!-- Chat Messages Area -->
            <div id="quick-chat-messages" class="bg-gray-100 p-4 rounded-lg flex-grow mb-4 overflow-y-auto max-h-64 md:max-h-80">
                <!-- Messages will be loaded here by JavaScript -->
                <p class="text-gray-500 text-center">Select a user or start typing...</p>
            </div>

            <!-- Chat Input Form -->
            <form id="quick-chat-form" class="flex">
                <input type="text" id="quick-chat-message-input" class="flex-grow p-3 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type your message..." required>
                <button type="submit" class="bg-blue-600 text-white p-3 rounded-r-lg font-semibold hover:bg-blue-700 transition-colors">Send</button>
            </form>
        <?php else: ?>
            <p class="text-gray-500 flex-grow flex items-center justify-center">No recent chats to respond to.</p>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Quick Links -->
    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Quick Links</h2>
        <div class="grid grid-cols-2 gap-4">
            <a href="users.php" class="bg-blue-500 text-white text-center py-4 rounded-lg hover:bg-blue-700">Manage Users</a>
            <a href="transactions.php" class="bg-blue-500 text-white text-center py-4 rounded-lg hover:bg-blue-700">Transactions</a>
            <a href="payment_orders.php" class="bg-blue-500 text-white text-center py-4 rounded-lg hover:bg-blue-700">Payment Orders</a>
            <a href="bank_settings.php" class="bg-blue-500 text-white text-center py-4 rounded-lg hover:bg-blue-700">Bank Settings</a>
            <a href="services.php" class="bg-blue-500 text-white text-center py-4 rounded-lg hover:bg-blue-700">Services</a>
            <a href="site_settings.php" class="bg-blue-500 text-white text-center py-4 rounded-lg hover:bg-blue-700">Site Settings</a>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md table-container">
        <h2 class="text-2xl font-bold mb-4">Recent Transactions</h2>
        <table class="min-w-full bg-white">
            <tbody class="text-gray-700">
                <?php foreach ($recent_transactions as $transaction): ?>
                    <tr class="border-b">
                        <td class="py-2 px-4"><?= htmlspecialchars($transaction['user_name']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($transaction['description']) ?></td>
                        <td class="py-2 px-4">
                            <span class="<?= $transaction['amount'] < 0 ? 'text-red-500' : 'text-green-500' ?>">
                                ₦<?= htmlspecialchars(number_format(abs($transaction['amount']), 2)) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
<?php if ($initialChatUserId): ?>
document.addEventListener('DOMContentLoaded', function () {
    const chatMessagesContainer = document.getElementById('quick-chat-messages');
    const chatForm = document.getElementById('quick-chat-form');
    const messageInput = document.getElementById('quick-chat-message-input');
    const chatUserIdInput = document.getElementById('quick-chat-user-id');
    const chatUserDisplay = document.getElementById('quick-chat-user');

    // --- Function to load chat messages for a specific user ---
    function loadQuickChatMessages(userId) {
        if (!userId) {
            chatMessagesContainer.innerHTML = '<p class="text-gray-500 text-center">Select a user to load messages.</p>';
            return;
        }

        // Show loading indicator
        chatMessagesContainer.innerHTML = '<p class="text-gray-500 text-center">Loading messages...</p>';

        fetch(`../api/get_messages.php?user_id=${encodeURIComponent(userId)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                chatMessagesContainer.innerHTML = ''; // Clear container
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('mb-2');
                        // Check if the message was sent by the admin (assuming admin ID is 1)
                        if (message.sender_id == 1) { // Adjust admin ID if needed
                            messageElement.classList.add('text-right');
                            messageElement.innerHTML = `<span class="inline-block bg-blue-500 text-white py-1 px-3 rounded-lg max-w-xs md:max-w-md break-words">${message.message}</span>`;
                        } else {
                            messageElement.innerHTML = `<span class="inline-block bg-gray-300 py-1 px-3 rounded-lg max-w-xs md:max-w-md break-words">${message.message}</span>`;
                        }
                        chatMessagesContainer.appendChild(messageElement);
                    });
                    // Scroll to the bottom
                    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
                } else {
                    chatMessagesContainer.innerHTML = '<p class="text-gray-500 text-center">No messages yet.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading quick chat messages:', error);
                chatMessagesContainer.innerHTML = '<p class="text-red-500 text-center">Failed to load messages.</p>';
            });
    }

    // --- Load messages for the initial user when the page loads ---
    loadQuickChatMessages(chatUserIdInput.value);

    // --- Handle user selection from Recent Chats ---
    document.querySelectorAll('.chat-user-selector').forEach(item => {
        item.addEventListener('click', function () {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');

            // Update hidden input and display
            chatUserIdInput.value = userId;
            chatUserDisplay.textContent = `Chatting with: ${userName}`;

            // Load messages for the selected user
            loadQuickChatMessages(userId);
        });
    });

    // --- Handle sending a new message ---
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        const recipientId = chatUserIdInput.value;

        if (message === '' || !recipientId) {
            alert('Please enter a message and select a user.');
            return;
        }

        // Disable input/button during send
        messageInput.disabled = true;
        chatForm.querySelector('button').disabled = true;

        fetch('../api/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
                // If you implement CSRF for API calls, add the token here
                // 'X-CSRF-Token': 'YOUR_CSRF_TOKEN_HERE' 
            },
            body: JSON.stringify({ message: message, recipient_id: recipientId })
        })
        .then(response => {
             if (!response.ok) {
                 // Try to get error message from JSON response
                 return response.json().then(errData => {
                     throw new Error(errData.error || `HTTP error! status: ${response.status}`);
                 }).catch(() => {
                     // If parsing JSON fails, throw generic error
                     throw new Error(`HTTP error! status: ${response.status}`);
                 });
             }
             return response.json();
        })
        .then(data => {
            if (data.success) {
                // Clear input
                messageInput.value = '';
                // Reload messages to show the new one
                loadQuickChatMessages(recipientId);
            } else {
                alert('Error sending message: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error sending quick chat message:', error);
            alert('An error occurred while sending the message: ' + error.message);
        })
        .finally(() => {
            // Re-enable input/button
            messageInput.disabled = false;
            chatForm.querySelector('button').disabled = false;
            messageInput.focus();
        });
    });
});
<?php endif; ?>
</script>

<?php require_once('includes/footer.php'); ?>