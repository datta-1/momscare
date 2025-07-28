<?php
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: blog.php');
    exit();
}

// Get blog post
$query = "SELECT * FROM blog_posts WHERE slug = :slug AND published = 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':slug', $slug);
$stmt->execute();
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: blog.php');
    exit();
}

// Get related posts
$query = "SELECT id, title, slug, excerpt FROM blog_posts WHERE published = 1 AND id != :id ORDER BY created_at DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $post['id']);
$stmt->execute();
$related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - MomCare AI Assistant</title>
    <meta name="description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
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
                    <a href="blog.php" class="text-gray-600 hover:text-indigo-600">Blog</a>
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

    <!-- Breadcrumb -->
    <div class="bg-gray-100 py-4">
        <div class="max-w-4xl mx-auto px-4">
            <nav class="text-sm">
                <a href="index.php" class="text-indigo-600 hover:text-indigo-800">Home</a>
                <span class="mx-2 text-gray-500">/</span>
                <a href="blog.php" class="text-indigo-600 hover:text-indigo-800">Blog</a>
                <span class="mx-2 text-gray-500">/</span>
                <span class="text-gray-700"><?php echo htmlspecialchars($post['title']); ?></span>
            </nav>
        </div>
    </div>

    <!-- Article -->
    <article class="max-w-4xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <?php if ($post['featured_image']): ?>
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                     class="w-full h-64 object-cover">
            <?php endif; ?>
            
            <div class="p-8">
                <header class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </h1>
                    
                    <div class="flex items-center text-gray-600 text-sm">
                        <i class="fas fa-user mr-2"></i>
                        <span class="mr-4"><?php echo htmlspecialchars($post['author']); ?></span>
                        <i class="fas fa-calendar mr-2"></i>
                        <span><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                    </div>
                </header>
                
                <div class="prose prose-lg max-w-none">
                    <?php echo $post['content']; ?>
                </div>
                
                <!-- Share buttons -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Share this article</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            <i class="fab fa-facebook-f mr-2"></i>Facebook
                        </a>
                        <a href="#" class="bg-blue-400 text-white px-4 py-2 rounded hover:bg-blue-500">
                            <i class="fab fa-twitter mr-2"></i>Twitter
                        </a>
                        <a href="#" class="bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900">
                            <i class="fab fa-linkedin-in mr-2"></i>LinkedIn
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <!-- Related Posts -->
    <?php if (!empty($related_posts)): ?>
        <section class="max-w-4xl mx-auto py-12 px-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Articles</h2>
            
            <div class="grid gap-6 md:grid-cols-3">
                <?php foreach ($related_posts as $related): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                <a href="blog_post.php?slug=<?php echo urlencode($related['slug']); ?>" 
                                   class="hover:text-indigo-600">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 mb-4">
                                <?php echo htmlspecialchars($related['excerpt']); ?>
                            </p>
                            
                            <a href="blog_post.php?slug=<?php echo urlencode($related['slug']); ?>" 
                               class="text-indigo-600 hover:text-indigo-800 font-medium">
                                Read More →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="bg-indigo-600 py-16">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-3xl font-bold text-white mb-4">Need Personalized Advice?</h2>
            <p class="text-xl text-indigo-200 mb-8">Chat with our AI assistant for 24/7 pregnancy support</p>
            
            <?php if (isLoggedIn()): ?>
                <a href="chat.php" class="bg-white text-indigo-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 transition duration-300">
                    Start Chatting Now
                </a>
            <?php else: ?>
                <a href="signup.php" class="bg-white text-indigo-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 transition duration-300">
                    Get Started Free
                </a>
            <?php endif; ?>
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
