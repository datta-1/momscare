<?php
require_once 'includes/functions.php';

// Get blog posts
$query = "SELECT id, title, slug, excerpt, featured_image, created_at FROM blog_posts WHERE published = 1 ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - MomCare AI Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-indigo-600">MomCare AI</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-indigo-600">Home</a>
                    <a href="blog.php" class="text-indigo-600 font-semibold">Blog</a>
                    <a href="resources.php" class="text-gray-600 hover:text-indigo-600">Resources</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                        <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-indigo-600 py-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold text-white mb-4">Pregnancy & Parenting Blog</h1>
            <p class="text-xl text-indigo-200">Expert advice and insights for your journey</p>
        </div>
    </div>

    <!-- Blog Posts -->
    <div class="max-w-7xl mx-auto py-12 px-4">
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($blog_posts as $post): ?>
                <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                    <?php if ($post['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                             class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center">
                            <i class="fas fa-baby text-4xl text-white"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">
                            <a href="blog_post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                               class="hover:text-indigo-600">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h2>
                        
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                            </span>
                            
                            <a href="blog_post.php?slug=<?php echo urlencode($post['slug']); ?>" 
                               class="text-indigo-600 hover:text-indigo-800 font-medium">
                                Read More →
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($blog_posts)): ?>
            <div class="text-center py-12">
                <i class="fas fa-blog text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-500 mb-2">No blog posts yet</h3>
                <p class="text-gray-400">Check back soon for new content!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Newsletter Signup -->
    <div class="bg-gray-100 py-16">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Stay Updated</h2>
            <p class="text-gray-600 mb-8">Get the latest pregnancy tips and advice delivered to your inbox</p>
            
            <form class="max-w-md mx-auto flex gap-4">
                <input type="email" 
                       placeholder="Enter your email" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" 
                        class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 font-medium">
                    Subscribe
                </button>
            </form>
        </div>
    </div>

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
