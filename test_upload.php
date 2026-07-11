<?php
echo "<h2>🔍 Upload Test</h2>";

// PHP Settings দেখুন
echo "<h3>PHP Settings:</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . " seconds<br>";

// Upload ডিরেক্টরি চেক
$upload_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/profiles/';
echo "<h3>Upload Directory:</h3>";
echo "Path: " . $upload_path . "<br>";
echo "Exists: " . (is_dir($upload_path) ? '✅ Yes' : '❌ No') . "<br>";
echo "Writable: " . (is_writable($upload_path) ? '✅ Yes' : '❌ No') . "<br>";

// পারমিশন দেখুন
if (is_dir($upload_path)) {
    $perms = fileperms($upload_path);
    echo "Permissions: " . substr(sprintf('%o', $perms), -4) . "<br>";
}

// টেস্ট ফাইল আপলোড ফর্ম
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['test_file'];
    echo "<h3>Upload Result:</h3>";
    echo "File Name: " . $file['name'] . "<br>";
    echo "File Type: " . $file['type'] . "<br>";
    echo "File Size: " . round($file['size'] / 1024) . " KB<br>";
    echo "Error Code: " . $file['error'] . "<br>";
    
    if ($file['error'] === 0) {
        $target = $upload_path . 'test_' . time() . '_' . $file['name'];
        if (move_uploaded_file($file['tmp_name'], $target)) {
            echo "✅ Upload successful! File saved as: " . basename($target);
        } else {
            echo "❌ Failed to move uploaded file.";
        }
    } else {
        echo "❌ Upload error: ";
        switch($file['error']) {
            case 1: echo "File exceeds upload_max_filesize"; break;
            case 2: echo "File exceeds MAX_FILE_SIZE"; break;
            case 3: echo "File only partially uploaded"; break;
            case 4: echo "No file uploaded"; break;
            default: echo "Unknown error";
        }
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <h3>Test Upload:</h3>
    <input type="file" name="test_file" required>
    <button type="submit">Upload</button>
</form>