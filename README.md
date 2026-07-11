# 🏢 LeaveEase - Smart Leave Management System

A modern, responsive web application for managing employee leave requests, featuring **real-time notifications, an AI-powered assistant, an interactive calendar, and a detailed analytics dashboard**.

---

## 🌐 Live Demo

**🔗 https://leavemanage.free.nf/**

---

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
- [Usage](#-usage)
- [Key Components](#-key-components)
- [Database Schema](#-database-schema)
- [API Endpoints](#-api-endpoints)
- [Security Features](#-security-features)
- [Demo Credentials](#-demo-credentials)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [Contact](#-contact)

---

# ✨ Features

## 👤 Employee Features

- Apply for different leave types:
  - Annual Leave
  - Sick Leave
  - Casual Leave
  - Maternity Leave
  - Paternity Leave
  - Study Leave
  - Other Leave

- View leave balance
  - Total Leaves
  - Used Leaves
  - Remaining Leaves

- Track leave history with advanced filters
- Interactive leave calendar with holidays
- Upload supporting documents
- Profile management with profile picture upload
- Browser notifications with sound alerts
- AI-powered chat assistant

---

## 👑 Admin Features

- Dashboard with key metrics
- Approve or reject leave requests
- Manage users (Activate / Deactivate)
- Update employee leave balances
- Generate reports and analytics
- System settings management
- Holiday management
- Activity log monitoring

---

## 🤖 AI Assistant

The built-in AI assistant helps employees by allowing them to:

- Check leave balance
- Learn how to apply for leave
- View upcoming holidays
- Check leave request status
- Ask general leave-related questions

---

## 🔔 Notification System

- Browser push notifications
- Sound alerts for approvals and rejections
- Real-time updates every **15 seconds**
- Mark all notifications as read
- Notification history

---

## 📊 Reports & Analytics

- Monthly leave trends (Line Chart)
- Department-wise statistics
- Leave type distribution (Pie Chart)
- CSV export functionality
- Activity log reports

---

# 🛠️ Technology Stack

## 🎨 Frontend

| Technology | Purpose |
|------------|---------|
| HTML5 | Structure & Semantics |
| CSS3 | Styling & Animations |
| Bootstrap 5.3 | Responsive UI Framework |
| JavaScript (ES6) | Interactivity & API Calls |
| FullCalendar 5.11 | Interactive Calendar |
| Chart.js 4.4 | Data Visualization |
| Font Awesome 6.4 | Icon Library |

---

## ⚙️ Backend

| Technology | Purpose |
|------------|---------|
| PHP 7.4+ | Server-side Logic |
| MySQL 5.7+ | Database Management |
| REST API | Frontend ↔ Backend Communication |
| Web Audio API | Notification Sounds |
| AJAX | Asynchronous Requests |

---

## 🔒 Security

| Feature | Implementation |
|---------|----------------|
| Password Hashing | bcrypt using `password_hash()` |
| SQL Injection Protection | Prepared Statements |
| XSS Protection | `htmlspecialchars()` & `strip_tags()` |
| Session Security | Role-Based Access Control |
| File Upload Security | MIME Type & Size Validation |

---

## ☁️ Hosting & Tools

- InfinityFree (Free PHP & MySQL Hosting)
- phpMyAdmin (Database Management)
- FileZilla (FTP File Transfer)

---

# 📁 Project Structure

```text
leave-management-system/
│
├── admin/                     # Admin panel
│   ├── dashboard.php          # Admin dashboard
│   ├── manage-leaves.php      # Approve/Reject leaves
│   ├── manage-users.php       # User management
│   ├── reports.php            # Analytics reports
│   └── settings.php           # System settings
│
├── api/                       # REST API endpoints
│   ├── ai-api.php             # AI chatbot
│   ├── auth-api.php           # Authentication
│   ├── get-latest-notification.php
│   ├── leave-api.php          # Leave operations
│   └── notification-check.php
│
├── assets/                    # Static assets
│   ├── css/
│   │   ├── admin.css
│   │   └── style.css
│   │
│   ├── images/
│   │   └── default-avatar.png
│   │
│   ├── js/
│   │   ├── admin.js
│   │   ├── ai-assistant.js
│   │   └── main.js
│   │
│   └── uploads/
│       ├── documents/
│       └── profiles/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── pages/
│   ├── apply-leave.php
│   ├── calendar.php
│   ├── dashboard.php
│   ├── forgot-password.php
│   ├── leave-history.php
│   ├── login.php
│   ├── logout.php
│   ├── notifications.php
│   ├── profile.php
│   ├── register.php
│   └── reset-password.php
│
├── sql/
│   └── database.sql
│
├── .htaccess
├── index.php
├── robots.txt
└── README.md
```

---

# 🚀 Getting Started

## 📋 Prerequisites

Before running the project, make sure you have:

- PHP **7.4** or higher
- MySQL **5.7** or higher
- Apache or Nginx Web Server

---

## 💻 Local Installation

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/chaiti-das/leave-management-system.git
cd leave-management-system
```

---

### 2️⃣ Configure the Database

1. Create a new MySQL database.
2. Open **phpMyAdmin**.
3. Import the `sql/database.sql` file into your database.

---

### 3️⃣ Update Database Configuration

Open:

```text
config/database.php
```

Update the following configuration:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'leave_db');
```

---

### 4️⃣ Set Folder Permissions

```bash
chmod -R 755 assets/uploads/
```

---

### 5️⃣ Run the Application

Using PHP's built-in server:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000
```

---

# 🌐 Deployment (InfinityFree)

| Step | Action |
|------|--------|
| 1 | Create an InfinityFree account |
| 2 | Create a MySQL database |
| 3 | Import `database.sql` using phpMyAdmin |
| 4 | Upload all project files to the `htdocs/` directory via FTP |
| 5 | Update `config/database.php` with your hosting credentials |
| 6 | Set the uploads folder permission to **755** |

---

# 📖 Usage

## 👤 Employee Dashboard

After logging in with employee credentials, users can:

- View leave balance
- Check recent activity
- Apply for a new leave request
- Track leave request status
- View leave history
- Browse the leave calendar with holidays

---

## 👑 Admin Panel

After logging in as an administrator, users can:

- Access the admin dashboard
- Manage leave requests
- Approve or reject leave applications
- Manage employee accounts
- Update leave balances
- Generate reports and analytics
- Configure system settings

---

## 🤖 AI Assistant

Using the built-in AI Assistant:

1. Click the AI chatbot icon.
2. Type your question.

Example questions:

- "balance"
- "apply leave"
- "holiday"
- "leave status"

The AI assistant will instantly provide helpful guidance related to leave management.

---

# 🧩 Key Components

| Component | Purpose |
|-----------|---------|
| Header | Navigation with live clock and profile dropdown |
| Footer | Brand information, quick links, social icons, and contact details |
| Dashboard | Employee dashboard with leave balance and recent activity |
| Apply Leave | Leave application form with document upload |
| Leave History | Searchable and filterable leave records |
| Calendar | Interactive calendar with leave schedules and holidays |
| Profile | User profile management with profile picture upload |
| Notifications | Real-time notification center with browser alerts and sound |
| Admin Dashboard | Key metrics, analytics, and activity overview |
| Manage Leaves | Approve or reject employee leave requests |
| Manage Users | User management and leave balance updates |
| Reports | Analytics dashboard with charts and CSV export |
| Settings | System configuration and holiday management |

---

# 🗄️ Database Schema

## Core Tables

| Table | Description |
|--------|-------------|
| `users` | User management (Admin & Employee accounts) |
| `leave_requests` | Leave applications and approval status |
| `notifications` | Notification system |
| `holidays` | Holiday management |
| `activity_logs` | User activity audit trail |
| `ai_chat_history` | AI assistant conversation history |

---

## Entity Relationships

```text
users (1)
   │
   ├───────────────< leave_requests (N)

users (1)
   │
   ├───────────────< notifications (N)

users (1)
   │
   ├───────────────< activity_logs (N)

users (1)
   │
   └───────────────< ai_chat_history (N)

leave_requests
      │
      └──────────── approved_by ─────► users (Admin)
```

---

# 📡 API Endpoints

## 🔐 Authentication

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/auth-api.php?action=login` | POST | User login |
| `/api/auth-api.php?action=logout` | GET | User logout |
| `/api/auth-api.php?action=check` | GET | Check active session |

---

## 📝 Leave Management

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/leave-api.php` | POST | Apply, approve, reject, or cancel leave requests |

---

## 🤖 AI Assistant

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/ai-api.php` | POST | Chat with the AI assistant |

---

## 🔔 Notifications

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/notification-check.php` | GET | Check for new notifications |
| `/api/get-latest-notification.php` | GET | Retrieve the latest notification |

---

# 🔒 Security Features

| Feature | Implementation |
|----------|----------------|
| Password Security | `bcrypt` hashing using `password_hash()` |
| SQL Injection Protection | Prepared statements for all database queries |
| XSS Protection | `htmlspecialchars()` on all user output |
| Session Security | Role-based access control |
| File Upload Security | MIME type validation and file size limits |
| CSRF Protection | Session-based CSRF token validation |

---

# 💰 Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@leaveease.com` | `admin123` |
| Employee | `john@leaveease.com` | `employee123` |
| Employee | `jane@leaveease.com` | `employee123` |

> **Note:** These credentials are for demonstration purposes only.

---

# 🌐 Deployment

This project is successfully deployed on **InfinityFree**.

## 🔗 Live Demo

**https://leavemanage.free.nf/**

---

## Deploy Your Own Version

1. Push the project to GitHub.
2. Create an InfinityFree account.
3. Create a MySQL database.
4. Import the `database.sql` file using phpMyAdmin.
5. Upload all project files to the `htdocs/` directory using FTP.
6. Update `config/database.php` with your database credentials.
7. Visit your domain and start using the application.

---

# 🤝 Contributing

Contributions are welcome!

If you would like to improve this project, please follow these steps:

1. **Fork** the repository.
2. Create a new feature branch.

```bash
git checkout -b feature/amazing-feature
```

3. Commit your changes.

```bash
git commit -m "Add amazing feature"
```

4. Push the branch.

```bash
git push origin feature/amazing-feature
```

5. Open a **Pull Request**.

---

# 📏 Coding Standards

| Language | Standard |
|----------|----------|
| PHP | PSR-12 |
| JavaScript | ES6 |
| CSS | Bootstrap 5 Conventions |

---

# 📧 Contact

**Project Name**

**LeaveEase – Smart Leave Management System**

| Platform | Link |
|----------|------|
| 🌐 Live Demo | https://leavemanage.free.nf/ |
| 💻 GitHub | https://github.com/chaiti-das/leave-management-system |
| 👤 Developer | **Chaiti Das** |
| 💼 LinkedIn | https://www.linkedin.com/in/chaity-das-682bb32a3 |
| 📧 Email | dchaity240@gmail.com |

---

# 📄 License

This project is licensed under the **MIT License**.

You are free to use, modify, and distribute this project under the terms of the MIT License.

---

# 🙏 Acknowledgments

Special thanks to the following amazing projects and communities:

- Bootstrap Team — Responsive UI Framework
- Chart.js — Beautiful Charts & Analytics
- FullCalendar — Interactive Calendar
- Font Awesome — Modern Icon Library
- InfinityFree — Free PHP & MySQL Hosting
- phpMyAdmin — Database Management
- FileZilla — FTP File Transfer

---

# ⭐ Support

If you found this project helpful, please consider giving it a **⭐ Star** on GitHub.

Your support helps others discover the project and motivates future improvements.

---

# 🚀 Future Improvements

- Email Notifications
- SMS Notifications
- PDF Report Export
- Multi-language Support
- Dark Mode
- Attendance Management
- Payroll Integration
- Mobile Application (Android & iOS)
- Google OAuth Login
- Microsoft Authentication
- Advanced Analytics Dashboard
- REST API v2

---

<div align="center">

## ❤️ Made with Passion

### **LeaveEase – Smart Leave Management System**

Developed by **Chaiti Das**

🌐 Live Demo  
https://leavemanage.free.nf/

💻 GitHub Repository  
https://github.com/chaiti-das/leave-management-system

---

**Thank you for visiting this project!**

If you like it, don't forget to **⭐ Star** the repository.

© 2026 LeaveEase. All Rights Reserved.

</div>
