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