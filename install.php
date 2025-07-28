<?php
/**
 * Simple installation script for MomCare AI Assistant
 * Run this file once to set up the database and initial configuration
 */

// Check if already installed
if (file_exists('config/installed.lock')) {
    die('Installation already completed. Delete config/installed.lock to reinstall.');
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 1) {
        // Database configuration step
        $host = $_POST['host'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $dbname = $_POST['dbname'];
        
        // Test database connection
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
            $pdo->exec("USE `$dbname`");
            
            // Read and execute schema
            $schema = file_get_contents('schema.sql');
            $statements = explode(';', $schema);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Update database configuration
            $config_content = "<?php
class Database {
    private \$host = '$host';
    private \$dbname = '$dbname';
    private \$username = '$username';
    private \$password = '$password';
    private \$conn;

    public function connect() {
        \$this->conn = null;
        
        try {
            \$this->conn = new PDO(
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->dbname,
                \$this->username,
                \$this->password
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException \$e) {
            echo \"Connection error: \" . \$e->getMessage();
        }
        
        return \$this->conn;
    }
}
?>";
            
            file_put_contents('config/database.php', $config_content);
            
            $success = 'Database setup completed successfully!';
            $step = 2;
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } elseif ($step == 2) {
        // Admin user creation
        require_once 'config/database.php';
        require_once 'includes/classes.php';
        
        $database = new Database();
        $db = $database->connect();
        $user = new User($db);
        
        $admin_name = $_POST['admin_name'];
        $admin_email = $_POST['admin_email'];
        $admin_password = $_POST['admin_password'];
        
        if ($user->create($admin_name, $admin_email, '', $admin_password)) {
            // Create installation lock file
            file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
            
            $success = 'Installation completed! You can now log in with your admin account.';
            $step = 3;
        } else {
            $error = 'Failed to create admin user.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MomCare AI Assistant - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">MomCare AI Installation</h2>
                <p class="mt-2 text-gray-600">Step <?php echo $step; ?> of 3</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <!-- Database Configuration -->
                <form method="POST" class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900">Database Configuration</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Host</label>
                        <input type="text" name="host" value="localhost" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Database Name</label>
                        <input type="text" name="dbname" value="momcare_ai" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" value="root" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                        Setup Database
                    </button>
                </form>
                
            <?php elseif ($step == 2): ?>
                <!-- Admin User Creation -->
                <form method="POST" class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900">Create Admin User</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="admin_name" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="admin_email" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="admin_password" required minlength="6"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                        Create Admin User
                    </button>
                </form>
                
            <?php else: ?>
                <!-- Installation Complete -->
                <div class="text-center space-y-4">
                    <div class="text-green-600 text-6xl">✓</div>
                    <h3 class="text-xl font-semibold text-gray-900">Installation Complete!</h3>
                    <p class="text-gray-600">Your MomCare AI Assistant is now ready to use.</p>
                    
                    <div class="space-y-2">
                        <a href="index.php" class="block w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                            Go to Homepage
                        </a>
                        <a href="login.php" class="block w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700">
                            Login as Admin
                        </a>
                    </div>
                    
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <p class="text-sm text-yellow-800">
                            <strong>Important:</strong> For security, please delete this install.php file after installation.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
