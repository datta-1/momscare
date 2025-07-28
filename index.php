<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOMCARE – Your AI Companion for a Healthy Pregnancy 🤰🤖</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/momcare-ui.css" rel="stylesheet">
    <style>
        .hero-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff" fill-opacity="0.1" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }
        .feature-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stats-counter {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="navbar fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <div class="navbar-brand text-2xl">
                        <i class="fas fa-heart mr-3 text-pink-500"></i>MOMCARE
                    </div>
                    <div class="hidden md:block ml-8">
                        <span class="text-sm bg-gradient-to-r from-pink-500 to-purple-600 text-white px-3 py-1 rounded-full">
                            AI-Powered Pregnancy Care
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="#features" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Features</a>
                    <a href="#about" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">About</a>
                    <a href="blog.php" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Blog</a>
                    <a href="login.php" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Login</a>
                    <a href="signup.php" class="btn-primary px-6 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                        Get Started Free
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg relative min-h-screen flex items-center">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <div class="mb-6">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white bg-opacity-20 text-white border border-white border-opacity-30">
                            <i class="fas fa-sparkles mr-2"></i>
                            New: AI-Powered Health Insights
                        </span>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
                        MOMCARE
                        <span class="block text-4xl md:text-5xl bg-gradient-to-r from-pink-300 to-yellow-300 bg-clip-text text-transparent">
                            Your AI Companion for a Healthy Pregnancy
                        </span>
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-indigo-100 leading-relaxed">
                        Get personalized pregnancy care, 24/7 AI support, health monitoring, and peace of mind throughout your beautiful journey to motherhood.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <a href="signup.php" class="btn-primary text-lg px-8 py-4 rounded-full font-semibold shadow-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                            <i class="fas fa-rocket mr-3"></i>Start Your Journey Today
                        </a>
                        <a href="#features" class="inline-flex items-center px-8 py-4 border-2 border-white text-white rounded-full font-semibold hover:bg-white hover:text-indigo-600 transition-all duration-300">
                            <i class="fas fa-play mr-3"></i>Explore Features
                        </a>
                    </div>
                    <div class="flex items-center text-indigo-200">
                        <div class="flex -space-x-2 mr-4">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://images.unsplash.com/photo-1494790108755-2616b612b515?w=50&h=50&fit=crop&crop=face" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=50&h=50&fit=crop&crop=face" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=50&h=50&fit=crop&crop=face" alt="User">
                        </div>
                        <span class="text-sm">Trusted by 10,000+ expecting mothers</span>
                    </div>
                </div>
                <div class="relative">
                    <div class="floating-animation">
                        <div class="bg-white bg-opacity-10 backdrop-filter backdrop-blur-lg rounded-3xl p-8 border border-white border-opacity-20">
                            <div class="text-center text-white">
                                <div class="w-24 h-24 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-2xl">
                                    <i class="fas fa-baby text-3xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold mb-4">AI-Powered Care</h3>
                                <p class="text-indigo-200 mb-6">24/7 personalized support for every stage of your pregnancy journey</p>
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <div class="text-2xl font-bold text-pink-300">24/7</div>
                                        <div class="text-xs text-indigo-200">Support</div>
                                    </div>
                                    <div>
                                        <div class="text-2xl font-bold text-yellow-300">AI</div>
                                        <div class="text-xs text-indigo-200">Powered</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 right-20 opacity-30">
            <div class="w-32 h-32 bg-pink-300 rounded-full blur-3xl floating-animation" style="animation-delay: -2s;"></div>
        </div>
        <div class="absolute bottom-20 left-20 opacity-30">
            <div class="w-40 h-40 bg-purple-300 rounded-full blur-3xl floating-animation" style="animation-delay: -4s;"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gradient-to-br from-gray-50 to-indigo-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold gradient-text mb-6">
                    Comprehensive Pregnancy Care Features
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    MOMCARE combines cutting-edge AI technology with expert medical knowledge to provide you with the most comprehensive pregnancy care platform available.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- AI Chatbot Feature -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-robot text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">🗣 AI Chatbot Support</h3>
                    <p class="text-gray-600 mb-6">Get instant answers to your pregnancy questions 24/7. Our AI understands your concerns and provides personalized, medically-accurate responses.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Real-time pregnancy advice</li>
                        <li>• Symptom assessment</li>
                        <li>• Emergency guidance</li>
                        <li>• Multi-language support</li>
                    </ul>
                </div>

                <!-- Health Monitoring -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-heartbeat text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">💡 Health Monitoring</h3>
                    <p class="text-gray-600 mb-6">Track vital health metrics with beautiful charts and get insights into your pregnancy progress with automated health reports.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Blood pressure tracking</li>
                        <li>• Weight monitoring</li>
                        <li>• Blood sugar levels</li>
                        <li>• Heart rate analysis</li>
                    </ul>
                </div>

                <!-- Emotional Support -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-heart text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">🧘‍♀️ Emotional Support</h3>
                    <p class="text-gray-600 mb-6">Reduce stress and anxiety with AI-driven mental well-being assistance, mindfulness exercises, and emotional support.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Guided meditation sessions</li>
                        <li>• Stress reduction techniques</li>
                        <li>• Mood tracking</li>
                        <li>• Breathing exercises</li>
                    </ul>
                </div>

                <!-- Appointment Management -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-calendar-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">⏰ Smart Scheduling</h3>
                    <p class="text-gray-600 mb-6">Never miss important appointments or medication doses with intelligent reminders and scheduling assistance.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Appointment reminders</li>
                        <li>• Medication tracking</li>
                        <li>• Doctor visit scheduling</li>
                        <li>• Test result reminders</li>
                    </ul>
                </div>

                <!-- Telehealth -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-video text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">👩‍⚕️ Telehealth Integration</h3>
                    <p class="text-gray-600 mb-6">Connect with healthcare professionals easily through integrated telehealth consultations and virtual appointments.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Virtual consultations</li>
                        <li>• Specialist connections</li>
                        <li>• Secure video calls</li>
                        <li>• Digital prescriptions</li>
                    </ul>
                </div>

                <!-- Hospital Finder -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-hospital text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">🗺 Hospital Mapping</h3>
                    <p class="text-gray-600 mb-6">Locate the nearest medical centers instantly with integrated maps, ratings, and emergency contact information.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• GPS hospital location</li>
                        <li>• Emergency services</li>
                        <li>• Hospital ratings</li>
                        <li>• Maternity ward info</li>
                    </ul>
                </div>

                <!-- Educational Resources -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-book-open text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📚 Educational Hub</h3>
                    <p class="text-gray-600 mb-6">Access a comprehensive library of health-related articles, videos, and resources tailored to your pregnancy stage.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Week-by-week guides</li>
                        <li>• Nutrition advice</li>
                        <li>• Exercise videos</li>
                        <li>• Labor preparation</li>
                    </ul>
                </div>

                <!-- Document Management -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <i class="fas fa-file-medical text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">📂 Document Storage</h3>
                    <p class="text-gray-600 mb-6">Easily store and access important medical records, test results, and ultrasound images in one secure location.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• Secure cloud storage</li>
                        <li>• Easy file sharing</li>
                        <li>• Medical history tracking</li>
                        <li>• Ultrasound gallery</li>
                    </ul>
                </div>

                <!-- Emergency Assistance -->
                <div class="feature-card bg-white bg-opacity-80 p-8 rounded-2xl border-2 border-red-200">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg pulse-animation">
                        <i class="fas fa-exclamation-triangle text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">🚨 Emergency Assistance</h3>
                    <p class="text-gray-600 mb-6">Quick access to emergency healthcare services with one-tap emergency contacts and location sharing capabilities.</p>
                    <ul class="text-sm text-gray-500 space-y-2">
                        <li>• One-tap emergency call</li>
                        <li>• Location sharing</li>
                        <li>• Emergency contacts</li>
                        <li>• Critical info access</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Preview Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold gradient-text mb-6">
                    📊 Your Pregnancy Command Center
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Experience a beautiful, intuitive dashboard that puts all your pregnancy information at your fingertips.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="space-y-8">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Real-time Health Charts</h3>
                                <p class="text-gray-600">Monitor your blood pressure, weight, and blood sugar with beautiful, interactive charts that help you understand trends and patterns.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-calendar-week text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Pregnancy Progress Tracker</h3>
                                <p class="text-gray-600">See exactly where you are in your pregnancy journey with visual progress indicators and week-by-week milestone celebrations.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-pills text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Smart Medication Reminders</h3>
                                <p class="text-gray-600">Never miss a dose with intelligent medication tracking and timely reminders that adapt to your schedule.</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-lightbulb text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Personalized Tips & Insights</h3>
                                <p class="text-gray-600">Receive AI-generated health tips, nutrition advice, and wellness recommendations tailored to your current pregnancy stage.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-8 shadow-2xl">
                        <div class="bg-white rounded-2xl p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-gray-900">Your Dashboard</h3>
                                <div class="text-right">
                                    <div class="text-3xl font-bold text-indigo-600">24</div>
                                    <div class="text-sm text-gray-500">weeks pregnant</div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="bg-indigo-50 rounded-xl p-4">
                                    <div class="text-2xl font-bold text-indigo-600">120/80</div>
                                    <div class="text-sm text-gray-600">Blood Pressure</div>
                                </div>
                                <div class="bg-green-50 rounded-xl p-4">
                                    <div class="text-2xl font-bold text-green-600">+2 lbs</div>
                                    <div class="text-sm text-gray-600">Weight Gain</div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-pink-100 to-purple-100 rounded-xl p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-baby text-pink-500 text-2xl mr-3"></i>
                                    <div>
                                        <div class="font-semibold text-gray-900">Baby is developing hearing!</div>
                                        <div class="text-sm text-gray-600">Talk and sing to your baby</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-20 bg-gradient-to-br from-indigo-600 to-purple-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Trusted by Thousands of Mothers
                </h2>
                <p class="text-xl text-indigo-200 max-w-3xl mx-auto">
                    Join the growing community of expectant mothers who trust MOMCARE for their pregnancy journey.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center text-white">
                    <div class="stats-counter gradient-text mb-4" data-target="10000">10,000+</div>
                    <div class="text-xl font-semibold mb-2">Happy Mothers</div>
                    <div class="text-indigo-300">Using MOMCARE daily</div>
                </div>
                <div class="text-center text-white">
                    <div class="stats-counter gradient-text mb-4" data-target="500000">500K+</div>
                    <div class="text-xl font-semibold mb-2">AI Conversations</div>
                    <div class="text-indigo-300">Questions answered</div>
                </div>
                <div class="text-center text-white">
                    <div class="stats-counter gradient-text mb-4" data-target="50000">50K+</div>
                    <div class="text-xl font-semibold mb-2">Health Records</div>
                    <div class="text-indigo-300">Safely stored</div>
                </div>
                <div class="text-center text-white">
                    <div class="stats-counter gradient-text mb-4" data-target="99">99.9%</div>
                    <div class="text-xl font-semibold mb-2">Uptime</div>
                    <div class="text-indigo-300">Always available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-pink-50 to-purple-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-8">
                <h2 class="text-4xl md:text-5xl font-bold gradient-text mb-6">
                    Ready to Start Your Journey?
                </h2>
                <p class="text-xl text-gray-600 mb-8">
                    Join thousands of expectant mothers who trust MOMCARE for personalized, AI-powered pregnancy care. Start your free account today!
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mb-8">
                <a href="signup.php" class="btn-primary text-xl px-10 py-5 rounded-full font-bold shadow-2xl hover:shadow-3xl transform hover:-translate-y-3 transition-all duration-300">
                    <i class="fas fa-rocket mr-3"></i>Get Started Free
                </a>
                <a href="chat.php" class="inline-flex items-center px-10 py-5 border-2 border-indigo-600 text-indigo-600 rounded-full font-bold hover:bg-indigo-600 hover:text-white transition-all duration-300">
                    <i class="fas fa-comments mr-3"></i>Try AI Chat Demo
                </a>
            </div>
            
            <div class="text-center text-gray-500">
                <p class="mb-4">✨ Free forever • No credit card required • HIPAA compliant</p>
                <div class="flex justify-center items-center space-x-6">
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                        <span>Secure & Private</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-heart text-red-500 mr-2"></i>
                        <span>Made with Love</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        <span>24/7 Support</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-heart text-pink-500 text-2xl mr-3"></i>
                        <span class="text-2xl font-bold">MOMCARE</span>
                    </div>
                    <p class="text-gray-300 mb-6 text-lg">
                        Your AI companion for a healthy pregnancy journey. Providing personalized care, 24/7 support, and peace of mind for expectant mothers worldwide.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Features</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">AI Chat Support</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Health Monitoring</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Appointment Scheduling</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Emergency Assistance</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Educational Resources</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Support</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Medical Disclaimer</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">
                    © 2025 MOMCARE. All rights reserved. | Made with ❤️ for expecting mothers everywhere
                </p>
            </div>
        </div>
    </footer>

    <script src="assets/js/momcare-app.js"></script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Counter animation
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                if (target >= 1000) {
                    element.textContent = Math.floor(current / 1000) + 'K+';
                } else {
                    element.textContent = Math.floor(current) + (target === 99 ? '.9%' : '+');
                }
            }, 20);
        }

        // Intersection Observer for counter animation
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.dataset.target);
                    animateCounter(counter, target);
                    counterObserver.unobserve(counter);
                }
            });
        });

        document.querySelectorAll('.stats-counter').forEach(counter => {
            counterObserver.observe(counter);
        });

        // Feature card hover effects
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Welcome message
        setTimeout(() => {
            if (typeof MomCare !== 'undefined') {
                MomCare.showNotification('Welcome to MOMCARE! 🤰 Your AI pregnancy companion is ready to help.', 'success', 5000);
            }
        }, 2000);
    </script>
