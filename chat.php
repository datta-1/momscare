<?php
require_once 'includes/functions.php';
requireAuth();

$current_user = getCurrentUser();
if (!$current_user) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = sanitizeInput($_POST['message']);
    
    if (!empty($message)) {
        // Save user message
        $chatMessage->save($current_user['user_id'], $message, 'user');
        
        // Get AI response
        $ai_response = getAIResponse($message, ['user_id' => $current_user['user_id']]);
        
        // Save AI response
        $chatMessage->save($current_user['user_id'], $ai_response, 'bot');
        
        $success = 'Message sent successfully!';
    }
}

// Get chat history
$messages = $chatMessage->getHistory($current_user['user_id'], 50);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat - MOMCARE Assistant 🤖</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/momcare-ui.css" rel="stylesheet">
    <style>
        .chat-container {
            height: calc(100vh - 200px);
        }
        .messages-container {
            height: calc(100% - 120px);
        }
        .typing-indicator {
            animation: fadeInUp 0.3s ease;
        }
        .typing-dots span {
            animation: typing 1.4s infinite ease-in-out;
        }
        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }
        .quick-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .quick-action-btn {
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            border: 1px solid rgba(99, 102, 241, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="navbar-brand">
                        <i class="fas fa-heart mr-2"></i>MOMCARE
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="dashboard.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                    </a>
                    <a href="appointments.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-calendar-alt mr-1"></i>Appointments
                    </a>
                    <a href="health-metrics.php" class="text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-chart-line mr-1"></i>Health
                    </a>
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto py-6 px-4">
        <div class="chat-container">
            <!-- Enhanced Chat Header -->
            <div class="chat-header rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="relative">
                            <i class="fas fa-robot text-3xl mr-4"></i>
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold">MOMCARE AI Assistant</h2>
                            <p class="text-indigo-200 text-sm">Your 24/7 pregnancy companion • Always here to help</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-indigo-200 text-xs">Powered by AI</div>
                        <div class="text-indigo-100 text-sm font-medium">Online</div>
                    </div>
                </div>
            </div>
            
            <!-- Chat Messages Container -->
            <div class="chat-messages" id="messagesContainer">
                <?php if (empty($messages)): ?>
                    <div class="message bot fade-in-up">
                        <div class="message-bubble bot">
                            <div class="flex items-start">
                                <i class="fas fa-heart text-pink-500 mr-2 mt-1"></i>
                                <div>
                                    <p>Hello! I'm your AI pregnancy care assistant. 🤰✨</p>
                                    <p class="mt-2">I'm here to help you with:</p>
                                    <ul class="mt-2 text-sm space-y-1">
                                        <li>• Pregnancy health tips and advice</li>
                                        <li>• Appointment reminders and scheduling</li>
                                        <li>• Medication guidance</li>
                                        <li>• Emotional support and mindfulness</li>
                                        <li>• Emergency assistance when needed</li>
                                    </ul>
                                    <p class="mt-3 text-sm">How can I help you today?</p>
                                </div>
                            </div>
                            <p class="message-time">Just now</p>
                        </div>
                    </div>
                    
                    <!-- Quick Action Buttons -->
                    <div class="quick-actions mt-4">
                        <button class="quick-action-btn" onclick="sendQuickMessage('How is my baby developing this week?')">
                            <i class="fas fa-baby mr-1"></i>Baby Development
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('What should I eat during pregnancy?')">
                            <i class="fas fa-apple-alt mr-1"></i>Nutrition Tips
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('I am feeling anxious about my pregnancy')">
                            <i class="fas fa-heart mr-1"></i>Emotional Support
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('What exercises are safe for me?')">
                            <i class="fas fa-dumbbell mr-1"></i>Exercise Advice
                        </button>
                        <button class="quick-action-btn" onclick="sendQuickMessage('Help me prepare for labor')">
                            <i class="fas fa-hospital mr-1"></i>Labor Prep
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender']; ?> fade-in-up">
                            <div class="message-bubble <?php echo $msg['sender']; ?>">
                                <?php if ($msg['sender'] === 'bot'): ?>
                                    <div class="flex items-start">
                                        <i class="fas fa-robot text-indigo-300 mr-2 mt-1 text-sm"></i>
                                        <div class="flex-1">
                                            <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                <?php endif; ?>
                                <p class="message-time"><?php echo timeAgo($msg['timestamp']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Chat Input Area -->
            <div class="bg-white border-t border-gray-200 p-4 rounded-b-xl">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success mb-3"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error mb-3"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form id="chatForm" class="flex items-end space-x-3">
                    <div class="flex-1">
                        <div class="relative">
                            <input 
                                type="text" 
                                id="chatInput" 
                                name="message" 
                                placeholder="Type your message... (e.g., 'I'm feeling tired today' or 'When is my next appointment?')"
                                class="form-input pr-12 resize-none"
                                autocomplete="off"
                                required
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-1">
                                <button type="button" onclick="toggleVoiceInput()" class="text-gray-400 hover:text-indigo-600 p-1">
                                    <i class="fas fa-microphone" id="voiceIcon"></i>
                                </button>
                                <button type="button" onclick="showEmojiPicker()" class="text-gray-400 hover:text-indigo-600 p-1">
                                    <i class="fas fa-smile"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Quick Suggestions -->
                        <div class="quick-actions mt-2" id="quickSuggestions" style="display: none;">
                            <button type="button" class="quick-action-btn" onclick="insertSuggestion('How are you feeling today?')">
                                How are you feeling?
                            </button>
                            <button type="button" class="quick-action-btn" onclick="insertSuggestion('I have a question about my symptoms')">
                                Ask about symptoms
                            </button>
                            <button type="button" class="quick-action-btn" onclick="insertSuggestion('Remind me about my medication')">
                                Medication reminder
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="button" onclick="showQuickActions()" class="p-3 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="submit" class="btn-primary px-6 py-3 rounded-lg">
                            <i class="fas fa-paper-plane mr-2"></i>Send
                        </button>
                    </div>
                </form>
                
                <!-- Quick Actions Menu -->
                <div id="quickActionsMenu" class="hidden mt-3 p-3 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <button onclick="sendQuickMessage('Schedule an appointment')" class="quick-action-btn">
                            <i class="fas fa-calendar-plus mr-1"></i>Schedule
                        </button>
                        <button onclick="sendQuickMessage('Log my blood pressure')" class="quick-action-btn">
                            <i class="fas fa-heartbeat mr-1"></i>Log Health
                        </button>
                        <button onclick="sendQuickMessage('Find nearby hospital')" class="quick-action-btn">
                            <i class="fas fa-hospital mr-1"></i>Find Hospital
                        </button>
                        <button onclick="sendQuickMessage('I need emergency help')" class="quick-action-btn text-red-600 border-red-200 bg-red-50">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Emergency
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Voice Input Modal -->
    <div id="voiceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 text-center">
                <div class="mb-4">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-microphone text-2xl text-red-600" id="micIcon"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Voice Input</h3>
                    <p class="text-gray-600 text-sm mt-2" id="voiceStatus">Click the microphone to start speaking</p>
                </div>
                
                <div class="flex justify-center space-x-3">
                    <button onclick="startVoiceInput()" id="startVoiceBtn" class="btn-primary">
                        <i class="fas fa-microphone mr-2"></i>Start Recording
                    </button>
                    <button onclick="stopVoiceInput()" id="stopVoiceBtn" class="btn-secondary hidden">
                        <i class="fas fa-stop mr-2"></i>Stop Recording
                    </button>
                    <button onclick="closeVoiceModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Button -->
    <button class="emergency-btn" onclick="MomCare.handleEmergency()">
        <i class="fas fa-exclamation-triangle mr-2"></i>Emergency
    </button>

    <script src="assets/js/momcare-app.js"></script>
    <script>
        let recognition;
        let isRecording = false;

        // Enhanced chat functionality
        function sendQuickMessage(message) {
            document.getElementById('chatInput').value = message;
            document.getElementById('chatForm').dispatchEvent(new Event('submit'));
        }

        function insertSuggestion(suggestion) {
            const input = document.getElementById('chatInput');
            input.value = suggestion;
            input.focus();
            hideQuickSuggestions();
        }

        function showQuickActions() {
            const menu = document.getElementById('quickActionsMenu');
            menu.classList.toggle('hidden');
        }

        function showQuickSuggestions() {
            document.getElementById('quickSuggestions').style.display = 'flex';
        }

        function hideQuickSuggestions() {
            document.getElementById('quickSuggestions').style.display = 'none';
        }

        // Voice input functionality
        function toggleVoiceInput() {
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                document.getElementById('voiceModal').classList.remove('hidden');
            } else {
                MomCare.showNotification('Voice input is not supported in your browser', 'error');
            }
        }

        function startVoiceInput() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-US';

            recognition.onstart = function() {
                isRecording = true;
                document.getElementById('voiceStatus').textContent = 'Listening... Speak now';
                document.getElementById('micIcon').classList.add('text-red-600');
                document.getElementById('startVoiceBtn').classList.add('hidden');
                document.getElementById('stopVoiceBtn').classList.remove('hidden');
            };

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                document.getElementById('chatInput').value = transcript;
                closeVoiceModal();
                MomCare.showNotification('Voice input captured successfully!', 'success');
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                MomCare.showNotification('Voice input error: ' + event.error, 'error');
                closeVoiceModal();
            };

            recognition.onend = function() {
                isRecording = false;
                document.getElementById('micIcon').classList.remove('text-red-600');
                document.getElementById('startVoiceBtn').classList.remove('hidden');
                document.getElementById('stopVoiceBtn').classList.add('hidden');
            };

            recognition.start();
        }

        function stopVoiceInput() {
            if (recognition && isRecording) {
                recognition.stop();
            }
        }

        function closeVoiceModal() {
            document.getElementById('voiceModal').classList.add('hidden');
            if (recognition && isRecording) {
                recognition.stop();
            }
        }

        function showEmojiPicker() {
            // Simple emoji insertion
            const emojis = ['😊', '😢', '😰', '🤱', '👶', '💗', '🙏', '😴', '🤗', '👍'];
            const emoji = emojis[Math.floor(Math.random() * emojis.length)];
            const input = document.getElementById('chatInput');
            input.value += emoji;
            input.focus();
        }

        // Enhanced chat input handling
        document.getElementById('chatInput').addEventListener('focus', showQuickSuggestions);
        document.getElementById('chatInput').addEventListener('blur', () => {
            setTimeout(hideQuickSuggestions, 150); // Delay to allow clicking suggestions
        });

        // Auto-resize textarea and handle enter key
        document.getElementById('chatInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').dispatchEvent(new Event('submit'));
            }
        });

        // Show typing indicator when user is typing
        let typingTimer;
        document.getElementById('chatInput').addEventListener('input', function() {
            clearTimeout(typingTimer);
            // Could show "user is typing" indicator here
            typingTimer = setTimeout(() => {
                // Clear typing indicator
            }, 1000);
        });

        // Initialize enhanced features
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus chat input
            document.getElementById('chatInput').focus();
            
            // Add welcome notification
            setTimeout(() => {
                MomCare.showNotification('💬 Pro tip: Try voice input or use quick actions for faster chatting!', 'info', 6000);
            }, 2000);
        });
    </script>
