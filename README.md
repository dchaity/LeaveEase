🏢 LeaveEase - Smart Leave Management System
A modern, responsive web application for managing employee leave requests, featuring real-time notifications, AI-powered assistant, interactive calendar, and detailed analytics dashboard.

🌐 Live Link
https://leavemanage.free.nf/

📋 Table of Contents
Features

Technology Stack

Project Structure

Installation & Setup

Usage

Key Components

Database Schema

API Endpoints

Security Features

Demo Credentials

Deployment

Contributing

Contact

✨ Features
👤 Employee Features
Apply for Leave: Multiple leave types (Annual, Sick, Casual, Maternity, Paternity, Study, Other)

Leave Balance: Real-time view of total, used, and remaining leaves

Leave History: Filterable history with search functionality

Interactive Calendar: Visual representation of leaves and holidays

Document Upload: Attach supporting documents (PDF, JPG, PNG)

Profile Management: Update profile, change password, upload profile picture

Browser Notifications: Real-time alerts with custom sound effects

AI Chat Assistant: 24/7 intelligent chatbot for queries

👑 Admin Features
Dashboard: Key metrics with interactive charts

Manage Leaves: Approve/Reject requests with comments

User Management: Activate/Deactivate users, update leave balances

Reports & Analytics: Monthly trends, department stats, leave type distribution

System Settings: Company info, default leaves, holiday management

Activity Logs: Complete audit trail of all actions

🤖 AI Assistant
Check leave balance

Guide on applying for leave

Show upcoming holidays

Check request status

General Q&A support

🔔 Notification System
Browser push notifications

Sound alerts for approvals/rejections

Real-time updates every 15 seconds

Mark all as read functionality

Notification history

📊 Reports & Analytics
Monthly trends line chart

Department-wise statistics

Leave type distribution chart

CSV export functionality

Activity logs



🛠️ Technology Stack
Frontend:

HTML5

CSS3

Bootstrap 5.3.0

JavaScript ES6

FullCalendar 5.11.3

Chart.js 4.4.0

Font Awesome 6.4.0

Backend:

PHP 7.4+

MySQL 5.7+

REST API

Web Audio API

AJAX

Security:

Password Hashing: bcrypt with password_hash()

SQL Injection: Prepared statements

XSS Protection: htmlspecialchars() & strip_tags()

Session Security: Role-based access control

File Uploads: MIME type & size validation

Hosting:

InfinityFree (Free PHP/MySQL hosting)

phpMyAdmin (Database management)

FTP (File transfer with FileZilla)

📁 Project Structure
text
leave-management-system/
│
├── 📁 admin/                          # Admin panel
│   ├── dashboard.php                  # Admin dashboard
│   ├── manage-leaves.php              # Approve/Reject leaves
│   ├── manage-users.php               # User management
│   ├── reports.php                    # Analytics reports
│   └── settings.php                   # System settings
│
├── 📁 api/                            # REST API endpoints
│   ├── ai-api.php                     # AI chatbot
│   ├── auth-api.php                   # Authentication
│   ├── get-latest-notification.php   # Latest notification
│   ├── leave-api.php                 # Leave operations
│   └── notification-check.php        # Notification check
│
├── 📁 assets/                         # Static assets
│   ├── 📁 css/
│   │   ├── admin.css                  # Admin styles
│   │   └── style.css                  # Main styles
│   ├── 📁 images/
│   │   └── default-avatar.png         # Default profile pic
│   ├── 📁 js/
│   │   ├── admin.js                   # Admin JS
│   │   ├── ai-assistant.js            # AI chatbot JS
│   │   └── main.js                    # Main JS
│   └── 📁 uploads/                    # User uploads
│       ├── 📁 documents/              # Leave documents
│       └── 📁 profiles/               # Profile pictures
│
├── 📁 config/
│   └── database.php                   # Database configuration
│
├── 📁 includes/
│   ├── footer.php                     # Footer component
│   └── header.php                     # Header component
│
├── 📁 pages/                          # Public pages
│   ├── apply-leave.php                # Apply leave
│   ├── calendar.php                   # Leave calendar
│   ├── dashboard.php                  # Employee dashboard
│   ├── forgot-password.php            # Password reset
│   ├── leave-history.php              # Leave history
│   ├── login.php                      # User login
│   ├── logout.php                     # User logout
│   ├── notifications.php              # Notification center
│   ├── profile.php                    # User profile
│   ├── register.php                   # User registration
│   └── reset-password.php             # Reset password
│
├── 📁 sql/
│   └── database.sql                   # Database schema
│
├── .htaccess                          # Apache configuration
├── index.php                          # Landing page
├── robots.txt                         # SEO configuration
└── README.md                          # Project documentation
🚀 Installation & Setup
Prerequisites
PHP 7.4 or higher

