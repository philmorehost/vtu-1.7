try {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('transactions.js loaded');
        const recentTransactionsContainer = document.getElementById('recent-transactions');
        console.log('recentTransactionsContainer:', recentTransactionsContainer);
        const allTransactionsContainer = document.getElementById('transactions-list');
        const viewAllBtn = document.getElementById('view-all-transactions-btn');
        const transactionHistoryModal = document.getElementById('transaction-history-modal');
        const closeTransactionHistoryModal = document.getElementById('close-transaction-history-modal');
        const transactionDetailsModal = document.getElementById('transaction-details-modal');
        const closeTransactionDetailsModal = document.getElementById('close-transaction-details-modal');

        let currentPage = 1;
        const limit = 10;

        function fetchTransactions(page = 1, limit = 5, container) {
            const offset = (page - 1) * limit;
            fetch(`api/transactions.php?limit=${limit}&offset=${offset}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Fetched transactions data:', data);
                    if (data.error) {
                        container.innerHTML = `<p class="text-red-500">${data.error}</p>`;
                        return;
                    }
                    renderTransactions(data.transactions, container);
                })
                .catch(error => {
                    console.error('Error fetching transactions:', error);
                    container.innerHTML = `<p class="text-red-500">An error occurred while fetching transactions.</p>`;
                });
        }

        function renderTransactions(transactions, container) {
            container.innerHTML = '';
            if (transactions.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No transactions found.</p>';
                return;
            }

            transactions.forEach(tx => {
                const transactionElement = document.createElement('div');
                transactionElement.className = 'p-4 border-b cursor-pointer hover:bg-gray-50';
                transactionElement.dataset.transactionId = tx.id;
                transactionElement.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold">${tx.type}</p>
                            <p class="text-sm text-gray-500">${new Date(tx.created_at).toLocaleString()}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold ${tx.amount > 0 ? 'text-green-500' : 'text-red-500'}">
                                NGN ${Math.abs(tx.amount).toFixed(2)}
                            </p>
                            <p class="text-sm text-gray-500">${tx.status}</p>
                        </div>
                    </div>
                `;
                transactionElement.addEventListener('click', () => {
                    showTransactionDetails(tx.id);
                });
                container.appendChild(transactionElement);
            });
        }

        function showTransactionDetails(transactionId) {
            fetch(`api/transaction-details.php?id=${transactionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    const detailsContainer = document.getElementById('transaction-details-content');
                    detailsContainer.innerHTML = `
                        <p><strong>ID:</strong> ${data.id}</p>
                        <p><strong>Type:</strong> ${data.type}</p>
                        <p><strong>Amount:</strong> NGN ${data.amount}</p>
                        <p><strong>Date:</strong> ${new Date(data.created_at).toLocaleString()}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <p><strong>Description:</strong> ${data.description || 'N/A'}</p>
                    `;
                    document.getElementById('print-receipt-btn').dataset.transactionId = data.id;
                    transactionDetailsModal.classList.remove('hidden');
                });
        }

        if (viewAllBtn) {
            viewAllBtn.addEventListener('click', function() {
                transactionHistoryModal.classList.remove('hidden');
                fetchTransactions(currentPage, limit, allTransactionsContainer);
            });
        }

        if (closeTransactionHistoryModal) {
            closeTransactionHistoryModal.addEventListener('click', function() {
                transactionHistoryModal.classList.add('hidden');
            });
        }

        if (closeTransactionDetailsModal) {
            closeTransactionDetailsModal.addEventListener('click', function() {
                transactionDetailsModal.classList.add('hidden');
            });
        }

        transactionDetailsModal.addEventListener('click', function(event) {
            if (event.target.id === 'print-receipt-btn') {
                const transactionId = event.target.dataset.transactionId;
                fetch(`api/transaction-details.php?id=${transactionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        const { jsPDF } = window.jspdf;
                        const doc = new jsPDF();

                        doc.setFontSize(20);
                        doc.text('Transaction Receipt', 10, 20);

                        doc.setFontSize(12);
                        doc.text(`Transaction ID: ${data.id}`, 10, 30);
                        doc.text(`User: ${data.user_name}`, 10, 40);
                        doc.text(`Email: ${data.user_email}`, 10, 50);
                        doc.text(`Date: ${new Date(data.created_at).toLocaleString()}`, 10, 60);

                        doc.autoTable({
                            startY: 70,
                            head: [['Description', 'Amount', 'Status']],
                            body: [
                                [
                                    data.type,
                                    `NGN ${data.amount}`,
                                    data.status
                                ]
                            ],
                        });

                        doc.save(`receipt-${data.id}.pdf`);
                    });
            }
        });

        // Initial load for recent transactions on the dashboard
        if (recentTransactionsContainer) {
            fetchTransactions(1, 5, recentTransactionsContainer);
        }
    });
} catch (e) {
    console.error('Error in transactions.js:', e);
    alert('A critical error occurred in the transaction script. Please check the console.');
}