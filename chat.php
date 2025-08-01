<!-- Chat Modal -->
<div id="chat-modal" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-gray-800">Chat with Admin</h3>
            <button id="close-chat-modal" class="text-gray-500 hover:text-gray-700 text-2xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chat-messages" class="bg-gray-100 p-4 rounded-lg h-64 overflow-y-auto mb-4">
            <!-- Chat messages will be loaded here -->
        </div>
        <form id="chat-form">
            <div class="flex">
                <input type="text" id="chat-message-input" class="flex-grow p-3 rounded-l-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type your message...">
                <button type="submit" class="bg-blue-600 text-white p-3 rounded-r-lg font-semibold hover:bg-blue-700 transition-colors">Send</button>
            </div>
        </form>
    </div>
</div>
