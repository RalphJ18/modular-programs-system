<?php
header('Content-Type: application/json');
include('../db_connect.php');

// ✅ Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// ✅ Validate inputs
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;

if ($user_id <= 0 || $session_id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid user or session ID.'
    ]);
    exit();
}

// ✅ Use prepared statement to avoid SQL injection
$stmt = $conn->prepare("
    SELECT status 
    FROM user_sessions 
    WHERE session_id = ? AND user_id = ? 
    LIMIT 1
");
$stmt->bind_param("ii", $session_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Return session status
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => $row['status'],
        'message' => 'Session found.'
    ]);
} else {
    echo json_encode([
        'status' => 'ended',
        'message' => 'No active session found.'
    ]);
}

$stmt->close();
$conn->close();
?>
