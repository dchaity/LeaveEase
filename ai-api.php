<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_message = strtolower(trim($data['message'] ?? ''));

if (empty($user_message)) {
    echo json_encode(['success' => false, 'message' => 'Message is required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$balance = getLeaveBalance($user_id);

function getAIResponse($message, $user_id, $balance) {
    global $conn;

    if (strpos($message, 'balance') !== false || strpos($message, 'remaining') !== false) {
        return "📊 Your current leave balance:\n\n" .
               "• Total Leaves: {$balance['total_leaves']} days\n" .
               "• Used Leaves: {$balance['used_leaves']} days\n" .
               "• Remaining Leaves: {$balance['remaining_leaves']} days\n\n" .
               "You can apply for leave from the 'Apply Leave' page!";
    }

    if (strpos($message, 'apply') !== false || strpos($message, 'request') !== false) {
        return "📝 To apply for leave:\n\n" .
               "1. Go to 'Apply Leave' from the menu\n" .
               "2. Select leave type (Annual/Sick/Casual/etc.)\n" .
               "3. Choose start and end dates\n" .
               "4. Enter reason for leave\n" .
               "5. Click 'Submit Request'\n\n" .
               "Make sure you have enough leave balance!";
    }

    if (strpos($message, 'holiday') !== false || strpos($message, 'holidays') !== false) {
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT * FROM holidays WHERE date >= ? ORDER BY date ASC LIMIT 5");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $holidays = $stmt->get_result();
        if ($holidays->num_rows > 0) {
            $response = "📅 Upcoming Holidays:\n\n";
            while ($holiday = $holidays->fetch_assoc()) {
                $response .= "• {$holiday['name']} - " . date('M d, Y', strtotime($holiday['date'])) . "\n";
            }
            return $response;
        }
        return "📅 No upcoming holidays found.";
    }

    if (strpos($message, 'status') !== false || strpos($message, 'pending') !== false) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM leave_requests WHERE user_id = ? AND status = 'Pending'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc()['count'];
        if ($pending > 0) {
            return "⏳ You have $pending pending leave request(s).\n\nCheck the 'Leave History' page for details!";
        }
        return "✅ You have no pending leave requests.";
    }

    if (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false || strpos($message, 'hey') !== false) {
        return "👋 Hello! I'm your AI Assistant.\n\nHow can I help you today?\n\n" .
               "You can ask me about:\n• Leave balance\n• Applying for leave\n• Request status\n• Upcoming holidays";
    }

    if (strpos($message, 'help') !== false) {
        return "🆘 I'm here to help! Here's what I can do:\n\n" .
               "🔹 Check your leave balance\n🔹 Guide you to apply for leave\n" .
               "🔹 Show upcoming holidays\n🔹 Check request status\n🔹 Answer general questions\n\n" .
               "Just type your question naturally!";
    }

    return "🤔 I understand you're asking about leave management.\n\n" .
           "You can ask me about:\n• Leave balance\n• Applying for leave\n• Request status\n• Upcoming holidays\n• Help\n\n" .
           "Feel free to rephrase your question!";
}

$response = getAIResponse($user_message, $user_id, $balance);

$stmt = $conn->prepare("INSERT INTO ai_chat_history (user_id, message, response) VALUES (?, ?, ?)");
$original_message = $data['message'];
$stmt->bind_param("iss", $user_id, $original_message, $response);
$stmt->execute();

echo json_encode([
    'success' => true,
    'response' => nl2br($response)
]);
?>