document.addEventListener('DOMContentLoaded', function() {
    console.log('transactions.js loaded');
    const allTransactionsContainer = document.getElementById('transactions-list');
    const viewAllBtn = document.getElementById('view-all-transactions-btn');
    const transactionHistoryModal = document.getElementById('transaction-history-modal');
    const closeTransactionHistoryModal = document.getElementById('close-transaction-history-modal');
    const transactionDetailsModal = document.getElementById('transaction-details-modal');
    const closeTransactionDetailsModal = document.getElementById('close-transaction-details-modal');
    const recentTransactionsContainer = document.getElementById('recent-transactions');

    let currentPage = 1;
    const limit = 10;

    function fetchAllTransactions() {
        return fetch(`api/transactions.php`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return [];
                } else {
                    return data.transactions;
                }
            })
            .catch(error => {
                console.error('Error fetching transactions:', error);
                return [];
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

    function renderTransactions(transactions, page, limit, container) {
        container.innerHTML = '';
        const startIndex = (page - 1) * limit;
        const endIndex = startIndex + limit;
        const transactionsToDisplay = transactions.slice(startIndex, endIndex);

        if (transactionsToDisplay.length === 0) {
            container.innerHTML = '<li class="p-4 text-gray-500 text-center">No transactions found.</li>';
            return;
        }

        transactionsToDisplay.forEach(txn => {
            const li = document.createElement('li');
            li.classList.add('flex', 'justify-between', 'items-center', 'py-3', 'px-4', 'hover:bg-gray-50', 'cursor-pointer');
            li.dataset.transactionId = txn.id;

            let statusHtml = `<span class="font-semibold ${txn.status === 'Completed' ? 'text-green-600' : (txn.status === 'Failed' ? 'text-red-600' : 'text-yellow-600')}">${txn.status}</span>`;
            if (txn.status === 'Pending') {
                statusHtml += ` <button class="requery-btn text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600" data-id="${txn.id}">Requery</button>`;
            }

            li.innerHTML = `
                <div>
                    <p class="font-medium text-gray-800">${txn.description}</p>
                    <p class="text-xs text-gray-500">${new Date(txn.created_at).toLocaleString()}</p>
                    <p class="text-sm">${statusHtml}</p>
                </div>
                <p class="font-semibold ${txn.amount < 0 ? 'text-red-600' : 'text-green-600'}">
                    ${txn.amount < 0 ? '-' : '+'}₦${Math.abs(txn.amount).toFixed(2)}
                </p>
            `;
            container.appendChild(li);
        });
    }

    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', async function() {
            transactionHistoryModal.classList.remove('hidden');
            const transactions = await fetchAllTransactions();
            renderTransactions(transactions, currentPage, limit, allTransactionsContainer);
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
            fetchAllTransactions().then(transactions => {
                renderTransactions(transactions, 1, 5, recentTransactionsContainer);
            });
        }
});