</body>
</html>
                Comprehensive Care at Your Fingertips
            </h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <i class="fas fa-comments text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">AI Chatbot</h3>
                    <p class="text-gray-600">
                        24/7 access to personalized pregnancy advice and support
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <i class="fas fa-calendar-alt text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Appointment Scheduling</h3>
                    <p class="text-gray-600">
                        Never miss important prenatal appointments with smart reminders
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <i class="fas fa-phone text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Emergency Support</h3>
                    <p class="text-gray-600">
                        Quick access to emergency contacts and urgent care information
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <i class="fas fa-book text-4xl text-indigo-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Educational Resources</h3>
                    <p class="text-gray-600">
                        Comprehensive library of pregnancy and parenting information
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-indigo-600 text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-xl font-bold">1</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Sign Up</h3>
                    <p class="text-gray-600">Create your account and provide basic pregnancy information</p>
                </div>
                <div class="text-center">
                    <div class="bg-indigo-600 text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-xl font-bold">2</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Chat & Learn</h3>
                    <p class="text-gray-600">Ask questions and get personalized advice from our AI assistant</p>
                </div>
                <div class="text-center">
                    <div class="bg-indigo-600 text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <span class="text-xl font-bold">3</span>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Stay Healthy</h3>
                    <p class="text-gray-600">Follow recommendations and track your pregnancy journey</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-indigo-600">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-3xl font-bold text-white mb-4">Ready to Start Your Journey?</h2>
            <p class="text-xl text-indigo-200 mb-8">Join thousands of expecting mothers who trust MomCare AI</p>
            <div class="space-x-4">
                <a href="signup.php" class="bg-white text-indigo-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 transition duration-300">
                    Get Started Free
                </a>
                <a href="chat.php" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-white hover:text-indigo-600 transition duration-300">
                    Try Demo Chat
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">MomCare AI</h3>
                    <p class="text-gray-400">Your trusted AI companion for a healthy pregnancy journey.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Features</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="chat.php" class="hover:text-white">AI Chatbot</a></li>
                        <li><a href="appointments.php" class="hover:text-white">Appointments</a></li>
                        <li><a href="emergency.php" class="hover:text-white">Emergency</a></li>
                        <li><a href="medicaldocuments.php" class="hover:text-white">Documents</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="blog.php" class="hover:text-white">Blog</a></li>
                        <li><a href="resources.php" class="hover:text-white">Resources</a></li>
                        <li><a href="#" class="hover:text-white">FAQ</a></li>
                        <li><a href="#" class="hover:text-white">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i>support@momcare.ai</li>
                        <li><i class="fas fa-phone mr-2"></i>1-800-MOM-CARE</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>Available 24/7</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 MomCare AI Assistant. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
