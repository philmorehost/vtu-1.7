document.addEventListener('DOMContentLoaded', function() {
    console.log('transactions.js loaded');
    const allTransactionsContainer = document.getElementById('transactions-list');
    const viewAllBtn = document.getElementById('view-all-transactions-btn');
    const transactionHistoryModal = document.getElementById('transaction-history-modal');
    const closeTransactionHistoryModal = document.getElementById('close-transaction-history-modal');
    const transactionDetailsModal = document.getElementById('transaction-details-modal');
    const closeTransactionDetailsModal = document.getElementById('close-transaction-details-modal');

    let currentPage = 1;
    const limit = 10;

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
    }