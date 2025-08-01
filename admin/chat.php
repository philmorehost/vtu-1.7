<?php
require_once('../includes/session_config.php');
require_once('../includes/db.php');
require_once('includes/header.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->query("SELECT DISTINCT u.id, u.name FROM users u JOIN chat c ON u.id = c.sender_id WHERE c.recipient_id = 1");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Chat Manager</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
            <h2 class="text-xl font-semibold mb-2">Users</h2>
            <ul class="bg-white rounded-lg shadow-md p-4">
                <?php if (empty($users)): ?>
                    <li class="text-gray-500">No users have started a chat yet.</li>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <li class="mb-2">
                            <a href="?user_id=<?= $user['id'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($user['name']) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class="md:col-span-2">
            <?php if (isset($_GET['user_id'])):
                $user_id = $_GET['user_id'];
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
                <h2 class="text-xl font-semibold mb-2">Chat with <?= htmlspecialchars($user['name']) ?></h2>
                <div id="chat-messages" class="bg-gray-100 p-4 rounded-lg h-64 overflow-y-auto flex flex-col mb-4">
                    <!-- Chat messages will be loaded here -->
                </div>
                <form id="chat-form">
                    <div class="flex">
                        <input type="hidden" id="user_id" value="<?= $user_id ?>">
                        <input type="text" id="chat-message-input" class="flex-grow p-3 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type your message...">
                        <button type="submit" class="bg-blue-600 text-white p-3 rounded-r-lg font-semibold hover:bg-blue-700 transition-colors">Send</button>
                    </div>
                </form>
            <?php else: ?>
                <p>Select a user to start chatting.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.admin-message {
    background-color: #d4edda;
    color: #155724;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 6px;
    max-width: 75%;
    align-self: flex-end; /* admin on right */
    text-align: right;
}

.user-message {
    background-color: #f1f1f1;
    color: #333;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 6px;
    max-width: 75%;
    align-self: flex-start; /* user on left */
    text-align: left;
}
</style>

<script>
    if (document.getElementById('chat-form')) {
        const userId = document.getElementById('user_id').value;

        function loadChatMessages() {
            fetch(`../api/get_messages.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const chatMessages = document.getElementById('chat-messages');
                    chatMessages.innerHTML = '';
                    if (data.messages) {
                        data.messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            if (message.sender_id == 1) {
                                messageElement.className = 'admin-message';
                                messageElement.innerHTML = `${message.message}`;
                            } else {
                                messageElement.className = 'user-message';
                                messageElement.innerHTML = `${message.message}`;
                            }
                            chatMessages.appendChild(messageElement);
                        });
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                });
        }

        loadChatMessages();

        document.getElementById('chat-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const message = document.getElementById('chat-message-input').value;
            if (message.trim() === '') {
                return;
            }

            fetch('../api/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message, recipient_id: userId })
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
    }
</script>

<?php require_once('includes/footer.php'); ?>