MySQL 5.7 or higher

Web server (Apache/Nginx)


Steps
Clone the repository:

bash
git clone https://github.com/chaiti-das/leave-management-system.git
cd leave-management-system
Configure Database:

Create a MySQL database

Import database.sql via phpMyAdmin

Update Configuration:

php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'leave_db');
Set Permissions:

bash
chmod -R 755 assets/uploads/
Run the Application:

bash
php -S localhost:8000
📖 Usage
Employee Dashboard
Login with employee credentials

View leave balance and recent activity

Apply for new leave requests

Track leave history and status

View calendar with leaves and holidays

Admin Panel
Login with admin credentials

Access admin dashboard

Manage all leave requests

Manage users and their leave balances

Generate reports and analytics

Configure system settings

AI Assistant
Click on the AI chatbot icon

Type your question (e.g., "balance", "apply", "holiday")

Get instant AI-powered responses

Receive guidance on leave management

🧩 Key Components
Component	Purpose
Header	Navigation with live clock and profile dropdown
Footer	Brand info, quick links, social icons, contact
Dashboard	Employee dashboard with leave balance and history
Apply Leave	Leave application form with document upload
Leave History	Filterable and searchable leave records
Calendar	Interactive calendar with leaves and holidays
Profile	User profile management with picture upload
Notifications	Real-time notification center with sounds
Admin Dashboard	Key metrics and activity logs
Manage Leaves	Approve/Reject leave requests
Manage Users	User management and leave balance updates
Reports	Analytics with charts and CSV export
Settings	System configuration and holiday management
🗄️ Database Schema
Core Tables
Table	Description
users	User management (admin/employee)
leave_requests	Leave applications with status
notifications	Notification system
holidays	Holiday management
activity_logs	Audit trail
ai_chat_history	AI conversations
Key Relationships
text
users (1) ────── (N) leave_requests
users (1) ────── (N) notifications
users (1) ────── (N) activity_logs
users (1) ────── (N) ai_chat_history
leave_requests (1) ────── (1) users (approved_by)
📡 API Endpoints
Authentication
Endpoint	Method	Description
/api/auth-api.php?action=login	POST	User login
/api/auth-api.php?action=logout	GET	User logout
/api/auth-api.php?action=check	GET	Check session
Leave Management
Endpoint	Method	Description
/api/leave-api.php	POST	Approve/Reject/Cancel leave
AI Assistant
Endpoint	Method	Description
/api/ai-api.php	POST	Chat with AI
Notifications
Endpoint	Method	Description
/api/notification-check.php	GET	Check new notifications
/api/get-latest-notification.php	GET	Get latest notification
🔒 Security Features
Feature	Implementation
Password Security	bcrypt hashing with password_hash()
SQL Injection	All queries use prepared statements
XSS Protection	htmlspecialchars() on all output
Session Security	Role-based access control
File Uploads	MIME type validation, size limits
CSRF Protection	Session token validation
💰 Demo Credentials
Role	Email	Password
Admin	admin@leaveease.com	admin123
Employee	john@leaveease.com	employee123
Employee	jane@leaveease.com	employee123
🌐 Deployment
This project is deployed on InfinityFree.

Live Demo: https://leavemanage.free.nf/

To deploy your own version:
Create account on InfinityFree

Create MySQL database

Import database.sql via phpMyAdmin

Upload all files via FTP to htdocs/

Update database.php with server credentials

Set folder permissions to 755

🤝 Contributing
Contributions are welcome! To contribute:

Fork the repository

Create a feature branch (git checkout -b feature/amazing-feature)

Commit your changes (git commit -m 'Add some amazing feature')

Push to the branch (git push origin feature/amazing-feature)

Open a Pull Request

Coding Standards
Language	Standard
PHP	PSR-12
JavaScript	ES6
CSS	Bootstrap 5 conventions

📧 Contact
Project Name: LeaveEase
Live URL: https://leavemanage.free.nf/

Developer: Chaiti Das

Platform	Link
GitHub	chaiti-das
LinkedIn	chaiti-das-2026
Email	dchaity240@gmail.com
📄 License
This project is open source and available under the MIT License.

🙏 Acknowledgments
Bootstrap Team - Amazing UI framework

Chart.js - Beautiful charts

FullCalendar - Interactive calendar

InfinityFree - Free hosting

Font Awesome - Icon library

Made with ❤️ for employees and administrators everywhere
