# 🚀 Quick Setup Guide for MomCare AI Assistant (PHP Version)

## Step-by-Step Instructions to Run on Your PC

### 📋 **What You Need First:**
1. **XAMPP** - Download from: https://www.apachefriends.org/download.html
2. **A web browser** (Chrome, Firefox, etc.)

---

## 🔧 **Installation Process**

### **Step 1: Install XAMPP**
1. Download XAMPP for Windows
2. Run the installer **as Administrator**
3. Install with default settings (make sure Apache, MySQL, and PHP are selected)
4. Default location: `C:\xampp\` ✅

### **Step 2: Start the Services**
1. Open **XAMPP Control Panel** (as Administrator)
2. Click **"Start"** next to **Apache** ⚡
3. Click **"Start"** next to **MySQL** 🗄️
4. Both should show **green "Running"** status

### **Step 3: Copy Your Files**
1. Go to `C:\xampp\htdocs\`
2. Create a new folder called `momcare`
3. Copy ALL files from your `php_version` folder into `C:\xampp\htdocs\momcare\`

**Your folder should look like:**
```
C:\xampp\htdocs\momcare\
├── config/
├── includes/
├── uploads/
├── index.php
├── login.php
├── signup.php
├── dashboard.php
├── chat.php
├── install.php
├── schema.sql
└── (all other files)
```

### **Step 4: Run the Installation**
1. Open your web browser
2. Go to: **`http://localhost/momcare/install.php`**
3. Follow these steps:

#### **Installation Step 1 - Database Setup:**
- Database Host: `localhost`
- Database Name: `momcare_ai`
- Username: `root`
- Password: (leave empty)
- Click **"Setup Database"**

#### **Installation Step 2 - Create Admin User:**
- Full Name: (Your name)
- Email: (Your email)
- Password: (Choose a password)
- Click **"Create Admin User"**

#### **Installation Step 3 - Complete:**
- Click **"Go to Homepage"**

---

## 🌐 **Using the Application**

### **Main URLs:**
- **Homepage**: `http://localhost/momcare/`
- **Login**: `http://localhost/momcare/login.php`
- **Sign Up**: `http://localhost/momcare/signup.php`
- **Dashboard**: `http://localhost/momcare/dashboard.php`
- **AI Chat**: `http://localhost/momcare/chat.php`
- **Blog**: `http://localhost/momcare/blog.php`

---

## ❗ **Common Problems & Solutions**

### **Problem**: "Connection error" message
**Solution**: 
- Make sure MySQL is running in XAMPP Control Panel
- Check if the green "Running" light is on

### **Problem**: "Page not found" error
**Solution**: 
- Make sure Apache is running in XAMPP Control Panel
- Check your URL: `http://localhost/momcare/` (not `https://`)

### **Problem**: Apache won't start (Port 80 conflict)
**Solution**: 
1. In XAMPP Control Panel, click "Config" next to Apache
2. Select "httpd.conf"
3. Find line `Listen 80` and change to `Listen 8080`
4. Save and restart Apache
5. Use URL: `http://localhost:8080/momcare/`

### **Problem**: Install.php says "Already installed"
**Solution**: 
- Delete the file `config/installed.lock`
- Try the installation again

---

## 🧪 **Test the Application**

### **Test 1: Create a User Account**
1. Go to `http://localhost/momcare/signup.php`
2. Fill in the form:
   - Name: "Test User"
   - Email: "test@example.com"
   - Age: 28
   - Weeks Pregnant: 12
   - Password: "test123"
3. Click "Create Account"

### **Test 2: Try the AI Chat**
1. Login with your account
2. Go to Dashboard
3. Click "Chat with AI"
4. Type: "Hello, I have questions about pregnancy"
5. The AI should respond!

---

## 📱 **Quick Access Checklist**

- [ ] XAMPP installed and running
- [ ] Apache service: **Running** (green)
- [ ] MySQL service: **Running** (green)
- [ ] Files copied to `C:\xampp\htdocs\momcare\`
- [ ] Installation completed at `http://localhost/momcare/install.php`
- [ ] Can access homepage at `http://localhost/momcare/`
- [ ] Can login and use features

---

## 🆘 **Still Having Issues?**

### **Check These:**
1. **XAMPP Control Panel** - Are Apache and MySQL both green and "Running"?
2. **File Location** - Are all files in `C:\xampp\htdocs\momcare\`?
3. **URL** - Are you using `http://localhost/momcare/` (not https)?
4. **Browser** - Try refreshing or clearing cache (Ctrl+F5)

### **Error Logs:**
- XAMPP errors: `C:\xampp\apache\logs\error.log`
- Check browser console (Press F12 → Console tab)

---

## 🎉 **You're Ready!**

Once everything is working:
- Create user accounts
- Test the AI chat feature
- Explore the blog section
- Try uploading documents
- Customize the application as needed

**Enjoy your MomCare AI Assistant!** 👶💕
