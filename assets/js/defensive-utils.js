/**
 * Defensive JavaScript Utilities for Admin Panel
 * Prevents common DOM errors and null reference issues
 */

// Add this at the beginning of any JavaScript file to prevent common errors
(function() {
    'use strict';
    
    // Enhanced querySelector that doesn't throw errors
    window.safeQuerySelector = function(selector) {
        try {
            return document.querySelector(selector);
        } catch (e) {
            console.warn('Invalid selector:', selector, e);
            return null;
        }
    };
    
    // Enhanced getElementById that logs warnings for missing elements
    window.safeGetElementById = function(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn('Element not found:', id);
        }
        return element;
    };
    
    // Safe event listener addition
    window.safeAddEventListener = function(element, event, handler) {
        if (element && typeof element.addEventListener === 'function') {
            element.addEventListener(event, handler);
            return true;
        } else {
            console.warn('Cannot add event listener to element:', element);
            return false;
        }
    };
    
    // Safe fetch with error handling
    window.safeFetch = async function(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    };
    
    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('Global JavaScript error:', e.error);
        // Don't prevent default error handling
        return false;
    });
    
    // Unhandled promise rejection handler
    window.addEventListener('unhandledpromise', function(e) {
        console.error('Unhandled promise rejection:', e.reason);
    });

    /**
     * Disables a button to prevent double clicks and shows a processing state.
     * @param {HTMLButtonElement} button The button element to disable.
     * @param {string} [processingText='Processing...'] The text to display while processing.
     * @returns {function} A function to re-enable the button.
     */
    window.disableButtonOnSubmit = function(button, processingText = 'Processing...') {
        if (!button || button.disabled) {
            return () => {}; // Return a no-op function if button is invalid or already disabled
        }

        const originalHTML = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> ${processingText}`;

        // Return a function to re-enable the button
        return () => {
            button.disabled = false;
            button.innerHTML = originalHTML;
        };
    };

    window.showTransactionDetailsModal = function(transactionId) {
        fetch(`api/transaction-details.php?id=${transactionId}`)
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    const transaction = response.data;
                    const transactionDetailsContent = document.getElementById('transaction-details-content');
                    const printReceiptBtn = document.getElementById('print-receipt-btn');
                    const transactionDetailsModal = document.getElementById('transaction-details-modal');

                    if(transactionDetailsContent && printReceiptBtn && transactionDetailsModal) {
                        transactionDetailsContent.innerHTML = `
                            <p><strong>ID:</strong> ${transaction.id}</p>
                            <p><strong>Type:</strong> ${transaction.type}</p>
                            <p><strong>Description:</strong> ${transaction.description}</p>
                            <p><strong>Amount:</strong> ₦${Math.abs(transaction.amount).toFixed(2)}</p>
                            <p><strong>Balance Before:</strong> ₦${Number(transaction.balance_before).toFixed(2)}</p>
                            <p><strong>Balance After:</strong> ₦${Number(transaction.balance_after).toFixed(2)}</p>
                            <p><strong>Date:</strong> ${new Date(transaction.created_at).toLocaleString()}</p>
                            <p><strong>Status:</strong> ${transaction.status}</p>
                        `;
                        printReceiptBtn.dataset.transactionId = transaction.id;
                        transactionDetailsModal.classList.remove('hidden');
                    }
                } else {
                    alert(response.message);
                }
            })
            .catch(error => {
                console.error('Error fetching transaction details:', error);
                alert('An error occurred while fetching transaction details.');
            });
    }
    
})();

// Usage examples:
// Instead of: document.getElementById('myId')
// Use: safeGetElementById('myId')
//
// Instead of: element.addEventListener('click', handler)  
// Use: safeAddEventListener(element, 'click', handler)
//
// Instead of: fetch('/api/endpoint').then(r => r.json())
// Use: safeFetch('/api/endpoint')