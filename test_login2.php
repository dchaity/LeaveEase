<?php
require_once 'config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Deep Password Test</h2>";

$email = 'admin@leaveease.com';
$password = 'admin123';

// 1. ডাটাবেস থেকে ইউজার নিন
$stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("❌ User not found!");
}

echo "✅ User found: " . $user['email'] . "<br>";
echo "Stored hash: " . $user['password'] . "<br><br>";

// 2. password_verify() চেক
echo "<h3>1. password_verify() Test:</h3>";
$result1 = password_verify($password, $user['password']);
echo "Result: " . ($result1 ? "✅ MATCHED" : "❌ NOT MATCHED") . "<br><br>";

// 3. password_hash() নতুন হ্যাশ জেনারেট
echo "<h3>2. New Hash Generation:</h3>";
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "New hash: " . $new_hash . "<br>";
$result2 = password_verify($password, $new_hash);
echo "Verify new hash: " . ($result2 ? "✅ MATCHED" : "❌ NOT MATCHED") . "<br><br>";

// 4. PHP Version
echo "<h3>3. PHP Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "password_hash() exists: " . (function_exists('password_hash') ? "✅ Yes" : "❌ No") . "<br>";
echo "password_verify() exists: " . (function_exists('password_verify') ? "✅ Yes" : "❌ No") . "<br><br>";

// 5. সরাসরি হ্যাশ তুলনা (শুধু টেস্ট)
echo "<h3>4. Direct Comparison (Test Only):</h3>";
$test_hash = '$2y$10$ZBzJ5I.SiUYgjXm8qxnUH.DMYDzGQmZGKw8Z/RrmmCkS8LPzp.8zK';
$result3 = password_verify('admin123', $test_hash);
echo "Testing known hash: " . ($result3 ? "✅ MATCHED" : "❌ NOT MATCHED") . "<br>";

// 6. আপডেট করার SQL
echo "<br><h3>5. Fix SQL:</h3>";
echo "<code style='background:#f4f4f4;padding:10px;display:block;'>
UPDATE users SET password = '" . $new_hash . "' WHERE email = 'admin@leaveease.com';
</code>";
?>