
let transactions = [];
// --- API Fetch Functions ---
function fetchTransactions() {
    return fetch(`api/transactions.php`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                transactions = [];
            } else {
                transactions = data.transactions;
            }
        })
        .catch(error => {
            console.error('Error fetching transactions:', error);
            transactions = [];
        });
}

document.addEventListener('DOMContentLoaded', () => {
    // --- Page Elements ---
    const appContainer = document.getElementById('app-container');
    const dashboardPage = document.getElementById('dashboard-page');
    const fundWalletPage = document.getElementById('fund-wallet-page');
    const servicePage = document.getElementById('service-page');
    const profilePage = document.getElementById('profile-page'); // New
    const footerNav = document.getElementById('footer-nav');

    // --- Dashboard Elements ---
    const customerNameElement = document.getElementById('customer-name');
    const walletBalanceElement = document.getElementById('wallet-balance').querySelector('.balance-value');
    const bonusBalanceElement = document.getElementById('bonus-balance').querySelector('.balance-value');
    const toggleBalanceVisibilityButton = document.getElementById('toggle-balance-visibility');
    const toggleText = document.getElementById('toggle-text');
    const balanceValues = document.querySelectorAll('.balance-value');
    const copyButtons = document.querySelectorAll('.copy-account-btn');
    const refreshButton = document.getElementById('refresh-button');
    const dashboardFundWalletButton = document.getElementById('dashboard-fund-wallet-button');
    const viewReferralsButton = document.getElementById('view-referrals-button');
    const copyReferralLinkButton = document.getElementById('copy-referral-link-button');
    const dashboardServiceButtons = document.querySelectorAll('.service-btn');
    const navButtons = document.querySelectorAll('.nav-item');
    const viewAllTransactionsBtn = document.getElementById('view-all-transactions-btn');
    const recentTransactionsContainer = document.getElementById('recent-transactions');

    // --- Fund Wallet Elements ---
    const backToDashboardFromFundButton = document.getElementById('back-to-dashboard-from-fund');
    const paymentMethodButtons = document.querySelectorAll('.payment-method-btn');

    // --- Service Page Elements ---
    const backToDashboardFromServiceButton = document.getElementById('back-to-dashboard-from-service');
    const servicePageTitle = document.getElementById('service-page-title');
    const currentServiceName = document.getElementById('current-service-name');
    const serviceForms = document.querySelectorAll('.service-form');

    // --- Data Vending Form Elements ---
    const dataFormSection = document.getElementById('data-form-section');
    const dataBulkPurchaseToggle = document.getElementById('data-bulk-purchase-toggle');
    const dataPurchaseTypeText = document.getElementById('data-purchase-type-text');
    const dataSinglePhoneInput = document.getElementById('data-single-phone-input');
    const dataBulkPhoneInput = document.getElementById('data-bulk-phone-input');
    const dataPhoneNumberInput = document.getElementById('data-phone-number');
    const dataPhoneNumbersBulkInput = document.getElementById('data-phone-numbers-bulk');
    const dataRecipientCountDisplay = document.getElementById('data-recipient-count');
    const dataDetectedNetworkDisplay = document.getElementById('data-detected-network');
    const dataNetworkOverrideToggle = document.getElementById('data-network-override-toggle');
    const dataManualNetworkSelection = document.getElementById('data-manual-network-selection');
    const dataManualNetworkSelect = document.getElementById('data-manual-network');
    const dataPlanSelect = document.getElementById('data-plan');
    const dataBulkTotalCostSection = document.getElementById('data-bulk-total-cost-section');
    const dataBulkTotalCostDisplay = document.getElementById('data-bulk-total-cost');
    const dataScheduleToggle = document.getElementById('data-schedule-toggle');
    const dataScheduleFields = document.getElementById('data-schedule-fields');
    const dataScheduleDateInput = document.getElementById('data-schedule-date');
    const dataScheduleTimeInput = document.getElementById('data-schedule-time');
    const dataVendingForm = document.getElementById('data-vending-form');

    // --- Airtime Vending Form Elements ---
    const airtimeFormSection = document.getElementById('airtime-form-section');
    const airtimeBulkPurchaseToggle = document.getElementById('airtime-bulk-purchase-toggle');
    const airtimePurchaseTypeText = document.getElementById('airtime-purchase-type-text');
    const airtimeSinglePhoneInput = document.getElementById('airtime-single-phone-input');
    const airtimeBulkPhoneInput = document.getElementById('airtime-bulk-phone-input');
    const airtimePhoneNumberInput = document.getElementById('airtime-phone-number');
    const airtimePhoneNumbersBulkInput = document.getElementById('airtime-phone-numbers-bulk');
    const airtimeRecipientCountDisplay = document.getElementById('airtime-recipient-count');
    const airtimeDetectedNetworkDisplay = document.getElementById('airtime-detected-network');
    const airtimeNetworkOverrideToggle = document.getElementById('airtime-network-override-toggle');
    const airtimeManualNetworkSelection = document.getElementById('airtime-manual-network-selection');
    const airtimeManualNetworkSelect = document.getElementById('airtime-manual-network');
    const airtimeAmountInput = document.getElementById('airtime-amount');
    const airtimeBulkTotalCostSection = document.getElementById('airtime-bulk-total-cost-section');
    const airtimeBulkTotalCostDisplay = document.getElementById('airtime-bulk-total-cost');
    const airtimeScheduleToggle = document.getElementById('airtime-schedule-toggle');
    const airtimeScheduleFields = document.getElementById('airtime-schedule-fields');
    const airtimeScheduleDateInput = document.getElementById('airtime-schedule-date');
    const airtimeScheduleTimeInput = document.getElementById('airtime-schedule-time');
    const airtimeVendingForm = document.getElementById('airtime-vending-form');

    // --- Electricity Vending Form Elements ---
    const electricityFormSection = document.getElementById('electricity-form-section');
    const electricityServiceTypeRadios = document.querySelectorAll('input[name="electricity-type"]');
    const electricityPrepaidRadio = document.getElementById('electricity-prepaid');
    const electricityPostpaidRadio = document.getElementById('electricity-postpaid');
    const meterNumberInput = document.getElementById('meter-number');
    const discoProviderSelect = document.getElementById('disco-provider');
    const electricityAmountInput = document.getElementById('electricity-amount');
    const electricityTokenSection = document.getElementById('electricity-token-section');
    const electricityTokenInput = document.getElementById('electricity-token');
    const verifyMeterBtn = document.getElementById('verify-meter-btn');
    const electricityVerificationResult = document.getElementById('electricity-verification-result');
    const verifiedCustomerNameElectricity = document.getElementById('verified-customer-name-electricity');
    const electricityVendingForm = document.getElementById('electricity-vending-form');

    // --- Cable TV Vending Form Elements ---
    const cabletvFormSection = document.getElementById('cabletv-form-section');
    const cabletvProviderSelect = document.getElementById('cabletv-provider');
    const smartCardNumberInput = document.getElementById('smart-card-number');
    const verifySmartCardBtn = document.getElementById('verify-smart-card-btn');
    const cabletvVerificationResult = document.getElementById('cabletv-verification-result');
    const verifiedCustomerName = document.getElementById('verified-customer-name');
    const verifiedSubscriptionStatus = document.getElementById('verified-subscription-status');
    const cabletvPlanSelect = document.getElementById('cabletv-plan');
    const cabletvBuyBtn = document.getElementById('cabletv-buy-btn');
    const cabletvVendingForm = document.getElementById('cabletv-vending-form');

    // --- Betting Wallet Funding Form Elements ---
    const bettingFormSection = document.getElementById('betting-form-section');
    const bettingPlatformSelect = document.getElementById('betting-platform');
    const bettingUserIdInput = document.getElementById('betting-user-id');
    const bettingAmountInput = document.getElementById('betting-amount');
    const bettingFundingForm = document.getElementById('betting-funding-form');

    // --- Exam Vending Form Elements ---
    const examFormSection = document.getElementById('exam-form-section');
    const examTypeSelect = document.getElementById('exam-type');
    const examQuantityInput = document.getElementById('exam-quantity');
    const examTotalAmountDisplay = document.getElementById('exam-total-amount');
    const examVendingForm = document.getElementById('exam-vending-form');

    // --- Bulk SMS Sending Form Elements ---
    const bulksmsFormSection = document.getElementById('bulksms-form-section');
    const smsSenderIdSelect = document.getElementById('sms-sender-id');
    const registerSenderIdBtn = document.getElementById('register-sender-id-btn');
    const usePhonebookToggle = document.getElementById('use-phonebook-toggle');
    const manualRecipientInput = document.getElementById('manual-recipient-input');
    const phonebookSelectionSection = document.getElementById('phonebook-selection-section');
    const phonebookGroupSelect = document.getElementById('phonebook-group-select');
    const managePhonebookBtn = document.getElementById('manage-phonebook-btn');
    const selectedPhonebookContactsDisplay = document.getElementById('selected-phonebook-contacts-display');
    const smsMessageTextarea = document.getElementById('sms-message');
    const charCountDisplay = document.getElementById('char-count');
    const smsUnitsDisplay = document.getElementById('sms-units');
    const recipientNumbersTextarea = document.getElementById('recipient-numbers');
    const recipientCountDisplay = document.getElementById('recipient-count');
    const bulksmsTotalCostDisplay = document.getElementById('bulksms-total-cost');
    const bulksmsScheduleToggle = document.getElementById('bulksms-schedule-toggle');
    const bulksmsScheduleFields = document.getElementById('bulksms-schedule-fields');
    const bulksmsScheduleDateInput = document.getElementById('bulksms-schedule-date');
    const bulksmsScheduleTimeInput = document.getElementById('bulksms-schedule-time');
    const bulksmsSendingForm = document.getElementById('bulksms-sending-form');
    const saveContactsToggle = document.getElementById('save-contacts-toggle');

    // --- Gift Card Buy/Sell Form Elements ---
    const giftcardFormSection = document.getElementById('giftcard-form-section');
    const giftcardBuyBtn = document.getElementById('giftcard-buy-btn');
    const giftcardSellBtn = document.getElementById('giftcard-sell-btn');
    const giftcardTypeSelect = document.getElementById('giftcard-type');
    const giftcardDenominationInput = document.getElementById('giftcard-denomination');
    const giftcardSellDetails = document.getElementById('giftcard-sell-details');
    const giftcardCodeInput = document.getElementById('giftcard-code');
    const giftcardImageInput = document.getElementById('giftcard-image');
    const giftcardEstimatedValueDisplay = document.getElementById('giftcard-estimated-value');
    const giftcardSubmitBtn = document.getElementById('giftcard-submit-btn');
    const giftcardForm = document.getElementById('giftcard-form');

    // Print Recharge Card Elements
    const rechargeCardFormSection = document.getElementById('recharge-card-form-section');
    const rechargeCardNetworkSelect = document.getElementById('recharge-card-network');
    const rechargeCardAmountSelect = document.getElementById('recharge-card-amount');
    const rechargeCardQuantityInput = document.getElementById('recharge-card-quantity');
    const rechargeCardTotalCostDisplay = document.getElementById('recharge-card-total-cost');
    const rechargeCardPrintingForm = document.getElementById('recharge-card-printing-form');

    // --- More Services Modal Elements ---
    const moreServicesBtn = document.getElementById('more-services-btn');
    const moreServicesModal = document.getElementById('more-services-modal');
    const closeMoreServicesModalBtn = document.getElementById('close-more-services-modal');
    const allServicesList = document.getElementById('all-services-list');

    // --- Phonebook Manager Modal Elements ---
    const phonebookManagerModal = document.getElementById('phonebook-manager-modal');
    const closePhonebookManagerModalBtn = document.getElementById('close-phonebook-manager-modal');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    // Add Contact Tab
    const addContactTab = document.getElementById('add-contact-tab');
    const newContactNameInput = document.getElementById('new-contact-name');
    const newContactNumberInput = document.getElementById('new-contact-number');
    const addContactGroupSelect = document.getElementById('add-contact-group-select');
    const addSingleContactBtn = document.getElementById('add-single-contact-btn');

    // Manage Groups Tab
    const manageGroupsTab = document.getElementById('manage-groups-tab');
    const newGroupNameInput = document.getElementById('new-group-name');
    const createNewGroupBtn = document.getElementById('createNewGroupBtn');
    const existingGroupsList = document.getElementById('existing-groups-list');

    // Upload Contacts Tab
    const uploadContactsTab = document.getElementById('upload-contacts-tab');
    const uploadFileGroupSelect = document.getElementById('upload-file-group-select');
    const contactUploadFile = document.getElementById('contact-upload-file');
    const uploadContactsBtn = document.getElementById('upload-contacts-btn');

    // Referral Details Modal Elements
    const referralDetailsModal = document.getElementById('referral-details-modal');
    const closeReferralDetailsModalBtn = document.getElementById('close-referral-details-modal');
    const displayReferralLinkInput = document.getElementById('display-referral-link');
    const copyModalReferralLinkButton = document.getElementById('copy-modal-referral-link-button');
    const referredUsersList = document.getElementById('referred-users-list');
    const totalBonusEarnedDisplay = document.getElementById('total-bonus-earned');

    // Transaction History Modal Elements
    const transactionHistoryModal = document.getElementById('transaction-history-modal');
    const closeTransactionHistoryModalBtn = document.getElementById('close-transaction-history-modal');
    const transactionSearchInput = document.getElementById('transaction-search-input');
    const exportTransactionsBtn = document.getElementById('export-transactions-btn');
    const transactionsList = document.getElementById('transactions-list');
    const prevTransactionsBtn = document.getElementById('prev-transactions-btn');
    const nextTransactionsBtn = document.getElementById('next-transactions-btn');
    const transactionPageInfo = document.getElementById('transaction-page-info');

    // Transaction Details Modal Elements
    const transactionDetailsModal = document.getElementById('transaction-details-modal');
    const closeTransactionDetailsModalBtn = document.getElementById('close-transaction-details-modal');
    const transactionDetailsContent = document.getElementById('transaction-details-content');
    const printReceiptBtn = document.getElementById('print-receipt-btn');

    // Transaction Calculator Modal Elements
    const transactionCalculatorDashboardBtn = document.getElementById('transaction-calculator-dashboard-btn');
    const transactionCalculatorModal = document.getElementById('transaction-calculator-modal');
    const closeTransactionCalculatorModalBtn = document.getElementById('close-transaction-calculator-modal');
    const timeFilterBtns = document.querySelectorAll('.time-filter-btn');
    const customDateRangeSection = document.getElementById('custom-date-range');
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    const applyCustomFilterBtn = document.getElementById('apply-custom-filter-btn');
    const spendingSummaryList = document.getElementById('spending-summary-list');
    const calculatorTotalSpentDisplay = document.getElementById('calculator-total-spent');
    const calculatorTransactionsList = document.getElementById('calculator-transactions-list');
    const calculatorPrevTransactionsBtn = document.getElementById('calculator-prev-transactions-btn');
    const calculatorNextTransactionsBtn = document.getElementById('calculator-next-transactions-btn');
    const calculatorTransactionPageInfo = document.getElementById('calculator-transaction-page-info');

    // Notifications Modal Elements
    const notificationsModal = document.getElementById('notifications-modal');
    const closeNotificationsModalBtn = document.getElementById('close-notifications-modal');
    const notificationsList = document.getElementById('notifications-list');
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    const unreadNotificationsDot = document.getElementById('unread-notifications-dot');

    // Profile Page Elements (New)
    const backToDashboardFromProfileButton = document.getElementById('back-to-dashboard-from-profile');
    const profileNameElement = document.getElementById('profile-name');
    const profileEmailElement = document.getElementById('profile-email');
    const profilePhoneElement = document.getElementById('profile-phone');
    const profileTierElement = document.getElementById('profile-tier');
    const upgradeTierBtn = document.getElementById('upgrade-tier-btn');
    const resetPasswordBtn = document.getElementById('reset-password-btn');
    const resetPasscodeBtn = document.getElementById('reset-passcode-btn');
    const darkModeToggle = document.getElementById('dark-mode-toggle');

    // BVN Verification Modal Elements (New)
    const bvnVerificationModal = document.getElementById('bvn-verification-modal');
    const closeBvnModalBtn = document.getElementById('close-bvn-modal');
    const bvnInput = document.getElementById('bvn-input');
    const verifyBvnBtn = document.getElementById('verify-bvn-btn');

    // Password Reset Modal Elements (New)
    const passwordResetModal = document.getElementById('password-reset-modal');
    const closePasswordResetModalBtn = document.getElementById('close-password-reset-modal');
    const newPasswordInput = document.getElementById('new-password-input');
    const confirmPasswordInput = document.getElementById('confirm-password-input');
    const confirmPasswordResetBtn = document.getElementById('confirm-password-reset-btn');

    // Passcode Reset Modal Elements (New)
    const passcodeResetModal = document.getElementById('passcode-reset-modal');
    const closePasscodeResetModalBtn = document.getElementById('close-passcode-reset-modal');
    const newPasscodeInput = document.getElementById('new-passcode-input');
    const confirmPasscodeInput = document.getElementById('confirm-passcode-input');
    const confirmPasscodeResetBtn = document.getElementById('confirm-passcode-reset-btn');

    // Submit Payment Modal Elements
    const submitPaymentButton = document.getElementById('submit-payment-button');
    const submitPaymentModal = document.getElementById('submit-payment-modal');
    const closePaymentModalButton = document.getElementById('close-payment-modal');
    const bankDetailsSection = document.getElementById('bank-details-section');
    const previousOrdersSection = document.getElementById('previous-orders-section');
    const paymentNotificationForm = document.getElementById('payment-notification-form');
    const withdrawButton = document.getElementById('withdraw-button');
    const withdrawModal = document.getElementById('withdraw-modal');
    const closeWithdrawModalButton = document.getElementById('close-withdraw-modal');
    const shareFundButton = document.getElementById('share-fund-button');
    const shareFundModal = document.getElementById('share-fund-modal');
    const closeShareFundModalButton = document.getElementById('close-share-fund-modal');


    // --- Application State ---
    let currentActivePage = '';
    let balancesVisible = true;
    let currentGiftCardMode = 'buy';
    let userReferralLink = "";
    let isSmartCardVerified = false;
    let userProfile = {};
    let isDarkMode = false;
    let bankDetails = [];

    // Data that will be fetched from the backend
    let phoneBookGroups = [];
    let registeredSenderIds = [];

    let selectedPhonebookContacts = [];

    // Define all available services - now loaded dynamically
    let allServices = [
        { id: 'data', name: 'Data', icon: 'fas fa-wifi' },
        { id: 'airtime', name: 'Airtime', icon: 'fas fa-mobile-alt' },
        { id: 'electricity', name: 'Electricity', icon: 'fas fa-bolt' },
        { id: 'cabletv', name: 'Cable TV', icon: 'fas fa-tv' },
        { id: 'betting', name: 'Betting', icon: 'fas fa-dice' },
        { id: 'exam', name: 'Exam Pin', icon: 'fas fa-graduation-cap' },
        { id: 'bulksms', name: 'Bulk SMS', icon: 'fas fa-sms' },
        { id: 'giftcard', name: 'Gift Card', icon: 'fas fa-gift' },
        { id: 'recharge-card', name: 'Recharge Card', icon: 'fas fa-print' },
        { id: 'transaction-calculator', name: 'Transaction Calculator', icon: 'fas fa-calculator' }
    ];

    // Dynamic service data loaded from API
    let serviceData = {};
    let networkData = [];

    let currentTransactionPage = 1;
    const transactionsPerPage = 10;
    let filteredTransactions = [];

    let calculatorCurrentTransactionPage = 1;
    let calculatorFilteredTransactions = [];

    let notifications = [];

    // --- Dynamic Service Loading Functions ---
    async function loadServiceData() {
        try {
            const response = await fetch('api/services.php?action=get_services');
            const result = await response.json();
            
            if (result.success) {
                serviceData = result.data;
                
                // Update service buttons with dynamic data
                updateServiceAvailability();
                
                // Load network data as well
                await loadNetworkData();
            } else {
                console.error('Failed to load service data:', result.message);
            }
        } catch (error) {
            console.error('Error loading service data:', error);
        }
    }

    async function loadNetworkData() {
        try {
            const response = await fetch('api/services.php?action=get_networks');
            const result = await response.json();
            
            if (result.success) {
                networkData = result.data;
            } else {
                console.error('Failed to load network data:', result.message);
            }
        } catch (error) {
            console.error('Error loading network data:', error);
        }
    }

    async function loadServiceByType(serviceType) {
        try {
            const response = await fetch(`api/services.php?action=get_service_by_type&type=${serviceType}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                console.error('Failed to load service by type:', result.message);
                return {};
            }
        } catch (error) {
            console.error('Error loading service by type:', error);
            return {};
        }
    }

    async function detectNetwork(phoneNumber) {
        try {
            const response = await fetch(`api/services.php?action=detect_network&phone=${phoneNumber}`);
            const result = await response.json();
            
            if (result.success) {
                return result.network;
            } else {
                return null;
            }
        } catch (error) {
            console.error('Error detecting network:', error);
            return null;
        }
    }

    function updateServiceAvailability() {
        // Update service buttons to show availability
        dashboardServiceButtons.forEach(button => {
            const serviceType = button.dataset.service;
            if (serviceType && serviceType !== 'more') {
                const hasProducts = serviceData[serviceType] && Object.keys(serviceData[serviceType].networks || {}).length > 0;
                
                if (hasProducts) {
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                    button.disabled = false;
                } else {
                    button.classList.add('opacity-50', 'cursor-not-allowed');
                    button.disabled = true;
                }
            }
        });
    }


    // --- Page Rendering Function ---
    function renderPage(pageId, serviceType = null) {
        document.querySelectorAll('.page').forEach(page => {
            page.style.display = 'none';
        });
        serviceForms.forEach(form => form.classList.add('hidden'));

        switch (pageId) {
            case 'dashboard':
                dashboardPage.style.display = 'block';
                footerNav.style.display = 'block';
                appContainer.classList.remove('justify-center');
                break;
            case 'fund-wallet':
                fundWalletPage.style.display = 'block';
                footerNav.style.display = 'none';
                appContainer.classList.remove('justify-center');
                break;
            case 'service':
                servicePage.style.display = 'block';
                footerNav.style.display = 'none';
                appContainer.classList.remove('justify-center');
                displayServiceForm(serviceType);
                break;
            case 'profile': // New
                profilePage.style.display = 'block';
                footerNav.style.display = 'block'; // Profile page has footer nav
                appContainer.classList.remove('justify-center');
                updateProfilePage(); // New: Update profile details
                break;
            default:
                console.error('Unknown page ID:', pageId);
                dashboardPage.style.display = 'block';
                footerNav.style.display = 'block';
                appContainer.classList.remove('justify-center');
        }
        currentActivePage = pageId;
        updateUnreadNotificationsDot();
    }

    // Helper function to display the correct service form
    async function displayServiceForm(serviceType) {
        const serviceInfo = allServices.find(s => s.id === serviceType);
        if (serviceInfo) {
            servicePageTitle.textContent = `${serviceInfo.name} Services`;
            currentServiceName.textContent = serviceInfo.name;
        } else {
            servicePageTitle.textContent = "Service Details";
            currentServiceName.textContent = "Unknown Service";
        }

        switch (serviceType) {
            case 'data':
                dataFormSection.classList.remove('hidden');
                resetDataForm();
                break;
            case 'airtime':
                airtimeFormSection.classList.remove('hidden');
                resetAirtimeForm();
                break;
            case 'electricity':
                electricityFormSection.classList.remove('hidden');
                resetElectricityForm();
                populateElectricityProviders();
                break;
            case 'cabletv':
                cabletvFormSection.classList.remove('hidden');
                resetCableTVForm();
                break;
            case 'betting':
                bettingFormSection.classList.remove('hidden');
                resetBettingForm();
                populateBettingProviders();
                break;
            case 'exam':
                examFormSection.classList.remove('hidden');
                resetExamForm();
                populateExamBoards();
                break;
            case 'bulksms':
                bulksmsFormSection.classList.remove('hidden');
                resetBulkSMSForm();
                loadSenderIds();
                loadPhoneBookGroups();
                break;
            case 'giftcard':
                giftcardFormSection.classList.remove('hidden');
                resetGiftCardForm();
                break;
            case 'recharge-card':
                rechargeCardFormSection.classList.remove('hidden');
                resetRechargeCardForm();
                populateRechargeCardProviders();
                break;
            default:
                console.error('Unknown service type for form display:', serviceType);
                break;
        }
    }

    // --- Dynamic Service Population Functions ---

    // --- Network Detection Logic - Now using dynamic API ---
    async function detectNetworkFromPhone(phoneNumber) {
        try {
            const network = await detectNetwork(phoneNumber);
            return network ? network.display_name : 'Unknown';
        } catch (error) {
            // Fallback to local detection if API fails
            const networkPrefixes = {
                '0803': 'MTN', '0703': 'MTN', '0806': 'MTN', '0706': 'MTN', '0813': 'MTN', '0816': 'MTN', '0810': 'MTN', '0814': 'MTN', '0903': 'MTN', '0906': 'MTN', '0704': 'MTN',
                '0805': 'Glo', '0705': 'Glo', '0807': 'Glo', '0811': 'Glo', '0815': 'Glo', '0905': 'Glo',
                '0802': 'Airtel', '0701': 'Airtel', '0708': 'Airtel', '0812': 'Airtel', '0902': 'Airtel', '0907': 'Airtel', '0901': 'Airtel', '0904': 'Airtel',
                '0809': '9mobile', '0817': '9mobile', '0818': '9mobile', '0909': '9mobile', '0908': '9mobile'
            };
            
            if (phoneNumber.length < 4) return 'N/A';
            const prefix = phoneNumber.substring(0, 4);
            return networkPrefixes[prefix] || 'Unknown';
        }
    }

    async function getUniqueNetworks(phoneNumbers) {
        const detectedNetworks = new Set();
        for (const num of phoneNumbers) {
            const network = await detectNetworkFromPhone(num);
            if (network !== 'N/A' && network !== 'Unknown') {
                detectedNetworks.add(network);
            }
        }
        return Array.from(detectedNetworks);
    }

    // --- Data Vending Form Logic ---
    function loadDataPlans(network) {
        dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
        if (serviceData.data && serviceData.data.networks && serviceData.data.networks[network]) {
            serviceData.data.networks[network].forEach(plan => {
                const option = document.createElement('option');
                option.value = plan.plan_code;
                option.textContent = `${plan.name} - ${plan.data_size} (${plan.validity}) - ₦${plan.price}`;
                option.dataset.price = plan.price;
                dataPlanSelect.appendChild(option);
            });
        }
    }

    function getDataRecipients() {
        if (dataBulkPurchaseToggle.checked) {
            const rawInput = dataPhoneNumbersBulkInput.value.trim();
            return rawInput.split('\n').map(num => num.trim()).filter(num => num.length === 11); // Filter for valid 11-digit numbers
        } else {
            const singleNumber = dataPhoneNumberInput.value.trim();
            return singleNumber.length === 11 ? [singleNumber] : [];
        }
    }

    async function updateDataRecipientCountAndCost() {
        const recipients = getDataRecipients();
        dataRecipientCountDisplay.textContent = recipients.length;

        let currentDetectedNetwork = dataDetectedNetworkDisplay.textContent;
        let selectedNetworkForPlans = dataManualNetworkSelect.value;
        let networkChanged = false;

        if (dataBulkPurchaseToggle.checked) {
            if (!dataNetworkOverrideToggle.checked) {
                const uniqueNetworks = await getUniqueNetworks(recipients);
                if (uniqueNetworks.length === 1) {
                    if(currentDetectedNetwork !== uniqueNetworks[0]) {
                        networkChanged = true;
                        currentDetectedNetwork = uniqueNetworks[0];
                        dataDetectedNetworkDisplay.textContent = currentDetectedNetwork;
                    }
                    selectedNetworkForPlans = currentDetectedNetwork;
                } else if (uniqueNetworks.length > 1) {
                    dataDetectedNetworkDisplay.textContent = 'Mixed/Manual Required';
                    dataNetworkOverrideToggle.checked = true;
                    dataManualNetworkSelection.style.display = 'block';
                    dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
                    selectedNetworkForPlans = ''; // Force manual selection
                } else {
                    dataDetectedNetworkDisplay.textContent = 'N/A';
                    dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
                    selectedNetworkForPlans = '';
                }
            } else {
                // Manual override is active, use selected manual network
                selectedNetworkForPlans = dataManualNetworkSelect.value;
            }
        } else { // Single purchase mode
            if (!dataNetworkOverrideToggle.checked) {
                const singleNumber = dataPhoneNumberInput.value.trim();
                const detected = await detectNetworkFromPhone(singleNumber);
                if(currentDetectedNetwork !== detected) {
                    networkChanged = true;
                    dataDetectedNetworkDisplay.textContent = detected;
                }
                if (detected !== 'N/A' && detected !== 'Unknown') {
                    selectedNetworkForPlans = detected;
                } else {
                    dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
                    selectedNetworkForPlans = '';
                }
            } else {
                // Manual override is active, use selected manual network
                selectedNetworkForPlans = dataManualNetworkSelect.value;
            }
        }

        if(networkChanged) {
            loadDataTypes(selectedNetworkForPlans);
            loadDataPlans(selectedNetworkForPlans, dataTypeSelect.value);
        }

        const productId = dataPlanSelect.value;
        let pricePerPlan = 0;
        if(productId && serviceData.data && serviceData.data.networks && serviceData.data.networks[selectedNetworkForPlans]) {
            const selectedPlan = serviceData.data.networks[selectedNetworkForPlans].find(p => p.plan_code === productId);
            if(selectedPlan) {
                pricePerPlan = selectedPlan.price;
            }
        }

        const totalCost = pricePerPlan * recipients.length;
        dataBulkTotalCostDisplay.textContent = `₦${totalCost.toFixed(2)}`;
    }

    function resetDataForm() {
        dataBulkPurchaseToggle.checked = false;
        dataPurchaseTypeText.textContent = 'Single Purchase';
        dataSinglePhoneInput.classList.remove('hidden');
        dataBulkPhoneInput.classList.add('hidden');
        dataBulkTotalCostSection.classList.add('hidden');

        dataPhoneNumberInput.value = '';
        dataPhoneNumbersBulkInput.value = '';
        dataRecipientCountDisplay.textContent = '0';

        dataDetectedNetworkDisplay.textContent = 'N/A';
        dataNetworkOverrideToggle.checked = false;
        dataManualNetworkSelection.style.display = 'none';
        dataManualNetworkSelect.value = '';
        dataTypeSelect.innerHTML = '<option value="">Select a data type</option>';
        dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
        dataBulkTotalCostDisplay.textContent = '₦0.00';

        dataScheduleToggle.checked = false;
        dataScheduleFields.classList.add('hidden');
        dataScheduleDateInput.value = '';
        dataScheduleTimeInput.value = '';
    }

    // --- Airtime Vending Form Logic ---
    function getAirtimeRecipients() {
        if (airtimeBulkPurchaseToggle.checked) {
            const rawInput = airtimePhoneNumbersBulkInput.value.trim();
            return rawInput.split('\n').map(num => num.trim()).filter(num => num.length === 11); // Filter for valid 11-digit numbers
        } else {
            const singleNumber = airtimePhoneNumberInput.value.trim();
            return singleNumber.length === 11 ? [singleNumber] : [];
        }
    }

    async function updateAirtimeRecipientCountAndCost() {
        const recipients = getAirtimeRecipients();
        airtimeRecipientCountDisplay.textContent = recipients.length;

        let currentDetectedNetwork = airtimeDetectedNetworkDisplay.textContent;
        let selectedNetworkForCost = airtimeManualNetworkSelect.value;

        if (airtimeBulkPurchaseToggle.checked) {
            if (!airtimeNetworkOverrideToggle.checked) {
                const uniqueNetworks = await getUniqueNetworks(recipients);
                if (uniqueNetworks.length === 1) {
                    currentDetectedNetwork = uniqueNetworks[0];
                    airtimeDetectedNetworkDisplay.textContent = currentDetectedNetwork;
                    selectedNetworkForCost = currentDetectedNetwork;
                } else if (uniqueNetworks.length > 1) {
                    airtimeDetectedNetworkDisplay.textContent = 'Mixed/Manual Required';
                    airtimeNetworkOverrideToggle.checked = true;
                    airtimeManualNetworkSelection.style.display = 'block';
                    selectedNetworkForCost = ''; // Force manual selection
                } else {
                    airtimeDetectedNetworkDisplay.textContent = 'N/A';
                    selectedNetworkForCost = '';
                }
            } else {
                // Manual override is active, use selected manual network
                selectedNetworkForCost = airtimeManualNetworkSelect.value;
            }
        } else { // Single purchase mode
            if (!airtimeNetworkOverrideToggle.checked) {
                const singleNumber = airtimePhoneNumberInput.value.trim();
                const detected = await detectNetworkFromPhone(singleNumber);
                airtimeDetectedNetworkDisplay.textContent = detected;
                selectedNetworkForCost = detected;
            } else {
                // Manual override is active, use selected manual network
                selectedNetworkForCost = airtimeManualNetworkSelect.value;
            }
        }

        const amountPerRecipient = parseFloat(airtimeAmountInput.value) || 0;
        const totalCost = amountPerRecipient * recipients.length;
        airtimeBulkTotalCostDisplay.textContent = `₦${totalCost.toFixed(2)}`;
    }

    function resetAirtimeForm() {
        airtimeBulkPurchaseToggle.checked = false;
        airtimePurchaseTypeText.textContent = 'Single Purchase';
        airtimeSinglePhoneInput.classList.remove('hidden');
        airtimeBulkPhoneInput.classList.add('hidden');
        airtimeBulkTotalCostSection.classList.add('hidden');

        airtimePhoneNumberInput.value = '';
        airtimePhoneNumbersBulkInput.value = '';
        airtimeRecipientCountDisplay.textContent = '0';

        airtimeDetectedNetworkDisplay.textContent = 'N/A';
        airtimeNetworkOverrideToggle.checked = false;
        airtimeManualNetworkSelection.style.display = 'none';
        airtimeManualNetworkSelect.value = '';
        airtimeAmountInput.value = '';
        airtimeBulkTotalCostDisplay.textContent = '₦0.00';

        airtimeScheduleToggle.checked = false;
        airtimeScheduleFields.classList.add('hidden');
        airtimeScheduleDateInput.value = '';
        airtimeScheduleTimeInput.value = '';
    }

    // --- Electricity Vending Form Logic ---
    function generateRandomToken() {
        return Array(5).fill(0).map(() => Math.floor(1000 + Math.random() * 9000)).join('-');
    }

    function resetElectricityForm() {
        electricityPrepaidRadio.checked = true;
        electricityTokenSection.classList.remove('hidden');
        meterNumberInput.value = '';
        discoProviderSelect.value = '';
        electricityAmountInput.value = '';
        electricityTokenInput.value = '';
    }

    function populateElectricityProviders() {
        discoProviderSelect.innerHTML = '<option value="">Select Disco</option>';
        if (serviceData.electricity && serviceData.electricity.products) {
            serviceData.electricity.products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.plan_code;
                option.textContent = product.name;
                discoProviderSelect.appendChild(option);
            });
        }
    }

    // --- Cable TV Vending Form Logic ---
    function loadCableTvPlans(provider) {
        cabletvPlanSelect.innerHTML = '<option value="">Select a plan</option>';
        if (serviceData.cabletv && serviceData.cabletv.providers && serviceData.cabletv.providers[provider]) {
            serviceData.cabletv.providers[provider].forEach(plan => {
                const option = document.createElement('option');
                option.value = plan.plan_code;
                option.textContent = `${plan.name} - ₦${plan.price}`;
                option.dataset.price = plan.price;
                cabletvPlanSelect.appendChild(option);
            });
        }
    }

    function resetCableTVForm() {
        smartCardNumberInput.value = '';
        cabletvPlanSelect.innerHTML = '<option value="">Select a plan</option>';
        cabletvVerificationResult.classList.add('hidden');
        verifiedCustomerName.textContent = '';
        verifiedSubscriptionStatus.textContent = '';
        cabletvBuyBtn.disabled = true;
        isSmartCardVerified = false;
        verifySmartCardBtn.disabled = smartCardNumberInput.value.trim().length === 0 || !cabletvProviderSelect.value;
    }

    // --- Betting Wallet Funding Form Logic ---
    function resetBettingForm() {
        bettingPlatformSelect.value = '';
        bettingUserIdInput.value = '';
        bettingAmountInput.value = '';
    }

    function populateBettingProviders() {
        bettingPlatformSelect.innerHTML = '<option value="">Select Platform</option>';
        if (serviceData.betting && serviceData.betting.products) {
            serviceData.betting.products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.plan_code;
                option.textContent = product.name;
                bettingPlatformSelect.appendChild(option);
            });
        }
    }

    // --- Exam Vending Form Logic ---
    function updateExamTotalCost() {
        const examType = examTypeSelect.value;
        const quantity = parseInt(examQuantityInput.value) || 0;
        let pricePerPin = 0;
        if (serviceData.exam && serviceData.exam.types && serviceData.exam.types[examType]) {
            pricePerPin = serviceData.exam.types[examType].price;
        }
        const total = pricePerPin * quantity;
        examTotalAmountDisplay.textContent = `₦${total.toFixed(2)}`;
    }

    function resetExamForm() {
        examTypeSelect.value = '';
        examQuantityInput.value = '1';
        updateExamTotalCost();
    }

    function populateExamBoards() {
        examTypeSelect.innerHTML = '<option value="">Select Exam</option>';
        if (serviceData.exam && serviceData.exam.networks && serviceData.exam.networks['All Networks']) {
            serviceData.exam.networks['All Networks'].forEach(product => {
                const option = document.createElement('option');
                option.value = product.plan_code;
                option.textContent = product.name;
                examTypeSelect.appendChild(option);
            });
        }
    }

    // --- Bulk SMS Sending Form Logic ---
    const smsCostPerUnit = 5;

    function calculateSmsUnitsAndCost() {
        const message = smsMessageTextarea.value;
        const charCount = message.length;
        const recipients = getRecipientNumbers().length;

        let unitsPerSms = 0;
        if (charCount > 0) {
            unitsPerSms = Math.ceil(charCount / 153);
            if (charCount <= 160) {
                unitsPerSms = 1;
            }
        }

        const totalSmsUnits = unitsPerSms * recipients;
        const totalCost = totalSmsUnits * smsCostPerUnit;

        charCountDisplay.textContent = charCount;
        smsUnitsDisplay.textContent = totalSmsUnits;
        bulksmsTotalCostDisplay.textContent = `₦${totalCost.toFixed(2)}`;
    }

    function getRecipientNumbers() {
        let numbers = [];
        if (usePhonebookToggle.checked) {
            numbers = selectedPhonebookContacts;
        } else {
            const rawInput = recipientNumbersTextarea.value.trim();
            if (rawInput) {
                numbers = rawInput.split(/[\n,]+/).map(num => num.trim()).filter(num => num.length > 0);
            }
        }
        return numbers;
    }

    function updateRecipientCount() {
        recipientCountDisplay.textContent = getRecipientNumbers().length;
        calculateSmsUnitsAndCost();
    }

    function loadSenderIds() {
        smsSenderIdSelect.innerHTML = '<option value="">Select Sender ID</option>';
        registeredSenderIds.forEach(id => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = id;
            smsSenderIdSelect.appendChild(option);
        });
    }

    function loadPhoneBookGroups() {
        addContactGroupSelect.innerHTML = '<option value="">Select existing group</option><option value="new-group">Create New Group...</option>';
        uploadFileGroupSelect.innerHTML = '<option value="">Select existing group or create new</option><option value="new-group">Create New Group...</option>';
        phonebookGroupSelect.innerHTML = '<option value="">Select a group</option>';

        phoneBookGroups.forEach(group => {
            const option1 = document.createElement('option');
            option1.value = group.id;
            option1.textContent = group.name;
            addContactGroupSelect.appendChild(option1);

            const option2 = document.createElement('option');
            option2.value = group.id;
            option2.textContent = group.name;
            uploadFileGroupSelect.appendChild(option2);

            const option3 = document.createElement('option');
            option3.value = group.id;
            option3.textContent = group.name;
            phonebookGroupSelect.appendChild(option3);
        });

        renderExistingGroupsList();
    }

    function resetBulkSMSForm() {
        smsSenderIdSelect.value = '';
        usePhonebookToggle.checked = false;
        manualRecipientInput.classList.remove('hidden');
        phonebookSelectionSection.classList.add('hidden');
        recipientNumbersTextarea.value = '';
        smsMessageTextarea.value = '';
        saveContactsToggle.checked = false;
        selectedPhonebookContacts = [];
        updateRecipientCount();
        calculateSmsUnitsAndCost();

        bulksmsScheduleToggle.checked = false;
        bulksmsScheduleFields.classList.add('hidden');
        bulksmsScheduleDateInput.value = '';
        bulksmsScheduleTimeInput.value = '';
    }

    // --- Gift Card Buy/Sell Form Logic ---
    const giftCardRates = {
        'amazon': { buy: 400, sell: 380 },
        'apple': { buy: 350, sell: 330 },
        'google_play': { buy: 300, sell: 280 },
        'steam': { buy: 250, sell: 230 }
    };

    function updateGiftCardEstimatedValue() {
        const cardType = giftcardTypeSelect.value;
        const denomination = parseFloat(giftcardDenominationInput.value) || 0;
        let estimatedValue = 0;

        if (cardType && denomination > 0) {
            const rates = giftCardRates[cardType];
            if (rates) {
                if (currentGiftCardMode === 'buy') {
                    estimatedValue = denomination * rates.buy;
                } else {
                    estimatedValue = denomination * rates.sell;
                }
            }
        }
        giftcardEstimatedValueDisplay.textContent = `₦${estimatedValue.toFixed(2)}`;
    }

    function resetGiftCardForm() {
        giftcardTypeSelect.value = '';
        giftcardDenominationInput.value = '';
        giftcardCodeInput.value = '';
        giftcardImageInput.value = '';
        giftcardSellDetails.classList.add('hidden');
        giftcardBuyBtn.classList.add('bg-blue-600', 'text-white');
        giftcardBuyBtn.classList.remove('bg-gray-200', 'text-gray-700');
        giftcardSellBtn.classList.add('bg-gray-200', 'text-gray-700');
        giftcardSellBtn.classList.remove('bg-blue-600', 'text-white');
        currentGiftCardMode = 'buy';
        updateGiftCardEstimatedValue();
    }

    // --- Print Recharge Card Form Logic ---
    function updateRechargeCardTotalCost() {
        const network = rechargeCardNetworkSelect.value;
        const amount = rechargeCardAmountSelect.value;
        const quantity = parseInt(rechargeCardQuantityInput.value) || 0;
        let costPerCard = 0;

        if (network && amount && serviceData.recharge_card && serviceData.recharge_card.networks && serviceData.recharge_card.networks[network] && serviceData.recharge_card.networks[network][amount]) {
            costPerCard = serviceData.recharge_card.networks[network][amount].price;
        }
        const total = costPerCard * quantity;
        rechargeCardTotalCostDisplay.textContent = `₦${total.toFixed(2)}`;
    }

    function resetRechargeCardForm() {
        rechargeCardNetworkSelect.value = '';
        rechargeCardAmountSelect.value = '';
        rechargeCardQuantityInput.value = '1';
        updateRechargeCardTotalCost();
    }

    function populateRechargeCardProviders() {
        rechargeCardNetworkSelect.innerHTML = '<option value="">Select Network</option>';
        if (serviceData.recharge_card && serviceData.recharge_card.networks) {
            for (const network in serviceData.recharge_card.networks) {
                const option = document.createElement('option');
                option.value = network;
                option.textContent = network;
                rechargeCardNetworkSelect.appendChild(option);
            }
        }
    }

    rechargeCardNetworkSelect.addEventListener('change', () => {
        const selectedNetwork = rechargeCardNetworkSelect.value;
        rechargeCardAmountSelect.innerHTML = '<option value="">Select Amount</option>';
        if (selectedNetwork && serviceData.recharge_card && serviceData.recharge_card.networks && serviceData.recharge_card.networks[selectedNetwork]) {
            for (const amount in serviceData.recharge_card.networks[selectedNetwork]) {
                const option = document.createElement('option');
                option.value = amount;
                option.textContent = `₦${amount}`;
                rechargeCardAmountSelect.appendChild(option);
            }
        }
    });

    // --- More Services Modal Logic ---
    function populateAllServicesModal() {
        allServicesList.innerHTML = '';
        allServices.forEach(service => {
            const serviceDiv = document.createElement('button');
            serviceDiv.classList.add('service-btn', 'bg-white', 'p-4', 'rounded-xl', 'shadow-md', 'flex', 'flex-col', 'items-center', 'justify-center', 'text-gray-700', 'hover:bg-blue-50', 'transition-colors');
            serviceDiv.dataset.service = service.id;
            serviceDiv.innerHTML = `
                <i class="${service.icon} text-2xl text-blue-600 mb-2"></i>
                <span class="text-sm font-medium">${service.name}</span>
            `;
            serviceDiv.addEventListener('click', () => {
                moreServicesModal.classList.add('hidden');
                if (service.id === 'transaction-calculator') {
                    showTransactionCalculatorModal();
                } else {
                    renderPage('service', service.id);
                }
            });
            allServicesList.appendChild(serviceDiv);
        });
    }

    function showMoreServicesModal() {
        populateAllServicesModal();
        moreServicesModal.classList.remove('hidden');
    }

    // --- Phonebook Manager Modal Logic ---
    function showPhonebookManagerModal() {
        phonebookManagerModal.classList.remove('hidden');
        switchTab('add-contact');
        loadPhoneBookGroups();
        resetAddContactForm();
        resetManageGroupsForm();
        resetUploadContactsForm();
    }

    function switchTab(tabId) {
        tabContents.forEach(content => content.classList.add('hidden'));
        tabButtons.forEach(btn => {
            btn.classList.remove('border-blue-600', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-600');
        });

        document.getElementById(`${tabId}-tab`).classList.remove('hidden');
        document.querySelector(`[data-tab="${tabId}"]`).classList.add('border-blue-600', 'text-blue-600');
        document.querySelector(`[data-tab="${tabId}"]`).classList.remove('border-transparent', 'text-gray-600');

        if (tabId === 'manage-groups') {
            renderExistingGroupsList();
        } else if (tabId === 'add-contact' || tabId === 'upload-contacts') {
            loadPhoneBookGroups();
        }
    }

    function resetAddContactForm() {
        newContactNameInput.value = '';
        newContactNumberInput.value = '';
        addContactGroupSelect.value = '';
    }

    function resetManageGroupsForm() {
        newGroupNameInput.value = '';
    }

    function resetUploadContactsForm() {
        uploadFileGroupSelect.value = '';
        contactUploadFile.value = '';
    }

    function renderExistingGroupsList() {
        existingGroupsList.innerHTML = '';
        if (phoneBookGroups.length === 0) {
            existingGroupsList.innerHTML = '<li class="text-gray-500">No groups created yet.</li>';
            return;
        }
        phoneBookGroups.forEach(group => {
            const li = document.createElement('li');
            li.classList.add('flex', 'justify-between', 'items-center', 'py-2', 'border-b', 'border-gray-100', 'last:border-b-0');
            li.innerHTML = `
                <span>${group.name} (${group.contacts.length} contacts)</span>
                <div>
                    <button class="text-blue-600 hover:text-blue-800 mr-2 edit-group-btn" data-group-id="${group.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-800 delete-group-btn" data-group-id="${group.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            existingGroupsList.appendChild(li);
        });

        document.querySelectorAll('.edit-group-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const groupId = e.currentTarget.dataset.groupId;
                const group = phoneBookGroups.find(g => g.id === groupId);
                if (group) {
                    const newName = prompt(`Edit name for group "${group.name}":`, group.name);
                    if (newName && newName.trim() !== group.name) {
                        group.name = newName.trim();
                        alert(`Group name updated to "${newName}".`);
                        loadPhoneBookGroups();
                    }
                }
            });
        });

        document.querySelectorAll('.delete-group-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const groupId = e.currentTarget.dataset.groupId;
                if (confirm('Are you sure you want to delete this group and all its contacts?')) {
                    phoneBookGroups = phoneBookGroups.filter(g => g.id !== groupId);
                    alert('Group deleted.');
                    loadPhoneBookGroups();
                }
            });
        });
    }

    // --- Referral Details Modal Logic ---
    function showReferralDetailsModal() {
        referralDetailsModal.classList.remove('hidden');
        displayReferralLinkInput.value = userReferralLink;
        
        // Show loading indicator
        referredUsersList.innerHTML = '<li class="text-gray-500 text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading referral data...</li>';
        totalBonusEarnedDisplay.textContent = '₦0.00';
        
        // Fetch referral data from API
        fetch('api/referrals.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                renderReferredUsers(data.referrals);
                totalBonusEarnedDisplay.textContent = `₦${parseFloat(data.total_bonus || 0).toFixed(2)}`;
            })
            .catch(error => {
                console.error('Error fetching referral data:', error);
                referredUsersList.innerHTML = `<li class="text-red-500 text-center py-4"><i class="fas fa-exclamation-triangle mr-2"></i>Error loading referral data: ${error.message}</li>`;
            });
    }

    function renderReferredUsers(referrals) {
        referredUsersList.innerHTML = '';
        if (!referrals || referrals.length === 0) {
            referredUsersList.innerHTML = '<li class="text-gray-500 text-center py-4">You have not referred any users yet.</li>';
            return;
        }
        
        referrals.forEach(referral => {
            const li = document.createElement('li');
            li.classList.add('flex', 'justify-between', 'items-center', 'py-3', 'px-2', 'border-b', 'border-gray-100', 'last:border-b-0', 'hover:bg-gray-50');
            
            // Format join date
            const joinDate = new Date(referral.join_date);
            const formattedDate = joinDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            
            li.innerHTML = `
                <div class="flex-grow">
                    <p class="font-medium text-gray-800">${referral.referred_user || 'Unknown User'}</p>
                    <p class="text-xs text-gray-500">Joined: ${formattedDate}</p>
                </div>
                <p class="font-semibold text-green-600">+₦${parseFloat(referral.bonus_earned || 0).toFixed(2)}</p>
            `;
            referredUsersList.appendChild(li);
        });
    }

    // --- Transaction History Modal Logic ---
    function filterTransactionsAndRender() {
        const searchTerm = transactionSearchInput.value.toLowerCase();
        filteredTransactions = transactions.filter(txn =>
            txn.description.toLowerCase().includes(searchTerm) ||
            txn.type.toLowerCase().includes(searchTerm) ||
            txn.id.toLowerCase().includes(searchTerm)
        );
        renderTransactions();
        renderRecentTransactions();
    }

    function renderRecentTransactions() {
        recentTransactionsContainer.innerHTML = '';
        const recent = transactions.slice(0, 5);

        if (recent.length === 0) {
            recentTransactionsContainer.innerHTML = '<p class="text-gray-500 text-center">No recent transactions.</p>';
            return;
        }

        const ul = document.createElement('ul');
        ul.className = 'divide-y divide-gray-100';

        recent.forEach(txn => {
            const li = document.createElement('li');
            li.className = 'flex justify-between items-center py-3 px-4 hover:bg-gray-50 cursor-pointer';
            li.dataset.transactionId = txn.id;
            const statusHtml = `<span class="text-xs font-semibold ${txn.status === 'Completed' ? 'text-green-600' : (txn.status === 'Failed' ? 'text-red-600' : 'text-yellow-600')}">${txn.status}</span>`;
            li.innerHTML = `
                <div>
                    <p class="font-medium text-gray-800">${txn.description}</p>
                    <p class="text-xs text-gray-500">${new Date(txn.created_at).toLocaleString()} - ${statusHtml}</p>
                </div>
                <p class="font-semibold ${txn.amount < 0 ? 'text-red-600' : 'text-green-600'}">
                    ${txn.amount < 0 ? '-' : '+'}₦${Math.abs(txn.amount).toFixed(2)}
                </p>
            `;
            ul.appendChild(li);
        });
        recentTransactionsContainer.appendChild(ul);

        document.querySelectorAll('#recent-transactions li').forEach(item => {
            item.addEventListener('click', (e) => {
                const txnId = e.currentTarget.dataset.transactionId;
                showTransactionDetailsModal(txnId);
            });
        });
    }

    function renderTransactions() {
        transactionsList.innerHTML = '';
        const startIndex = (currentTransactionPage - 1) * transactionsPerPage;
        const endIndex = startIndex + transactionsPerPage;
        const transactionsToDisplay = filteredTransactions.slice(startIndex, endIndex);

        if (transactionsToDisplay.length === 0) {
            transactionsList.innerHTML = '<li class="p-4 text-gray-500 text-center">No transactions found.</li>';
            transactionPageInfo.textContent = 'Page 0 of 0';
            prevTransactionsBtn.disabled = true;
            nextTransactionsBtn.disabled = true;
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
                    <p class="text-xs text-gray-500">${new Date(txn.date).toLocaleString()}</p>
                    <p class="text-sm">${statusHtml}</p>
                </div>
                <p class="font-semibold ${txn.amount < 0 ? 'text-red-600' : 'text-green-600'}">
                    ${txn.amount < 0 ? '-' : '+'}₦${Math.abs(txn.amount).toFixed(2)}
                </p>
            `;
            transactionsList.appendChild(li);
        });

        const totalPages = Math.ceil(filteredTransactions.length / transactionsPerPage);
        transactionPageInfo.textContent = `Page ${currentTransactionPage} of ${totalPages}`;
        prevTransactionsBtn.disabled = currentTransactionPage === 1;
        nextTransactionsBtn.disabled = currentTransactionPage === totalPages;

        document.querySelectorAll('#transactions-list li').forEach(item => {
            item.addEventListener('click', (e) => {
                // Do not open details modal if requery button was clicked
                if (e.target.classList.contains('requery-btn')) {
                    return;
                }
                const txnId = e.currentTarget.dataset.transactionId;
                showTransactionDetailsModal(txnId);
            });
        });

        document.querySelectorAll('.requery-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const txnId = e.currentTarget.dataset.id;
                e.currentTarget.textContent = 'Requerying...';
                e.currentTarget.disabled = true;

                fetch('api/requery.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `transaction_id=${txnId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Refresh transaction list to show new status
                        fetchTransactions();
                    } else {
                        alert('Error: ' + data.message);
                        e.currentTarget.textContent = 'Requery';
                        e.currentTarget.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Requery error:', error);
                    alert('An error occurred while requerying.');
                    e.currentTarget.textContent = 'Requery';
                    e.currentTarget.disabled = false;
                });
            });
        });
    }

    function exportTransactionsToPDF(data) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(18);
        doc.text("Transaction History", 14, 22);

        doc.setFontSize(11);
        doc.setTextColor(100);

        const headers = [['ID', 'Type', 'Description', 'Amount (₦)', 'Date', 'Status']];
        const rows = data.map(txn => [
            txn.id,
            txn.type,
            txn.description,
            `${txn.amount < 0 ? '-' : ''}₦${Math.abs(txn.amount).toFixed(2)}`,
            new Date(txn.date).toLocaleString(),
            txn.status
        ]);

        doc.autoTable({
            startY: 30,
            head: headers,
            body: rows,
            theme: 'striped',
            headStyles: { fillColor: [41, 128, 185] },
            styles: {
                font: 'Inter',
                fontSize: 8,
                cellPadding: 2,
                valign: 'middle',
                halign: 'left'
            },
            columnStyles: {
                0: { cellWidth: 20 },
                1: { cellWidth: 20 },
                2: { cellWidth: 60 },
                3: { cellWidth: 25, halign: 'right' },
                4: { cellWidth: 35 },
                5: { cellWidth: 20 }
            }
        });

        doc.save('transaction_history.pdf');
        alert('Transaction history exported as PDF!');
    }


    function printTransactionReceipt(transaction) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(22);
        doc.text("Vending Platform Receipt", 105, 20, null, null, "center");

        doc.setFontSize(12);
        doc.text(`Date: ${new Date().toLocaleString()}`, 105, 30, null, null, "center");
        doc.line(20, 35, 190, 35);

        let y = 45;
        doc.setFontSize(14);
        doc.text("Transaction Details:", 20, y);
        y += 10;
        doc.setFontSize(12);
        doc.text(`Transaction ID: ${transaction.id}`, 20, y); y += 7;
        doc.text(`Type: ${transaction.type}`, 20, y); y += 7;
        doc.text(`Description: ${transaction.description}`, 20, y); y += 7;
        doc.text(`Amount: ${transaction.amount < 0 ? '-' : '+'}₦${Math.abs(transaction.amount).toFixed(2)}`, 20, y); y += 7;
        doc.text(`Status: ${transaction.status}`, 20, y); y += 7;
        doc.text(`Transaction Date: ${new Date(transaction.date).toLocaleString()}`, 20, y); y += 10;

        if (transaction.serviceDetails) {
            doc.setFontSize(14);
            doc.text("Service Specifics:", 20, y);
            y += 10;
            doc.setFontSize(12);
            for (const key in transaction.serviceDetails) {
                doc.text(`${key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())}: ${transaction.serviceDetails[key]}`, 20, y);
                y += 7;
            }
        }

        doc.line(20, y + 5, 190, y + 5);
        doc.setFontSize(10);
        doc.text("Thank you for your business!", 105, y + 15, null, null, "center");

        doc.save(`receipt_${transaction.id}.pdf`);
        alert('Receipt printed successfully!');
    }


    // --- Transaction Calculator Modal Logic ---
    function showTransactionCalculatorModal() {
        transactionCalculatorModal.classList.remove('hidden');
        resetCalculatorFilters();
    }

    function resetCalculatorFilters() {
        timeFilterBtns.forEach(btn => {
            if (btn.dataset.period === 'today') {
                btn.classList.remove('bg-blue-100', 'text-blue-700');
                btn.classList.add('bg-blue-700', 'text-white');
            } else {
                btn.classList.remove('bg-blue-700', 'text-white');
                btn.classList.add('bg-blue-100', 'text-blue-700');
            }
        });
        customDateRangeSection.classList.add('hidden');
        startDateInput.value = '';
        endDateInput.value = '';

        const today = new Date();
        const startOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        filterCalculatorTransactionsByDate(startOfToday, today);
    }

    function filterCalculatorTransactionsAndRender() {
        renderCalculatorTransactions();
        updateSpendingSummary();
    }

    function filterCalculatorTransactionsByDate(startDate, endDate) {
        calculatorFilteredTransactions = transactions.filter(txn => {
            const txnDate = new Date(txn.date);
            const isWithinRange = (!startDate || txnDate >= startDate) && (!endDate || txnDate <= endDate);
            return isWithinRange && txn.amount < 0;
        });
        calculatorCurrentTransactionPage = 1;
        renderCalculatorTransactions();
        updateSpendingSummary();
    }

    function renderCalculatorTransactions() {
        calculatorTransactionsList.innerHTML = '';
        const startIndex = (calculatorCurrentTransactionPage - 1) * transactionsPerPage;
        const endIndex = startIndex + transactionsPerPage;
        const transactionsToDisplay = calculatorFilteredTransactions.slice(startIndex, endIndex);

        if (transactionsToDisplay.length === 0) {
            calculatorTransactionsList.innerHTML = '<li class="p-4 text-gray-500 text-center">No spending transactions found for this period.</li>';
            calculatorTransactionPageInfo.textContent = 'Page 0 of 0';
            calculatorPrevTransactionsBtn.disabled = true;
            calculatorNextTransactionsBtn.disabled = true;
            return;
        }

        transactionsToDisplay.forEach(txn => {
            const li = document.createElement('li');
            li.classList.add('flex', 'justify-between', 'items-center', 'py-3', 'px-4', 'hover:bg-gray-50', 'cursor-pointer');
            li.dataset.transactionId = txn.id;
            li.innerHTML = `
                <div>
                    <p class="font-medium text-gray-800">${txn.description}</p>
                    <p class="text-xs text-gray-500">${new Date(txn.date).toLocaleString()}</p>
                </div>
                <p class="font-semibold text-red-600">
                    -₦${Math.abs(txn.amount).toFixed(2)}
                </p>
            `;
            calculatorTransactionsList.appendChild(li);
        });

        const totalPages = Math.ceil(calculatorFilteredTransactions.length / transactionsPerPage);
        calculatorTransactionPageInfo.textContent = `Page ${calculatorCurrentTransactionPage} of ${totalPages}`;
        calculatorPrevTransactionsBtn.disabled = calculatorCurrentTransactionPage === 1;
        calculatorNextTransactionsBtn.disabled = calculatorCurrentTransactionPage === totalPages;

        document.querySelectorAll('#calculator-transactions-list li').forEach(item => {
            item.addEventListener('click', (e) => {
                const txnId = e.currentTarget.dataset.transactionId;
                showTransactionDetailsModal(txnId);
            });
        });
    }

    function updateSpendingSummary() {
        const spendingByCategory = {};
        let totalSpent = 0;

        calculatorFilteredTransactions.forEach(txn => {
            const category = txn.type;
            const amount = Math.abs(txn.amount);

            if (spendingByCategory[category]) {
                spendingByCategory[category] += amount;
            } else {
                spendingByCategory[category] = amount;
            }
            totalSpent += amount;
        });

        spendingSummaryList.innerHTML = '';
        for (const category in spendingByCategory) {
            const p = document.createElement('p');
            p.classList.add('text-gray-700', 'mb-1');
            p.innerHTML = `<span class="font-medium">${category}:</span> ₦${spendingByCategory[category].toFixed(2)}`;
            spendingSummaryList.appendChild(p);
        }

        if (Object.keys(spendingByCategory).length === 0) {
            spendingSummaryList.innerHTML = '<p class="text-gray-500">No spending data for this period.</p>';
        }

        calculatorTotalSpentDisplay.textContent = `₦${totalSpent.toFixed(2)}`;
    }

    // --- Notifications Modal Logic ---
    function showNotificationsModal() {
        notificationsModal.classList.remove('hidden');
        renderNotifications();
    }

    function renderNotifications() {
        notificationsList.innerHTML = '';
        if (notifications.length === 0) {
            notificationsList.innerHTML = '<li class="p-4 text-gray-500 text-center">No new notifications.</li>';
            return;
        }

        notifications.forEach(notif => {
            const li = document.createElement('li');
            li.classList.add('p-4', 'flex', 'items-start', 'cursor-pointer', 'hover:bg-gray-50');
            if (!notif.read) {
                li.classList.add('bg-blue-50', 'font-semibold');
            }
            li.dataset.notificationId = notif.id;

            li.innerHTML = `
                <div class="flex-shrink-0 w-3 h-3 rounded-full mr-3 mt-1 ${notif.is_read ? 'bg-gray-300' : 'bg-blue-500'}"></div>
                <div class="flex-grow">
                    <h4 class="text-gray-800 text-base mb-1">${notif.title}</h4>
                    <div class="text-sm text-gray-600 mb-1">${notif.message}</div>
                    <p class="text-xs text-gray-500">${new Date(notif.created_at).toLocaleString()}</p>
                </div>
            `;
            notificationsList.appendChild(li);

            li.addEventListener('click', () => {
                if (!notif.is_read) {
                    fetch('api/notifications.php?action=mark_read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${notif.id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notif.is_read = true;
                            renderNotifications();
                            updateUnreadNotificationsDot();
                        }
                    });
                }
            });
        });
    }

    function updateUnreadNotificationsDot() {
        const unreadCount = notifications.filter(notif => !notif.read).length;
        if (unreadCount > 0) {
            unreadNotificationsDot.classList.remove('hidden');
        } else {
            unreadNotificationsDot.classList.add('hidden');
        }
    }

    function openSubmitPaymentModal() {
        submitPaymentModal.classList.remove('hidden');
        fetchBankDetails();
        fetchPreviousOrders();
    }

    function closeSubmitPaymentModal() {
        submitPaymentModal.classList.add('hidden');
    }

    function fetchBankDetails() {
        fetch('api/bank_details.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Bank details data:', data);
                if (data.success) {
                    bankDetails = data.data;
                    displayBankDetails();
                    populateBankDropdown();
                } else {
                    bankDetailsSection.innerHTML = `<p>${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Fatal error fetching bank details:', error);
                bankDetailsSection.innerHTML = '<p>Could not load bank details.</p>';
            });
    }

    function displayBankDetails() {
        let detailsHtml = '<h4 class="text-lg font-bold mb-2">Bank Account Details</h4>';
        if (bankDetails && bankDetails.length > 0) {
            bankDetails.forEach(bank => {
                detailsHtml += `
                    <div class="bg-gray-100 p-4 rounded-lg mb-2">
                        <p><strong>Bank:</strong> ${bank.bank_name}</p>
                        <p><strong>Account Name:</strong> ${bank.account_name}</p>
                        <p><strong>Account Number:</strong> ${bank.account_number}</p>
                        <p><strong>Charge:</strong> ₦${parseFloat(bank.charge).toFixed(2)}</p>
                        <p><strong>Instructions:</strong> ${bank.instructions}</p>
                    </div>
                `;
            });
        } else {
            detailsHtml += '<p>No bank details available.</p>';
        }
        bankDetailsSection.innerHTML = detailsHtml;
    }

    function populateBankDropdown() {
        const bankSelect = document.getElementById('bank_id');
        bankSelect.innerHTML = '<option value="">Select a Bank</option>';
        if (bankDetails && bankDetails.length > 0) {
            bankDetails.forEach(bank => {
                const option = document.createElement('option');
                option.value = bank.id;
                option.textContent = `${bank.bank_name} - ${bank.account_name}`;
                bankSelect.appendChild(option);
            });
        }
    }

    function fetchPreviousOrders() {
        fetch('api/payment_orders.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    previousOrdersSection.innerHTML = `
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Amount</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Date</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                ${data.data.map(order => `
                                    <tr>
                                        <td class="text-left py-3 px-4">₦${Number(order.amount).toLocaleString()}</td>
                                        <td class="text-left py-3 px-4">${order.status}</td>
                                        <td class="text-left py-3 px-4">${new Date(order.created_at).toLocaleString()}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    previousOrdersSection.innerHTML = '<p>Could not load previous orders.</p>';
                }
            });
    }

    submitPaymentButton.addEventListener('click', openSubmitPaymentModal);
    closePaymentModalButton.addEventListener('click', closeSubmitPaymentModal);

    // --- Profile Page Logic (New) ---
    function updateProfilePage() {
        profileNameElement.textContent = userProfile.name;
        profileEmailElement.textContent = userProfile.email;
        profilePhoneElement.textContent = userProfile.phone;
        profileTierElement.textContent = `Tier ${userProfile.tier}`;
        if (userProfile.tier >= 2) {
            upgradeTierBtn.disabled = true;
            upgradeTierBtn.textContent = 'Tier 2 Activated';
            upgradeTierBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            upgradeTierBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        } else {
            upgradeTierBtn.disabled = false;
            upgradeTierBtn.textContent = 'Upgrade to Tier 2';
            upgradeTierBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            upgradeTierBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
        }
    }

    // --- Dark/Light Mode Logic (New) ---
    function applyTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
        isDarkMode = isDark;
        darkModeToggle.checked = isDark;
    }


    // --- Event Listeners ---

    toggleBalanceVisibilityButton.addEventListener('click', () => {
        balancesVisible = !balancesVisible;
        balanceValues.forEach(el => {
            if (balancesVisible) {
                el.textContent = el.dataset.originalValue;
                el.classList.remove('blur-sm');
            } else {
                el.dataset.originalValue = el.textContent;
                el.textContent = '****';
                el.classList.add('blur-sm');
            }
        });
        toggleText.textContent = balancesVisible ? 'Hide' : 'Show';
        toggleBalanceVisibilityButton.querySelector('i').classList.toggle('fa-eye', balancesVisible);
        toggleBalanceVisibilityButton.querySelector('i').classList.toggle('fa-eye-slash', !balancesVisible);
    });

    balanceValues.forEach(el => {
        el.dataset.originalValue = el.textContent;
    });

    copyButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const accountNumber = event.currentTarget.dataset.account;
            if (accountNumber) {
                const tempInput = document.createElement('textarea');
                tempInput.value = accountNumber;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                alert('Account number copied: ' + accountNumber);
            }
        });
    });

    refreshButton.addEventListener('click', () => {
        fetchUserData();
        fetchTransactions();
    });

    dashboardFundWalletButton.addEventListener('click', () => {
        renderPage('fund-wallet');
    });

    copyReferralLinkButton.addEventListener('click', () => {
        const referralLink = document.getElementById('referral-link-display').value;
        if (!referralLink) {
            alert('Referral link not available. Please try refreshing the page.');
            return;
        }
        copyToClipboard(referralLink, copyReferralLinkButton, 'Referral link copied to clipboard!');
    });

    viewReferralsButton.addEventListener('click', () => {
        showReferralDetailsModal();
    });

    dashboardServiceButtons.forEach(button => {
        button.addEventListener('click', () => {
            const serviceName = button.dataset.service;
            if (serviceName === 'more') {
                showMoreServicesModal();
            } else {
                renderPage('service', serviceName);
            }
        });
    });

    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            navButtons.forEach(btn => btn.classList.remove('text-blue-600'));
            navButtons.forEach(btn => btn.classList.add('text-gray-500'));
            button.classList.remove('text-gray-500');
            button.classList.add('text-blue-600');
            const navTarget = button.dataset.nav;
            if (navTarget === 'home') {
                renderPage('dashboard');
            } else if (navTarget === 'services') {
                showMoreServicesModal();
            } else if (navTarget === 'notifications') {
                showNotificationsModal();
            } else if (navTarget === 'profile') { // New
                renderPage('profile');
            }
            else {
                alert(`Navigating to ${navTarget} page...`);
            }
        });
    });

    viewAllTransactionsBtn.addEventListener('click', () => {
        currentTransactionPage = 1;
        transactionSearchInput.value = '';
        filterTransactionsAndRender();
        transactionHistoryModal.classList.remove('hidden');
    });

    transactionCalculatorDashboardBtn.addEventListener('click', () => {
        showTransactionCalculatorModal();
    });

    backToDashboardFromFundButton.addEventListener('click', () => {
        renderPage('dashboard');
    });

    paymentMethodButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const gateway = e.currentTarget.dataset.gateway;
            alert(`Initiating payment via ${gateway.toUpperCase()}... (This would integrate with the actual API)`);
            console.log(`Simulating API call for ${gateway} payment.`);
        });
    });

    backToDashboardFromServiceButton.addEventListener('click', () => {
        renderPage('dashboard');
    });

    // Data Form Bulk Purchase Toggle
    dataBulkPurchaseToggle.addEventListener('change', () => {
        if (dataBulkPurchaseToggle.checked) {
            dataPurchaseTypeText.textContent = 'Bulk Purchase';
            dataSinglePhoneInput.classList.add('hidden');
            dataBulkPhoneInput.classList.remove('hidden');
            dataBulkTotalCostSection.classList.remove('hidden');
            dataPhoneNumberInput.removeAttribute('required');
            dataPhoneNumbersBulkInput.setAttribute('required', 'true');
        } else {
            dataPurchaseTypeText.textContent = 'Single Purchase';
            dataSinglePhoneInput.classList.remove('hidden');
            dataBulkPhoneInput.classList.add('hidden');
            dataBulkTotalCostSection.classList.add('hidden');
            dataPhoneNumberInput.setAttribute('required', 'true');
            dataPhoneNumbersBulkInput.removeAttribute('required');
        }
        updateDataRecipientCountAndCost();
    });

    dataPhoneNumberInput.addEventListener('input', async () => {
        if (!dataBulkPurchaseToggle.checked) {
            const phoneNumber = dataPhoneNumberInput.value;
            if (!dataNetworkOverrideToggle.checked) {
                const detected = await detectNetworkFromPhone(phoneNumber);
                dataDetectedNetworkDisplay.textContent = detected;
                if (detected !== 'N/A' && detected !== 'Unknown') {
                    loadDataPlans(detected);
                } else {
                    dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
                }
            }
        }
        await updateDataRecipientCountAndCost();
    });

    dataPhoneNumbersBulkInput.addEventListener('input', async () => {
        await updateDataRecipientCountAndCost();
    });

    dataNetworkOverrideToggle.addEventListener('change', async () => {
        if (dataNetworkOverrideToggle.checked) {
            dataManualNetworkSelection.style.display = 'block';
            dataDetectedNetworkDisplay.textContent = 'Manual';
            dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
        } else {
            dataManualNetworkSelection.style.display = 'none';
            await updateDataRecipientCountAndCost();
        }
        await updateDataRecipientCountAndCost();
    });

    dataManualNetworkSelect.addEventListener('change', async () => {
        const selectedNetwork = dataManualNetworkSelect.value;
        if (selectedNetwork) {
            loadDataPlans(selectedNetwork);
        } else {
            dataPlanSelect.innerHTML = '<option value="">Select a plan</option>';
        }
        await updateDataRecipientCountAndCost();
    });

    dataPlanSelect.addEventListener('change', () => {
        const recipients = getDataRecipients();
        const productId = dataPlanSelect.value;
        let pricePerPlan = 0;
        const selectedNetwork = dataNetworkOverrideToggle.checked ? dataManualNetworkSelect.value : dataDetectedNetworkDisplay.textContent;
        if(productId && serviceData.data && serviceData.data.networks && serviceData.data.networks[selectedNetwork]) {
            const selectedPlan = serviceData.data.networks[selectedNetwork].find(p => p.plan_code === productId);
            if(selectedPlan) {
                pricePerPlan = selectedPlan.price;
            }
        }
        const totalCost = pricePerPlan * recipients.length;
        dataBulkTotalCostDisplay.textContent = `₦${totalCost.toFixed(2)}`;
    });

    dataScheduleToggle.addEventListener('change', () => {
        if (dataScheduleToggle.checked) {
            dataScheduleFields.classList.remove('hidden');
            dataScheduleDateInput.setAttribute('required', 'true');
            dataScheduleTimeInput.setAttribute('required', 'true');
        } else {
            dataScheduleFields.classList.add('hidden');
            dataScheduleDateInput.removeAttribute('required');
            dataScheduleTimeInput.removeAttribute('required');
            dataScheduleDateInput.value = '';
            dataScheduleTimeInput.value = '';
        }
    });

    dataVendingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = dataVendingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const recipients = getDataRecipients();
        const selectedNetwork = dataNetworkOverrideToggle.checked ? dataManualNetworkSelect.value : dataDetectedNetworkDisplay.textContent;
        const productId = dataPlanSelect.value;

        if (recipients.length === 0 || selectedNetwork === 'N/A' || selectedNetwork === 'Unknown' || !productId) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('phoneNumber', recipients[0]); // Sending the first for simplicity
        formData.append('product_id', productId);

        fetch('api/data_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                resetDataForm();
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    // Airtime Form Bulk Purchase Toggle
    airtimeBulkPurchaseToggle.addEventListener('change', () => {
        if (airtimeBulkPurchaseToggle.checked) {
            airtimePurchaseTypeText.textContent = 'Bulk Purchase';
            airtimeSinglePhoneInput.classList.add('hidden');
            airtimeBulkPhoneInput.classList.remove('hidden');
            airtimeBulkTotalCostSection.classList.remove('hidden');
            airtimePhoneNumberInput.removeAttribute('required');
            airtimePhoneNumbersBulkInput.setAttribute('required', 'true');
        } else {
            airtimePurchaseTypeText.textContent = 'Single Purchase';
            airtimeSinglePhoneInput.classList.remove('hidden');
            airtimeBulkPhoneInput.classList.add('hidden');
            airtimeBulkTotalCostSection.classList.add('hidden');
            airtimePhoneNumberInput.setAttribute('required', 'true');
            airtimePhoneNumbersBulkInput.removeAttribute('required');
        }
        updateAirtimeRecipientCountAndCost();
    });

    airtimePhoneNumberInput.addEventListener('input', async () => {
        if (!airtimeBulkPurchaseToggle.checked) {
            const phoneNumber = airtimePhoneNumberInput.value;
            if (!airtimeNetworkOverrideToggle.checked) {
                const detected = await detectNetworkFromPhone(phoneNumber);
                airtimeDetectedNetworkDisplay.textContent = detected;
            }
        }
        await updateAirtimeRecipientCountAndCost();
    });

    airtimePhoneNumbersBulkInput.addEventListener('input', async () => {
        await updateAirtimeRecipientCountAndCost();
    });

    airtimeNetworkOverrideToggle.addEventListener('change', async () => {
        if (airtimeNetworkOverrideToggle.checked) {
            airtimeManualNetworkSelection.style.display = 'block';
            airtimeDetectedNetworkDisplay.textContent = 'Manual';
        } else {
            airtimeManualNetworkSelection.style.display = 'none';
            await updateAirtimeRecipientCountAndCost();
        }
        await updateAirtimeRecipientCountAndCost();
    });

    airtimeAmountInput.addEventListener('input', async () => {
        await updateAirtimeRecipientCountAndCost()
    });

    airtimeScheduleToggle.addEventListener('change', () => {
        if (airtimeScheduleToggle.checked) {
            airtimeScheduleFields.classList.remove('hidden');
            airtimeScheduleDateInput.setAttribute('required', 'true');
            airtimeScheduleTimeInput.setAttribute('required', 'true');
        } else {
            airtimeScheduleFields.classList.add('hidden');
            airtimeScheduleDateInput.removeAttribute('required');
            airtimeScheduleTimeInput.removeAttribute('required');
            airtimeScheduleDateInput.value = '';
            airtimeScheduleTimeInput.value = '';
        }
    });

    airtimeVendingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = airtimeVendingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const recipients = getAirtimeRecipients();
        const selectedNetwork = airtimeNetworkOverrideToggle.checked ? airtimeManualNetworkSelect.value : airtimeDetectedNetworkDisplay.textContent;
        const amountPerRecipient = parseFloat(airtimeAmountInput.value);

        if (recipients.length === 0 || selectedNetwork === 'N/A' || selectedNetwork === 'Unknown' || !amountPerRecipient || amountPerRecipient <= 0) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const isBulk = airtimeBulkPurchaseToggle.checked;
        const phoneNumbers = getAirtimeRecipients().join(',');
        const batchId = isBulk ? `batch_${Date.now()}` : null;

        const formData = new FormData();
        formData.append('phoneNumbers', phoneNumbers);
        formData.append('amount', amountPerRecipient);
        formData.append('network', selectedNetwork);
        if (isBulk) {
            formData.append('batch_id', batchId);
            formData.append('source', 'API_BULK');
        }

        fetch('api/airtime_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = "Batch processed.\n";
                data.responses.forEach(res => {
                    message += `\n${res.phoneNumber}: ${res.message}`;
                });
                alert(message);
            } else {
                alert(`Error: ${data.message}`);
            }
            resetAirtimeForm();
            fetchTransactions();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });


    electricityServiceTypeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (electricityPostpaidRadio.checked) {
                electricityTokenSection.classList.add('hidden');
                electricityTokenInput.value = '';
            } else {
                electricityTokenSection.classList.remove('hidden');
                electricityTokenInput.value = '';
            }
        });
    });

    verifyMeterBtn.addEventListener('click', () => {
        const meterNumber = meterNumberInput.value.trim();
        const disco = discoProviderSelect.value;
        const serviceType = document.querySelector('input[name="electricity-type"]:checked').value;

        if (!meterNumber || !disco) {
            alert('Please enter a meter number and select a disco provider.');
            return;
        }

        verifyMeterBtn.innerHTML = 'Verifying...';
        verifyMeterBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'verify_meter');
        formData.append('meterNumber', meterNumber);
        formData.append('disco', disco);
        formData.append('serviceType', serviceType);

        fetch('api/electricity_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                electricityVerificationResult.classList.remove('hidden');
                verifiedCustomerNameElectricity.textContent = data.data.customer_name;
            } else {
                alert(`Error: ${data.message}`);
                electricityVerificationResult.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while verifying the meter.');
            electricityVerificationResult.classList.add('hidden');
        })
        .finally(() => {
            verifyMeterBtn.innerHTML = 'Verify';
            verifyMeterBtn.disabled = false;
        });
    });

    electricityVendingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = electricityVendingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const serviceType = document.querySelector('input[name="electricity-type"]:checked').value;
        const meterNumber = meterNumberInput.value;
        const disco = discoProviderSelect.value;
        const amount = parseFloat(electricityAmountInput.value);

        if (electricityVerificationResult.classList.contains('hidden')) {
            alert('Please verify the meter number before proceeding.');
            return;
        }

        if (!meterNumber || !disco || !amount || amount <= 0) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('serviceType', serviceType);
        formData.append('meterNumber', meterNumber);
        formData.append('disco', disco);
        formData.append('amount', amount);

        fetch('api/electricity_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                if (data.token) {
                    electricityTokenInput.value = data.token;
                }
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    cabletvProviderSelect.addEventListener('change', () => {
        const selectedProvider = cabletvProviderSelect.value;
        resetCableTVForm();
        if (selectedProvider) {
            loadCableTvPlans(selectedProvider);
        }
        verifySmartCardBtn.disabled = smartCardNumberInput.value.trim().length === 0 || !selectedProvider;
    });

    smartCardNumberInput.addEventListener('input', () => {
        verifySmartCardBtn.disabled = smartCardNumberInput.value.trim().length === 0 || !cabletvProviderSelect.value;
        cabletvVerificationResult.classList.add('hidden');
        cabletvBuyBtn.disabled = true;
        isSmartCardVerified = false;
    });

    verifySmartCardBtn.addEventListener('click', () => {
        const provider = cabletvProviderSelect.value;
        const smartCardNumber = smartCardNumberInput.value.trim();

        if (!provider || !smartCardNumber) {
            alert('Please select a provider and enter a smart card number.');
            return;
        }

        console.log(`Verifying smart card ${smartCardNumber} for ${provider}...`);

        if (smartCardNumber.length === 9 && ['dstv', 'gotv', 'startimes'].includes(provider)) {
            const dummyCustomerName = "Test Customer";
            const dummyCurrentPlan = "Basic Plan";
            const dummyAvailablePackages = cabletvPlans[provider];

            verifiedCustomerName.textContent = dummyCustomerName;
            verifiedSubscriptionStatus.textContent = dummyCurrentPlan;
            cabletvVerificationResult.classList.remove('hidden');
            isSmartCardVerified = true;
            cabletvBuyBtn.disabled = false;
            loadCableTvPlans(provider, dummyAvailablePackages);
        } else {
            alert('Smart card verification failed. Please check the number and try again.');
            cabletvVerificationResult.classList.add('hidden');
            isSmartCardVerified = false;
            cabletvBuyBtn.disabled = true;
        }
    });

    cabletvVendingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = cabletvVendingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        if (!isSmartCardVerified) {
            alert('Please verify your smart card first.');
            return;
        }

        const provider = cabletvProviderSelect.value;
        const smartCardNumber = smartCardNumberInput.value;
        const planValue = cabletvPlanSelect.value;

        if (!provider || !smartCardNumber || !planValue) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('provider', provider);
        formData.append('smartCardNumber', smartCardNumber);
        formData.append('plan', planValue);

        fetch('api/cabletv_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                resetCableTVForm();
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    bettingFundingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = bettingFundingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const platform = bettingPlatformSelect.value;
        const userId = bettingUserIdInput.value;
        const amount = parseFloat(bettingAmountInput.value);

        if (!platform || !userId || !amount || amount <= 0) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('platform', platform);
        formData.append('userId', userId);
        formData.append('amount', amount);

        fetch('api/betting_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                resetBettingForm();
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    examTypeSelect.addEventListener('change', updateExamTotalCost);
    examQuantityInput.addEventListener('input', updateExamTotalCost);

    examVendingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = examVendingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const examType = examTypeSelect.value;
        const quantity = parseInt(examQuantityInput.value);

        if (!examType || !quantity || quantity <= 0) {
            alert('Please select an exam type and quantity.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('card_type_id', examType);
        formData.append('quantity', quantity);

        fetch('api/exam_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                resetExamForm();
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    smsMessageTextarea.addEventListener('input', calculateSmsUnitsAndCost);
    recipientNumbersTextarea.addEventListener('input', updateRecipientCount);

    usePhonebookToggle.addEventListener('change', () => {
        if (usePhonebookToggle.checked) {
            manualRecipientInput.classList.add('hidden');
            phonebookSelectionSection.classList.remove('hidden');
            saveContactsToggle.checked = false;
            updateRecipientCount();
        } else {
            manualRecipientInput.classList.remove('hidden');
            phonebookSelectionSection.classList.add('hidden');
            phonebookGroupSelect.value = '';
            selectedPhonebookContacts = [];
            updateRecipientCount();
        }
    });

    phonebookGroupSelect.addEventListener('change', () => {
        const selectedGroupId = phonebookGroupSelect.value;
        const selectedGroup = phoneBookGroups.find(group => group.id === selectedGroupId);
        if (selectedGroup) {
            selectedPhonebookContacts = selectedGroup.contacts;
            selectedPhonebookContactsDisplay.textContent = `${selectedGroup.contacts.length} contacts selected from "${selectedGroup.name}"`;
        } else {
            selectedPhonebookContacts = [];
            selectedPhonebookContactsDisplay.textContent = '';
        }
        updateRecipientCount();
    });

    bulksmsScheduleToggle.addEventListener('change', () => {
        if (bulksmsScheduleToggle.checked) {
            bulksmsScheduleFields.classList.remove('hidden');
            bulksmsScheduleDateInput.setAttribute('required', 'true');
            bulksmsScheduleTimeInput.setAttribute('required', 'true');
        } else {
            bulksmsScheduleFields.classList.add('hidden');
            bulksmsScheduleDateInput.removeAttribute('required');
            bulksmsScheduleTimeInput.removeAttribute('required');
            bulksmsScheduleDateInput.value = '';
            bulksmsScheduleTimeInput.value = '';
        }
    });

    bulksmsSendingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = bulksmsSendingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const senderId = smsSenderIdSelect.value;
        const recipients = getRecipientNumbers();
        const message = smsMessageTextarea.value;

        if (!senderId || recipients.length === 0 || !message) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('senderId', senderId);
        formData.append('recipients', JSON.stringify(recipients));
        formData.append('message', message);

        fetch('api/bulksms_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                resetBulkSMSForm();
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    giftcardBuyBtn.addEventListener('click', () => {
        currentGiftCardMode = 'buy';
        giftcardSellDetails.classList.add('hidden');
        giftcardBuyBtn.classList.add('bg-blue-600', 'text-white');
        giftcardBuyBtn.classList.remove('bg-gray-200', 'text-gray-700');
        giftcardSellBtn.classList.add('bg-gray-200', 'text-gray-700');
        giftcardSellBtn.classList.remove('bg-blue-600', 'text-white');
        giftcardSubmitBtn.textContent = 'Proceed to Buy';
        updateGiftCardEstimatedValue();
    });

    giftcardSellBtn.addEventListener('click', () => {
        currentGiftCardMode = 'sell';
        giftcardSellDetails.classList.remove('hidden');
        giftcardSellBtn.classList.add('bg-blue-600', 'text-white');
        giftcardSellBtn.classList.remove('bg-gray-200', 'text-gray-700');
        giftcardBuyBtn.classList.add('bg-gray-200', 'text-gray-700');
        giftcardBuyBtn.classList.remove('bg-blue-600', 'text-white');
        giftcardSubmitBtn.textContent = 'Proceed to Sell';
        updateGiftCardEstimatedValue();
    });

    giftcardTypeSelect.addEventListener('change', updateGiftCardEstimatedValue);
    giftcardDenominationInput.addEventListener('input', updateGiftCardEstimatedValue);

    giftcardForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = giftcardForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const cardType = giftcardTypeSelect.value;
        const denomination = parseFloat(giftcardDenominationInput.value);

        if (!cardType || !denomination || denomination <= 0) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('cardType', cardType);
        formData.append('denomination', denomination);
        formData.append('mode', currentGiftCardMode);

        if (currentGiftCardMode === 'sell') {
            formData.append('code', giftcardCodeInput.value);
            if (giftcardImageInput.files.length > 0) {
                formData.append('image', giftcardImageInput.files[0]);
            }
        }

        fetch('api/giftcard_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                resetGiftCardForm();
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    rechargeCardNetworkSelect.addEventListener('change', updateRechargeCardTotalCost);
    rechargeCardAmountSelect.addEventListener('change', updateRechargeCardTotalCost);
    rechargeCardQuantityInput.addEventListener('input', updateRechargeCardTotalCost);

    rechargeCardPrintingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const submitButton = rechargeCardPrintingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        const network = rechargeCardNetworkSelect.value;
        const amount = rechargeCardAmountSelect.value;
        const quantity = parseInt(rechargeCardQuantityInput.value);

        if (!network || !amount || !quantity || quantity <= 0) {
            alert('Please fill in all details correctly.');
            return;
        }

        submitButton.innerHTML = 'Processing...';
        submitButton.disabled = true;

        const formData = new FormData();
        formData.append('network', network);
        formData.append('amount', amount);
        formData.append('quantity', quantity);

        fetch('api/rechargecard_modular.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || (data.status && data.status === 'Pending')) {
                alert(data.message);
                resetRechargeCardForm();
                fetchTransactions(); // Refresh transactions
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request.');
        })
        .finally(() => {
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        });
    });

    moreServicesBtn.addEventListener('click', showMoreServicesModal);

    closeMoreServicesModalBtn.addEventListener('click', () => {
        moreServicesModal.classList.add('hidden');
    });

    moreServicesModal.addEventListener('click', (e) => {
        if (e.target === moreServicesModal) {
            moreServicesModal.classList.add('hidden');
        }
    });

    managePhonebookBtn.addEventListener('click', () => {
        showPhonebookManagerModal();
    });

    closePhonebookManagerModalBtn.addEventListener('click', () => {
        phonebookManagerModal.classList.add('hidden');
    });

    phonebookManagerModal.addEventListener('click', (e) => {
        if (e.target === phonebookManagerModal) {
            phonebookManagerModal.classList.add('hidden');
        }
    });

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.dataset.tab;
            switchTab(tab);
        });
    });

    addSingleContactBtn.addEventListener('click', () => {
        const name = newContactNameInput.value.trim();
        const number = newContactNumberInput.value.trim();
        const groupId = addContactGroupSelect.value;

        if (!name || !number) {
            alert('Please enter contact name and number.');
            return;
        }

        if (groupId === 'new-group') {
            const newGroupName = prompt("Enter a name for the new contact group:");
            if (newGroupName) {
                const newGroupId = `group-${Date.now()}`;
                phoneBookGroups.push({ id: newGroupId, name: newGroupName, contacts: [number] });
                alert(`Contact "${name}" added to new group "${newGroupName}".`);
            } else {
                alert('New group name cannot be empty.');
                return;
            }
        } else if (groupId) {
            const targetGroup = phoneBookGroups.find(group => group.id === groupId);
            if (targetGroup) {
                targetGroup.contacts.push(number);
                alert(`Contact "${name}" added to group "${targetGroup.name}".`);
            }
        } else {
            alert('Please select a group or create a new one.');
            return;
        }
        loadPhoneBookGroups();
        resetAddContactForm();
    });

    createNewGroupBtn.addEventListener('click', () => {
        const newGroupName = newGroupNameInput.value.trim();
        if (newGroupName) {
            const newGroupId = `group-${Date.now()}`;
            phoneBookGroups.push({ id: newGroupId, name: newGroupName, contacts: [] });
            alert(`Group "${newGroupName}" created.`);
            loadPhoneBookGroups();
            newGroupNameInput.value = '';
        } else {
            alert('Group name cannot be empty.');
        }
    });

    uploadContactsBtn.addEventListener('click', () => {
        const file = contactUploadFile.files[0];
        const groupId = uploadFileGroupSelect.value;

        if (!file) {
            alert('Please select a file to upload.');
            return;
        }
        if (!groupId) {
            alert('Please select a group or create a new one.');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const content = e.target.result;
            let numbers = [];
            if (file.name.endsWith('.csv')) {
                numbers = content.split('\n').map(row => row.split(',')[0].trim()).filter(num => num.length > 0);
            } else {
                numbers = content.split('\n').map(num => num.trim()).filter(num => num.length > 0);
            }

            if (groupId === 'new-group') {
                const newGroupName = prompt("Enter a name for the new contact group:");
                if (newGroupName) {
                    const newGroupId = `group-${Date.now()}`;
                    phoneBookGroups.push({ id: newGroupId, name: newGroupName, contacts: numbers });
                    alert(`${numbers.length} contacts uploaded to new group "${newGroupName}".`);
                } else {
                    alert('New group name cannot be empty.');
                    return;
                }
            } else {
                const targetGroup = phoneBookGroups.find(group => group.id === groupId);
                if (targetGroup) {
                    targetGroup.contacts = [...new Set([...targetGroup.contacts, ...numbers])];
                    alert(`${numbers.length} contacts uploaded to group "${targetGroup.name}".`);
                }
            }
            loadPhoneBookGroups();
            resetUploadContactsForm();
        };
        reader.readAsText(file);
    });

    closeReferralDetailsModalBtn.addEventListener('click', () => {
        referralDetailsModal.classList.add('hidden');
    });

    referralDetailsModal.addEventListener('click', (e) => {
        if (e.target === referralDetailsModal) {
            referralDetailsModal.classList.add('hidden');
        }
    });

    // Improved copy to clipboard function with better UX
    function copyToClipboard(text, button, successMessage = 'Copied to clipboard!') {
        const originalContent = button.innerHTML;
        
        if (navigator.clipboard && window.isSecureContext) {
            // Modern async clipboard API
            return navigator.clipboard.writeText(text).then(() => {
                showCopySuccess(button, originalContent, successMessage);
            }).catch(() => {
                fallbackCopyToClipboard(text, button, originalContent, successMessage);
            });
        } else {
            // Fallback for older browsers or non-secure contexts
            fallbackCopyToClipboard(text, button, originalContent, successMessage);
        }
    }
    
    function fallbackCopyToClipboard(text, button, originalContent, successMessage) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(button, originalContent, successMessage);
            } else {
                showCopyError(button, originalContent);
            }
        } catch (err) {
            showCopyError(button, originalContent);
        } finally {
            document.body.removeChild(textArea);
        }
    }
    
    function showCopySuccess(button, originalContent, message) {
        button.innerHTML = '<i class="fas fa-check text-green-600"></i>';
        button.className = button.className.replace('bg-blue-100 text-blue-700 hover:bg-blue-200', 'bg-green-100 text-green-700');
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.className = button.className.replace('bg-green-100 text-green-700', 'bg-blue-100 text-blue-700 hover:bg-blue-200');
        }, 2000);
    }
    
    function showCopyError(button, originalContent) {
        button.innerHTML = '<i class="fas fa-times text-red-600"></i>';
        button.className = button.className.replace('bg-blue-100 text-blue-700 hover:bg-blue-200', 'bg-red-100 text-red-700');
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.className = button.className.replace('bg-red-100 text-red-700', 'bg-blue-100 text-blue-700 hover:bg-blue-200');
        }, 2000);
        alert('Failed to copy to clipboard. Please copy manually.');
    }
    
    // Copy functionality with improved UX
    copyModalReferralLinkButton.addEventListener('click', () => {
        copyToClipboard(displayReferralLinkInput.value, copyModalReferralLinkButton, 'Referral link copied to clipboard!');
    });

    closeTransactionHistoryModalBtn.addEventListener('click', () => {
        transactionHistoryModal.classList.add('hidden');
    });

    transactionHistoryModal.addEventListener('click', (e) => {
        if (e.target === transactionHistoryModal) {
            transactionHistoryModal.classList.add('hidden');
        }
    });

    transactionSearchInput.addEventListener('input', () => {
        currentTransactionPage = 1;
        filterTransactionsAndRender();
    });

    prevTransactionsBtn.addEventListener('click', () => {
        if (currentTransactionPage > 1) {
            currentTransactionPage--;
            renderTransactions();
        }
    });

    nextTransactionsBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredTransactions.length / transactionsPerPage);
        if (currentTransactionPage < totalPages) {
            currentTransactionPage++;
            renderTransactions();
        }
    });

    exportTransactionsBtn.addEventListener('click', () => {
        exportTransactionsToPDF(filteredTransactions);
    });

    closeTransactionDetailsModalBtn.addEventListener('click', () => {
        transactionDetailsModal.classList.add('hidden');
    });

    transactionDetailsModal.addEventListener('click', (e) => {
        if (e.target === transactionDetailsModal) {
            transactionDetailsModal.classList.add('hidden');
        }
    });

    printReceiptBtn.addEventListener('click', (e) => {
        const transactionId = e.currentTarget.dataset.transactionId;
        fetch(`api/transaction-details.php?id=${transactionId}`)
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    printTransactionReceipt(response.data);
                } else {
                    alert(response.message);
                }
            })
            .catch(error => {
                console.error('Error fetching transaction details:', error);
                alert('An error occurred while fetching transaction details.');
            });
    });

    closeTransactionCalculatorModalBtn.addEventListener('click', () => {
        transactionCalculatorModal.classList.add('hidden');
    });

    transactionCalculatorModal.addEventListener('click', (e) => {
        if (e.target === transactionCalculatorModal) {
            transactionCalculatorModal.classList.add('hidden');
        }
    });

    timeFilterBtns.forEach(button => {
        button.addEventListener('click', (e) => {
            timeFilterBtns.forEach(btn => {
                btn.classList.remove('bg-blue-700', 'text-white');
                btn.classList.add('bg-blue-100', 'text-blue-700');
            });
            e.currentTarget.classList.remove('bg-blue-100', 'text-blue-700');
            e.currentTarget.classList.add('bg-blue-700', 'text-white');

            const period = e.currentTarget.dataset.period;
            customDateRangeSection.classList.add('hidden');

            let startDate = null;
            let endDate = new Date();

            switch (period) {
                case 'today':
                    startDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                    break;
                case 'week':
                    startDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate() - endDate.getDay());
                    break;
                case 'month':
                    startDate = new Date(endDate.getFullYear(), endDate.getMonth(), 1);
                    break;
                case 'custom':
                    customDateRangeSection.classList.remove('hidden');
                    return;
            }
            filterCalculatorTransactionsByDate(startDate, endDate);
        });
    });

    applyCustomFilterBtn.addEventListener('click', () => {
        const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
        const endDate = endDateInput.value ? new Date(endDateInput.value) : null;

        if ((startDate && isNaN(startDate.getTime())) || (endDate && isNaN(endDate.getTime()))) {
            alert('Please enter valid dates.');
            return;
        }
        if (startDate && endDate && startDate > endDate) {
            alert('Start date cannot be after end date.');
            return;
        }
        filterCalculatorTransactionsByDate(startDate, endDate);
    });

    calculatorPrevTransactionsBtn.addEventListener('click', () => {
        if (calculatorCurrentTransactionPage > 1) {
            calculatorCurrentTransactionPage--;
            renderCalculatorTransactions();
        }
    });

    calculatorNextTransactionsBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(calculatorFilteredTransactions.length / transactionsPerPage);
        if (calculatorCurrentTransactionPage < totalPages) {
            calculatorCurrentTransactionPage++;
            renderCalculatorTransactions();
        }
    });

    // Notifications Modal Event Listeners
    closeNotificationsModalBtn.addEventListener('click', () => {
        notificationsModal.classList.add('hidden');
    });

    notificationsModal.addEventListener('click', (e) => {
        if (e.target === notificationsModal) {
            notificationsModal.classList.add('hidden');
        }
    });

    markAllReadBtn.addEventListener('click', () => {
        notifications.forEach(notif => {
            notif.read = true;
        });
        renderNotifications();
        updateUnreadNotificationsDot();
        alert('All notifications marked as read.');
    });

    // Profile Page Event Listeners (New)
    backToDashboardFromProfileButton.addEventListener('click', () => {
        renderPage('dashboard');
    });

    upgradeTierBtn.addEventListener('click', () => {
        if (userProfile.tier < 2) {
            bvnVerificationModal.classList.remove('hidden');
            bvnInput.value = ''; // Clear previous input
        }
    });

    closeBvnModalBtn.addEventListener('click', () => {
        bvnVerificationModal.classList.add('hidden');
    });

    bvnVerificationModal.addEventListener('click', (e) => {
        if (e.target === bvnVerificationModal) {
            bvnVerificationModal.classList.add('hidden');
        }
    });

    verifyBvnBtn.addEventListener('click', () => {
        const bvn = bvnInput.value.trim();
        if (bvn.length === 11 && /^\d+$/.test(bvn)) {
            // Simulate BVN verification success
            userProfile.tier = 2;
            alert('BVN verified successfully! You have been upgraded to Tier 2.');
            bvnVerificationModal.classList.add('hidden');
            updateProfilePage(); // Update the profile page to reflect new tier
        } else {
            alert('Invalid BVN. Please enter an 11-digit number.');
        }
    });

    resetPasswordBtn.addEventListener('click', () => {
        passwordResetModal.classList.remove('hidden');
        newPasswordInput.value = '';
        confirmPasswordInput.value = '';
    });

    closePasswordResetModalBtn.addEventListener('click', () => {
        passwordResetModal.classList.add('hidden');
    });

    passwordResetModal.addEventListener('click', (e) => {
        if (e.target === passwordResetModal) {
            passwordResetModal.classList.add('hidden');
        }
    });

    confirmPasswordResetBtn.addEventListener('click', () => {
        const newPass = newPasswordInput.value;
        const confirmPass = confirmPasswordInput.value;

        if (newPass.length < 6) {
            alert('Password must be at least 6 characters long.');
            return;
        }
        if (newPass !== confirmPass) {
            alert('New password and confirmation do not match.');
            return;
        }
        userProfile.password = newPass; // Simulate password update
        alert('Password reset successfully!');
        passwordResetModal.classList.add('hidden');
    });

    resetPasscodeBtn.addEventListener('click', () => {
        passcodeResetModal.classList.remove('hidden');
        newPasscodeInput.value = '';
        confirmPasscodeInput.value = '';
    });

    closePasscodeResetModalBtn.addEventListener('click', () => {
        passcodeResetModal.classList.add('hidden');
    });

    passcodeResetModal.addEventListener('click', (e) => {
        if (e.target === passcodeResetModal) {
            passcodeResetModal.classList.add('hidden');
        }
    });

    confirmPasscodeResetBtn.addEventListener('click', () => {
        const newCode = newPasscodeInput.value;
        const confirmCode = confirmPasscodeInput.value;

        if (newCode.length !== 4 || !/^\d{4}$/.test(newCode)) {
            alert('Passcode must be a 4-digit number.');
            return;
        }
        if (newCode !== confirmCode) {
            alert('New passcode and confirmation do not match.');
            return;
        }
        userProfile.passcode = newCode; // Simulate passcode update
        alert('Passcode reset successfully!');
        passcodeResetModal.classList.add('hidden');
    });

    darkModeToggle.addEventListener('change', (e) => {
        applyTheme(e.target.checked);
    });


    function fetchUserData() {
        fetch('api/user.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    userProfile = data.data;
                    customerNameElement.textContent = userProfile.name;
                    walletBalanceElement.textContent = parseFloat(userProfile.wallet_balance).toFixed(2);
                    bonusBalanceElement.textContent = parseFloat(userProfile.bonus_balance).toFixed(2);
                    userReferralLink = userProfile.referral_link;
                    document.getElementById('referral-link-display').value = userReferralLink;
                    displayReferralLinkInput.value = userReferralLink;
                    updateProfilePage();
                } else {
                    console.error('Error fetching user data:', data.message);
                    // Display an error to the user
                    appContainer.innerHTML = '<p class="text-red-500 text-center">Could not load user data. Please try again later.</p>';
                }
            })
            .catch(error => {
                console.error('Fatal error fetching user data:', error);
                // Display a fatal error to the user
                appContainer.innerHTML = '<p class="text-red-500 text-center">A critical error occurred. Please try again later.</p>';
            });
    }


    withdrawButton.addEventListener('click', () => {
        withdrawModal.classList.remove('hidden');
    });

    closeWithdrawModalButton.addEventListener('click', () => {
        withdrawModal.classList.add('hidden');
    });

    shareFundButton.addEventListener('click', () => {
        shareFundModal.classList.remove('hidden');
    });

    closeShareFundModalButton.addEventListener('click', () => {
        shareFundModal.classList.add('hidden');
    });


    function showTransactionDetailsModal(transactionId) {
        fetch(`api/transaction-details.php?id=${transactionId}`)
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    const transaction = response.data;
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
                } else {
                    alert(response.message);
                }
            })
            .catch(error => {
                console.error('Error fetching transaction details:', error);
                alert('An error occurred while fetching transaction details.');
            });
    }

    function fetchNotifications() {
        fetch('api/notifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    notifications = data.data;
                    renderNotifications();
                    updateUnreadNotificationsDot();
                } else {
                    console.error('Error fetching notifications:', data.message);
                }
            })
            .catch(error => {
                console.error('Fatal error fetching notifications:', error);
            });
    }

    markAllReadBtn.addEventListener('click', () => {
        fetch('api/notifications.php?action=mark_all_read', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notifications.forEach(notif => {
                        notif.read = true;
                    });
                    renderNotifications();
                    updateUnreadNotificationsDot();
                    alert('All notifications marked as read.');
                } else {
                    alert('Failed to mark all notifications as read.');
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
                alert('An error occurred while marking notifications as read.');
            });
    });

    // --- Initial Load ---
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        applyTheme(true);
    } else {
        applyTheme(false);
    }

    fetchUserData();
    fetchNotifications();
    loadServiceData(); // Load dynamic service data
    fetchTransactions().then(() => {
        filterTransactionsAndRender();
    });
    renderPage('dashboard');
    updateUnreadNotificationsDot();

    // --- Register Sender ID Modal Elements ---
    const registerSenderIdModal = document.getElementById('register-sender-id-modal');
    const closeRegisterSenderIdModalBtn = document.getElementById('close-register-sender-id-modal');
    const registerSenderIdForm = document.getElementById('register-sender-id-form');
    const senderIdRequestsSection = document.getElementById('sender-id-requests-section');

    document.body.addEventListener('click', (e) => {
        if (e.target.matches('#register-sender-id-btn')) {
            registerSenderIdModal.classList.remove('hidden');
            fetchSenderIdRequests();
        }
    });

    closeRegisterSenderIdModalBtn.addEventListener('click', () => {
        registerSenderIdModal.classList.add('hidden');
    });

    registerSenderIdModal.addEventListener('click', (e) => {
        if (e.target === registerSenderIdModal) {
            registerSenderIdModal.classList.add('hidden');
        }
    });

    registerSenderIdForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(registerSenderIdForm);
        formData.append('action', 'register');

        try {
            const response = await fetch('api/sms_sender_ids.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                registerSenderIdForm.reset();
                fetchSenderIdRequests();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred: ' + error.message);
        }
    });

    function fetchSenderIdRequests() {
        fetch('api/sms_sender_ids.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSenderIdRequests(data.sender_ids);
                } else {
                    senderIdRequestsSection.innerHTML = `<p class="text-red-500">${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching sender ID requests:', error);
                senderIdRequestsSection.innerHTML = '<p>Could not load sender ID requests.</p>';
            });
    }

    function renderSenderIdRequests(requests) {
        senderIdRequestsSection.innerHTML = '';
        if (requests.length === 0) {
            senderIdRequestsSection.innerHTML = '<p class="text-gray-500">No sender ID requests found.</p>';
            return;
        }

        const ul = document.createElement('ul');
        ul.className = 'divide-y divide-gray-100';

        requests.forEach(request => {
            const li = document.createElement('li');
            li.className = 'flex justify-between items-center py-3 px-4';
            li.innerHTML = `
                <div>
                    <p class="font-medium text-gray-800">${request.sender_id}</p>
                    <p class="text-xs text-gray-500">${request.status}</p>
                </div>
                <div>
                    <button class="edit-sender-id-request-btn text-blue-600 hover:text-blue-800 mr-2" data-id="${request.id}" data-sender-id="${request.sender_id}" data-sample-message="${request.sample_message}" ${request.status !== 'disapproved' ? 'disabled' : ''}>
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="delete-sender-id-request-btn text-red-600 hover:text-red-800" data-id="${request.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            ul.appendChild(li);
        });
        senderIdRequestsSection.appendChild(ul);

        document.querySelectorAll('.edit-sender-id-request-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const requestId = e.currentTarget.dataset.id;
                const senderId = e.currentTarget.dataset.senderId;
                const sampleMessage = e.currentTarget.dataset.sampleMessage;

                document.getElementById('new-sender-id').value = senderId;
                document.getElementById('sample-sms').value = sampleMessage;
                registerSenderIdForm.querySelector('button[type="submit"]').textContent = 'Update Sender ID';

                const hiddenIdInput = document.createElement('input');
                hiddenIdInput.type = 'hidden';
                hiddenIdInput.name = 'id';
                hiddenIdInput.value = requestId;
                registerSenderIdForm.appendChild(hiddenIdInput);

                registerSenderIdForm.querySelector('button[type="submit"]').onclick = async (e) => {
                    e.preventDefault();
                    const formData = new FormData(registerSenderIdForm);
                    formData.append('action', 'edit');

                    try {
                        const response = await fetch('api/sms_sender_ids.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        if (result.success) {
                            alert(result.message);
                            registerSenderIdForm.reset();
                            registerSenderIdForm.querySelector('button[type="submit"]').textContent = 'Register Sender ID';
                            registerSenderIdForm.querySelector('input[name="id"]').remove();
                            fetchSenderIdRequests();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        alert('An error occurred: ' + error.message);
                    }
                };
            });
        });

        document.querySelectorAll('.delete-sender-id-request-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                if (confirm('Are you sure you want to delete this request?')) {
                    const requestId = e.currentTarget.dataset.id;
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', requestId);

                    try {
                        const response = await fetch('api/sms_sender_ids.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();
                        if (result.success) {
                            alert(result.message);
                            fetchSenderIdRequests();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        alert('An error occurred: ' + error.message);
                    }
                }
            });
        });
    }
});
