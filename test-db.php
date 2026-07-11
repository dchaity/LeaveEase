<?php
// ডাটাবেস কানেকশন টেস্ট
echo "<h2>🔍 ডাটাবেস কানেকশন টেস্ট</h2>";

// ডাটাবেস কানেকশন
$host = 'sql310.infinityfree.com';
$user = 'if0_42349364';
$pass = 'AseFBKTsIThki';
$db = 'if0_42349364_leave_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error;
} else {
    echo "✅ Connection successful!<br><br>";
    
    // ইউজার চেক করুন
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Total users: " . $row['total'] . "<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
    
    // ইউজার লিস্ট দেখুন
    $users = $conn->query("SELECT id, email, username, full_name, role, is_active FROM users");
    if ($users && $users->num_rows > 0) {
        echo "<br><h3>📋 User List:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Email</th><th>Username</th><th>Name</th><th>Role</th><th>Active</th></tr>";
        while ($u = $users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $u['id'] . "</td>";
            echo "<td>" . $u['email'] . "</td>";
            echo "<td>" . $u['username'] . "</td>";
            echo "<td>" . $u['full_name'] . "</td>";
            echo "<td>" . $u['role'] . "</td>";
            echo "<td>" . ($u['is_active'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // পাসওয়ার্ড হ্যাশ চেক করুন
    $hash_check = $conn->query("SELECT email, password FROM users WHERE email = 'admin@leaveease.com'");
    if ($hash_check && $hash_check->num_rows > 0) {
        $h = $hash_check->fetch_assoc();
        echo "<br>🔑 Admin password hash: " . $h['password'];
    }
}

$conn->close();
?>