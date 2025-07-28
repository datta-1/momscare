/**
 * MOMCARE - Interactive UI JavaScript
 * Handles all interactive features and animations
 */

// Global application state
const MomCare = {
    user: null,
    currentWeek: 0,
    charts: {},
    notifications: [],
    
    // Initialize the application
    init: function() {
        this.loadUserData();
        this.initializeCharts();
        this.setupEventListeners();
        this.startNotificationCheck();
        this.initializeAnimations();
    },
    
    // Load user data from the server
    loadUserData: function() {
        fetch('/api/user-data.php')
            .then(response => response.json())
            .then(data => {
                this.user = data.user;
                this.currentWeek = data.currentWeek;
                this.updateWeekIndicator();
                this.loadHealthMetrics();
            })
            .catch(error => console.error('Error loading user data:', error));
    },
    
    // Update pregnancy week indicator
    updateWeekIndicator: function() {
        const weekElement = document.querySelector('.week-number');
        const weekText = document.querySelector('.week-text');
        
        if (weekElement && this.currentWeek > 0) {
            weekElement.textContent = this.currentWeek;
            weekText.textContent = `weeks pregnant`;
            
            // Add celebration animation for milestone weeks
            if ([12, 20, 28, 36, 40].includes(this.currentWeek)) {
                this.celebrateWeekMilestone();
            }
        }
    },
    
    // Celebrate week milestones with animation
    celebrateWeekMilestone: function() {
        const indicator = document.querySelector('.week-indicator');
        if (indicator) {
            indicator.classList.add('milestone-celebration');
            setTimeout(() => {
                indicator.classList.remove('milestone-celebration');
            }, 2000);
        }
    },
    
    // Initialize health metric charts
    initializeCharts: function() {
        this.initBloodPressureChart();
        this.initBloodSugarChart();
        this.initWeightChart();
        this.initHeartRateChart();
    },
    
    // Blood Pressure Chart
    initBloodPressureChart: function() {
        const ctx = document.getElementById('bloodPressureChart');
        if (!ctx) return;
        
        this.charts.bloodPressure = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Systolic',
                    data: [],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Diastolic',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Blood Pressure Trends'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 60,
                        max: 180
                    }
                }
            }
        });
    },
    
    // Blood Sugar Chart
    initBloodSugarChart: function() {
        const ctx = document.getElementById('bloodSugarChart');
        if (!ctx) return;
        
        this.charts.bloodSugar = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Blood Sugar (mg/dL)',
                    data: [],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Blood Sugar Levels'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 70,
                        max: 200
                    }
                }
            }
        });
    },
    
    // Weight Chart
    initWeightChart: function() {
        const ctx = document.getElementById('weightChart');
        if (!ctx) return;
        
        this.charts.weight = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Weight (lbs)',
                    data: [],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Weight Progress'
                    }
                }
            }
        });
    },
    
    // Heart Rate Chart
    initHeartRateChart: function() {
        const ctx = document.getElementById('heartRateChart');
        if (!ctx) return;
        
        this.charts.heartRate = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Heart Rate (BPM)',
                    data: [],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Heart Rate Monitoring'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 60,
                        max: 120
                    }
                }
            }
        });
    },
    
    // Load health metrics data
    loadHealthMetrics: function() {
        fetch('/api/health-metrics.php')
            .then(response => response.json())
            .then(data => {
                this.updateChartData(data);
                this.updateHealthStats(data);
            })
            .catch(error => console.error('Error loading health metrics:', error));
    },
    
    // Update chart data
    updateChartData: function(data) {
        // Update Blood Pressure Chart
        if (this.charts.bloodPressure && data.bloodPressure) {
            this.charts.bloodPressure.data.labels = data.bloodPressure.dates;
            this.charts.bloodPressure.data.datasets[0].data = data.bloodPressure.systolic;
            this.charts.bloodPressure.data.datasets[1].data = data.bloodPressure.diastolic;
            this.charts.bloodPressure.update();
        }
        
        // Update Blood Sugar Chart
        if (this.charts.bloodSugar && data.bloodSugar) {
            this.charts.bloodSugar.data.labels = data.bloodSugar.dates;
            this.charts.bloodSugar.data.datasets[0].data = data.bloodSugar.values;
            this.charts.bloodSugar.update();
        }
        
        // Update Weight Chart
        if (this.charts.weight && data.weight) {
            this.charts.weight.data.labels = data.weight.dates;
            this.charts.weight.data.datasets[0].data = data.weight.values;
            this.charts.weight.update();
        }
        
        // Update Heart Rate Chart
        if (this.charts.heartRate && data.heartRate) {
            this.charts.heartRate.data.labels = data.heartRate.dates;
            this.charts.heartRate.data.datasets[0].data = data.heartRate.values;
            this.charts.heartRate.update();
        }
    },
    
    // Update health statistics cards
    updateHealthStats: function(data) {
        const stats = {
            lastBP: data.lastBloodPressure || 'N/A',
            lastSugar: data.lastBloodSugar || 'N/A',
            currentWeight: data.currentWeight || 'N/A',
            lastHeartRate: data.lastHeartRate || 'N/A'
        };
        
        // Update stat cards with animation
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                this.animateNumberChange(element, stats[key]);
            }
        });
    },
    
    // Animate number changes
    animateNumberChange: function(element, newValue) {
        element.style.transform = 'scale(1.1)';
        element.style.color = '#10b981';
        
        setTimeout(() => {
            element.textContent = newValue;
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 200);
    },
    
    // Setup event listeners
    setupEventListeners: function() {
        // Health metric form submissions
        this.setupHealthMetricForms();
        
        // Medication reminder interactions
        this.setupMedicationReminders();
        
        // Chat interface
        this.setupChatInterface();
        
        // Emergency button
        this.setupEmergencyButton();
        
        // Navigation interactions
        this.setupNavigationEffects();
        
        // Form validations
        this.setupFormValidations();
    },
    
    // Setup health metric forms
    setupHealthMetricForms: function() {
        const forms = document.querySelectorAll('.health-metric-form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitHealthMetric(form);
            });
        });
    },
    
    // Submit health metric
    submitHealthMetric: function(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Show loading state
        submitBtn.innerHTML = '<div class="spinner"></div> Saving...';
        submitBtn.disabled = true;
        
        fetch('/api/save-health-metric.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Health metric saved successfully!', 'success');
                form.reset();
                this.loadHealthMetrics(); // Refresh charts
            } else {
                this.showNotification('Error saving health metric', 'error');
            }
        })
        .catch(error => {
            this.showNotification('Error saving health metric', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            submitBtn.innerHTML = 'Save';
            submitBtn.disabled = false;
        });
    },
    
    // Setup medication reminders
    setupMedicationReminders: function() {
        const reminderButtons = document.querySelectorAll('.medication-taken-btn');
        reminderButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.markMedicationTaken(e.target.dataset.reminderId);
            });
        });
    },
    
    // Mark medication as taken
    markMedicationTaken: function(reminderId) {
        fetch('/api/mark-medication-taken.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reminderId: reminderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const reminderCard = document.querySelector(`[data-reminder-id="${reminderId}"]`);
                if (reminderCard) {
                    reminderCard.classList.add('taken');
                    reminderCard.classList.remove('due');
                }
                this.showNotification('Medication marked as taken!', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    },
    
    // Setup chat interface
    setupChatInterface: function() {
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const messagesContainer = document.getElementById('messagesContainer');
        
        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendChatMessage(chatInput.value.trim());
                chatInput.value = '';
            });
        }
        
        // Auto-scroll chat to bottom
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    },
    
    // Send chat message
    sendChatMessage: function(message) {
        if (!message) return;
        
        const messagesContainer = document.getElementById('messagesContainer');
        
        // Add user message to UI
        this.addMessageToChat(message, 'user');
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Send to server
        fetch('/api/chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            this.hideTypingIndicator();
            if (data.response) {
                this.addMessageToChat(data.response, 'bot');
            }
        })
        .catch(error => {
            this.hideTypingIndicator();
            console.error('Error:', error);
            this.addMessageToChat('Sorry, I\'m having trouble responding right now. Please try again.', 'bot');
        });
    },
    
    // Add message to chat
    addMessageToChat: function(message, sender) {
        const messagesContainer = document.getElementById('messagesContainer');
        if (!messagesContainer) return;
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        const bubbleDiv = document.createElement('div');
        bubbleDiv.className = `message-bubble ${sender}`;
        bubbleDiv.innerHTML = `
            <p>${message}</p>
            <p class="message-time">Just now</p>
        `;
        
        messageDiv.appendChild(bubbleDiv);
        messagesContainer.appendChild(messageDiv);
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Add animation
        messageDiv.classList.add('fade-in-up');
    },
    
    // Show typing indicator
    showTypingIndicator: function() {
        const messagesContainer = document.getElementById('messagesContainer');
        if (!messagesContainer) return;
        
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-bubble bot">
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    },
    
    // Hide typing indicator
    hideTypingIndicator: function() {
        const typingIndicator = document.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    },
    
    // Setup emergency button
    setupEmergencyButton: function() {
        const emergencyBtn = document.querySelector('.emergency-btn');
        if (emergencyBtn) {
            emergencyBtn.addEventListener('click', () => {
                this.handleEmergency();
            });
        }
    },
    
    // Handle emergency situation
    handleEmergency: function() {
        const confirmEmergency = confirm('Are you experiencing a medical emergency? This will contact emergency services and your emergency contacts.');
        
        if (confirmEmergency) {
            // Log emergency action
            fetch('/api/emergency-alert.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ emergency: true })
            });
            
            // Show emergency contacts
            this.showEmergencyContacts();
            
            // Call emergency services (in a real app, this would be handled more carefully)
            if (confirm('Would you like to call 911 now?')) {
                window.open('tel:911', '_self');
            }
        }
    },
    
    // Show emergency contacts modal
    showEmergencyContacts: function() {
        // This would show a modal with emergency contacts
        // For now, we'll just show an alert
        alert('Emergency contacts notified. Calling emergency services if needed.');
    },
    
    // Setup navigation effects
    setupNavigationEffects: function() {
        const navLinks = document.querySelectorAll('nav a');
        navLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    },
    
    // Setup form validations
    setupFormValidations: function() {
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateInput(input);
            });
            
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    this.validateInput(input);
                }
            });
        });
    },
    
    // Validate individual input
    validateInput: function(input) {
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        // Required field validation
        if (input.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        
        // Email validation
        if (input.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        
        // Phone validation
        if (input.type === 'tel' && value) {
            const phoneRegex = /^\(\d{3}\)\s\d{3}-\d{4}$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number (xxx) xxx-xxxx';
            }
        }
        
        // Number validation
        if (input.type === 'number' && value) {
            const min = parseFloat(input.getAttribute('min'));
            const max = parseFloat(input.getAttribute('max'));
            const numValue = parseFloat(value);
            
            if (!isNaN(min) && numValue < min) {
                isValid = false;
                errorMessage = `Value must be at least ${min}`;
            }
            
            if (!isNaN(max) && numValue > max) {
                isValid = false;
                errorMessage = `Value must be no more than ${max}`;
            }
        }
        
        // Update UI based on validation
        if (isValid) {
            input.classList.remove('error');
            this.removeErrorMessage(input);
        } else {
            input.classList.add('error');
            this.showErrorMessage(input, errorMessage);
        }
        
        return isValid;
    },
    
    // Show error message for input
    showErrorMessage: function(input, message) {
        this.removeErrorMessage(input); // Remove existing error message
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.color = '#ef4444';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        
        input.parentNode.appendChild(errorDiv);
    },
    
    // Remove error message for input
    removeErrorMessage: function(input) {
        const existingError = input.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
    },
    
    // Initialize animations
    initializeAnimations: function() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);
        
        // Observe all animatable elements
        const animatableElements = document.querySelectorAll('.stat-card, .chart-container, .appointment-card, .medication-card');
        animatableElements.forEach(el => observer.observe(el));
    },
    
    // Start notification checking
    startNotificationCheck: function() {
        // Check for notifications every 30 seconds
        setInterval(() => {
            this.checkNotifications();
        }, 30000);
        
        // Check immediately
        this.checkNotifications();
    },
    
    // Check for new notifications
    checkNotifications: function() {
        fetch('/api/notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        this.showNotification(notification.message, notification.type);
                    });
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
    },
    
    // Show notification
    showNotification: function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        notification.style.animation = 'slideInRight 0.3s ease';
        
        document.body.appendChild(notification);
        
        // Auto remove after duration
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, duration);
        
        // Click to dismiss
        notification.addEventListener('click', () => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        });
    },
    
    // Utility function to format dates
    formatDate: function(date) {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(new Date(date));
    },
    
    // Utility function to format time
    formatTime: function(time) {
        return new Intl.DateTimeFormat('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).format(new Date(`2000-01-01 ${time}`));
    }
};

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    MomCare.init();
});

// Additional CSS for animations
const additionalStyles = `
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .typing-dots {
        display: flex;
        gap: 4px;
        align-items: center;
    }
    
    .typing-dots span {
        width: 8px;
        height: 8px;
        background: #6b7280;
        border-radius: 50%;
        animation: typing 1.4s infinite ease-in-out;
    }
    
    .typing-dots span:nth-child(1) {
        animation-delay: -0.32s;
    }
    
    .typing-dots span:nth-child(2) {
        animation-delay: -0.16s;
    }
    
    @keyframes typing {
        0%, 80%, 100% {
            transform: scale(0);
            opacity: 0.5;
        }
        40% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .milestone-celebration {
        animation: celebrate 2s ease-in-out;
    }
    
    @keyframes celebrate {
        0%, 100% {
            transform: scale(1);
        }
        25% {
            transform: scale(1.05) rotate(1deg);
        }
        50% {
            transform: scale(1.1) rotate(-1deg);
        }
        75% {
            transform: scale(1.05) rotate(1deg);
        }
    }
    
    .notification {
        cursor: pointer;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
`;

// Inject additional styles
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);
