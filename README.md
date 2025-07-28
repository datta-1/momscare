# MomCare AI Assistant - PHP/MySQL Version

A comprehensive pregnancy care web application built with PHP and MySQL, converted from the original Next.js/React version.

## Features

- **AI Chatbot**: 24/7 pregnancy advice and support
- **User Authentication**: Secure login and registration system
- **Dashboard**: Personal pregnancy tracking dashboard
- **Appointment Management**: Schedule and track prenatal appointments
- **Medical Documents**: Upload and store medical records
- **Blog System**: Educational content about pregnancy and parenting
- **Emergency Contacts**: Quick access to emergency information
- **Resources Library**: Comprehensive pregnancy resources

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PDO PHP extension enabled

## Installation

1. **Clone/Download the project**
   ```
   Place the php_version folder in your web server directory
   ```

2. **Database Setup**
   - Create a MySQL database named `momcare_ai`
   - Import the schema: `mysql -u root -p momcare_ai < schema.sql`
   - Or run the SQL commands in `schema.sql` manually

3. **Configuration**
   - Edit `config/database.php` to match your database settings:
     ```php
     private $host = 'localhost';        // Your MySQL host
     private $dbname = 'momcare_ai';     // Your database name
     private $username = 'root';         // Your MySQL username
     private $password = '';             // Your MySQL password
     ```

4. **File Permissions**
   - Ensure the `uploads/` directory is writable:
     ```
     chmod 755 uploads/
     ```

5. **Web Server Setup**
   - Point your web server to the `php_version` directory
   - Ensure `.htaccess` is supported (for Apache)

## File Structure

```
php_version/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── classes.php           # Core PHP classes
│   └── functions.php         # Helper functions
├── uploads/                  # File upload directory
├── assets/                   # Static assets
├── index.php                 # Homepage
├── login.php                 # User login
├── signup.php                # User registration
├── dashboard.php             # User dashboard
├── chat.php                  # AI chatbot interface
├── blog.php                  # Blog listing
├── blog_post.php             # Individual blog post
├── logout.php                # Logout functionality
└── schema.sql                # Database schema
```

## Core Components

### Database Classes

- **User**: Handles user registration, login, and profile management
- **Session**: Manages user sessions and authentication
- **ChatMessage**: Stores and retrieves chat conversations

### Main Features

1. **Authentication System**
   - Secure password hashing with PHP's `password_hash()`
   - Session-based authentication with database storage
   - CSRF protection for forms

2. **AI Chat System**
   - Mock AI responses (replace with actual AI API integration)
   - Chat history storage and retrieval
   - Real-time conversation interface

3. **Blog System**
   - Dynamic blog post management
   - SEO-friendly URLs with slugs
   - Related posts functionality

4. **File Upload System**
   - Secure file upload for medical documents
   - File type and size validation
   - Database tracking of uploaded files

## Customization

### Integrating Real AI
Replace the mock AI function in `includes/functions.php`:

```php
function getAIResponse($message, $userContext = []) {
    // Replace with actual AI API call
    // Example: OpenAI, Google Gemini, etc.
    
    $api_response = callAIAPI($message, $userContext);
    return $api_response;
}
```

### Adding New Features
1. Create new PHP files for additional pages
2. Add database tables in `schema.sql` if needed
3. Update navigation in existing files
4. Add corresponding CSS/JavaScript as needed

### Styling
The application uses Tailwind CSS via CDN. To customize:
1. Replace CDN link with local Tailwind build
2. Modify CSS classes in PHP templates
3. Add custom CSS in a separate stylesheet

## Security Features

- Password hashing with PHP's built-in functions
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- CSRF token validation
- File upload security validation
- Session management with database storage

## Development Notes

### Database Schema
The application includes these main tables:
- `users`: User accounts and profiles
- `user_sessions`: Session management
- `chat_messages`: Chat conversation history
- `medical_documents`: File upload tracking
- `appointments`: Appointment scheduling
- `blog_posts`: Blog content management
- `emergency_contacts`: Emergency contact information
- `resources`: Educational resources

### API Integration
To integrate with real AI services:
1. Sign up for an AI service (OpenAI, Google, etc.)
2. Get API credentials
3. Replace the mock `getAIResponse()` function
4. Add proper error handling and rate limiting

## Deployment

### Production Checklist
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Configure error logging
- [ ] Set up regular database backups
- [ ] Configure email system for notifications
- [ ] Add proper session security settings
- [ ] Enable PHP OPcache for performance

### Hosting Requirements
- PHP 7.4+
- MySQL 5.7+
- At least 256MB RAM
- 1GB disk space
- SSL certificate recommended

## Support

For issues or questions:
1. Check the database connection in `config/database.php`
2. Verify file permissions on the `uploads/` directory
3. Check PHP error logs for specific errors
4. Ensure all required PHP extensions are installed

## License

This project maintains the same license as the original MomCare AI Assistant project.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Changelog

### v1.0.0
- Initial PHP/MySQL conversion
- Core authentication system
- Basic AI chat functionality
- Blog system implementation
- File upload system
- Dashboard and user management