</body>
</html>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Message Input -->
            <div class="border-t bg-gray-50 p-4 rounded-b-lg">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="flex space-x-2">
                    <input type="text" 
                           name="message" 
                           placeholder="Type your message here..." 
                           required
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           autocomplete="off">
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-6 py-2 rounded-full hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
                
                <div class="mt-3 flex flex-wrap gap-2">
                    <button onclick="sendQuickMessage('Hello, I have some questions about my pregnancy')" 
                            class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-300">
                        Ask about pregnancy
                    </button>
                    <button onclick="sendQuickMessage('What should I eat during pregnancy?')" 
                            class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-300">
                        Nutrition advice
                    </button>
                    <button onclick="sendQuickMessage('I'm experiencing some symptoms')" 
                            class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-300">
                        Report symptoms
                    </button>
                    <button onclick="sendQuickMessage('Help me prepare for my appointment')" 
                            class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-300">
                        Appointment prep
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="appointments.php" class="bg-white p-4 rounded-lg shadow hover:shadow-md transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-2xl text-green-600 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">Appointments</h3>
                        <p class="text-sm text-gray-600">Schedule checkups</p>
                    </div>
                </div>
            </a>
            
            <a href="medicaldocuments.php" class="bg-white p-4 rounded-lg shadow hover:shadow-md transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-file-medical text-2xl text-blue-600 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">Documents</h3>
                        <p class="text-sm text-gray-600">Medical records</p>
                    </div>
                </div>
            </a>
            
            <a href="emergency.php" class="bg-white p-4 rounded-lg shadow hover:shadow-md transition duration-300">
                <div class="flex items-center">
                    <i class="fas fa-phone text-2xl text-red-600 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">Emergency</h3>
                        <p class="text-sm text-gray-600">Quick contacts</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <script>
        // Auto scroll to bottom
        function scrollToBottom() {
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        // Call on page load
        window.onload = function() {
            scrollToBottom();
        };
        
        // Quick message function
        function sendQuickMessage(message) {
            const form = document.querySelector('form');
            const input = form.querySelector('input[name="message"]');
            input.value = message;
            form.submit();
        }
        
        // Auto-refresh messages every 30 seconds (optional)
        setInterval(function() {
            // You can implement auto-refresh here if needed
            // location.reload();
        }, 30000);
    </script>
</body>
</html>
