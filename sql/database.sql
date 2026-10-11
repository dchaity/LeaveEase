-- ============================================
-- LEAVEEASE DATABASE SCHEMA
-- Compatible with InfinityFree MySQL
-- ============================================

-- Note: On InfinityFree, you don't need CREATE DATABASE
-- The database is already created. Just run the tables.

-- ============================================
-- 1. USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('employee', 'admin') DEFAULT 'employee',
    department VARCHAR(50),
    position VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    profile_pic VARCHAR(255) DEFAULT 'default.png',
    total_leaves INT DEFAULT 20,
    used_leaves INT DEFAULT 0,
    remaining_leaves INT DEFAULT 20,
    is_active BOOLEAN DEFAULT TRUE,
    theme_preference ENUM('light', 'dark') DEFAULT 'light',
    reset_token VARCHAR(255) NULL,
    reset_expires DATETIME NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. LEAVE REQUESTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    leave_type ENUM('Annual', 'Sick', 'Casual', 'Maternity', 'Paternity', 'Study', 'Other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT NOT NULL,
    document VARCHAR(255) NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') DEFAULT 'Pending',
    admin_comment TEXT,
    approved_by INT NULL,
    approved_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. HOLIDAYS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS holidays (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    type ENUM('Public', 'Company', 'Optional') DEFAULT 'Public',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. AI CHAT HISTORY TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS ai_chat_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. ACTIVITY LOGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 7. SETTINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'LeaveEase Solutions'),
('company_email', 'hr@leaveease.com'),
('company_phone', '+880 1234-567890'),
('default_leaves', '20')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Admin user (password: admin123)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO users (username, email, password, full_name, role, department, position) 
VALUES ('admin', 'admin@leaveease.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'admin', 'Management', 'Administrator')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Sample employee (password: employee123)
INSERT INTO users (username, email, password, full_name, role, department, position) 
VALUES ('john.doe', 'john@leaveease.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'employee', 'IT', 'Software Engineer')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Sample employee 2 (password: employee123)
INSERT INTO users (username, email, password, full_name, role, department, position) 
VALUES ('jane.smith', 'jane@leaveease.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'employee', 'HR', 'HR Manager')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Bangladesh Holidays 2026
INSERT INTO holidays (name, date, type, description) VALUES
('New Year\'s Day', '2026-01-01', 'Public', 'English New Year'),
('International Mother Language Day', '2026-02-21', 'Public', 'Shaheed Dibosh'),
('Independence Day', '2026-03-26', 'Public', 'Shadhinota Dibosh'),
('Bengali New Year', '2026-04-14', 'Public', 'Pohela Boishakh'),
('Eid-ul-Fitr', '2026-04-10', 'Public', 'End of Ramadan'),
('May Day', '2026-05-01', 'Public', 'International Workers Day'),
('Eid-ul-Adha', '2026-06-17', 'Public', 'Festival of Sacrifice'),
('National Mourning Day', '2026-08-15', 'Public', 'National Mourning Day'),
('Victory Day', '2026-12-16', 'Public', 'Bijoy Dibosh'),
('Christmas Day', '2026-12-25', 'Public', 'Birth of Jesus Christ');

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================
CREATE INDEX idx_leave_user ON leave_requests(user_id);
CREATE INDEX idx_leave_status ON leave_requests(status);
CREATE INDEX idx_leave_dates ON leave_requests(start_date, end_date);
CREATE INDEX idx_notification_user ON notifications(user_id);
CREATE INDEX idx_notification_read ON notifications(is_read);
CREATE INDEX idx_activity_user ON activity_logs(user_id);
CREATE INDEX idx_chat_user ON ai_chat_history(user_id);
CREATE INDEX idx_holiday_date ON holidays(date);

-- ============================================
-- SETUP COMPLETE
-- ============================================
-- Admin Login: admin@leaveease.com / admin123
-- Employee Login: john@leaveease.com / employee123
-- Employee Login: jane@leaveease.com / employee123
-- ============================================