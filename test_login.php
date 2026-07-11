<?php
require_once 'config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Login Debug Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    echo "Email: " . $email . "<br>";
    echo "Password: " . $password . "<br><br>";
    
    $stmt = $conn->prepare("SELECT id, username, email, password, role, full_name FROM users WHERE email = ? AND is_active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo "✅ User found!<br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Stored hash: " . $user['password'] . "<br>";
        
        $verify_result = password_verify($password, $user['password']);
        echo "password_verify result: " . ($verify_result ? "✅ TRUE" : "❌ FALSE") . "<br>";
        
        if ($verify_result) {
            echo "✅✅✅ PASSWORD MATCHED!<br>";
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            echo "Session set! <a href='pages/dashboard.php'>Go to Dashboard</a>";
        } else {
            echo "❌ Password did not match!<br>";
            
            // নতুন হ্যাশ জেনারেট
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            echo "New hash for '" . $password . "':<br>";
            echo "<code style='background:#f4f4f4;padding:10px;display:block;'>" . $new_hash . "</code><br>";
            echo "Run this SQL:<br>";
            echo "<code style='background:#f4f4f4;padding:10px;display:block;'>
UPDATE users SET password = '" . $new_hash . "' WHERE email = '" . $email . "';
            </code>";
        }
    } else {
        echo "❌ User not found or inactive!";
    }
} else {
    ?>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" placeholder="Email" value="john@leaveease.com" style="width:100%;padding:8px;"><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" placeholder="Password" value="employee123" style="width:100%;padding:8px;"><br><br>
        <button type="submit" style="padding:10px 30px;background:#00a86b;color:white;border:none;border-radius:5px;">Login Test</button>
    </form>
    <?php
}
?>