<?php
session_start();

echo "<h2>🔍 Session Check</h2>";

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    echo "✅ Session is working! User ID: " . $_SESSION['user_id'];
} else {
    echo "❌ Session is NOT working!";
}

// Session ID দেখুন
echo "<br><br>Session ID: " . session_id();
?>