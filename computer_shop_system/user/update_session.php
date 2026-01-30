<?php
// user/update_session.php
session_start();
header('Content-Type: application/json');

include('../db_connect.php');

// Quick helper to send JSON and exit
function json_exit($conn, $status, $message, $data = []) {
    if (isset($conn) && $conn instanceof mysqli) {
        // optional: close connection
        $conn->close();
    }
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data));
    exit();
}

// Require AJAX POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_exit($conn, 'error', 'Invalid request method.');
}

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    json_exit($conn, 'error', 'Not authenticated.');
}

// Read and validate POST inputs
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
$rate = isset($_POST['rate']) ? floatval($_POST['rate']) : 0.0;

// Basic validations
if ($user_id <= 0 || $session_id <= 0 || $rate <= 0) {
    json_exit($conn, 'error', 'Invalid parameters.');
}

// Security: ensure the caller is the same logged-in user (prevent spoof)
if ($_SESSION['user_id'] !== $user_id) {
    json_exit($conn, 'error', 'User mismatch.');
}

// Start transaction for atomic update
$conn->begin_transaction();

try {
    // Lock the user's balance row to prevent race conditions
    $stmt = $conn->prepare("SELECT balance, active_pc_id FROM users WHERE user_id = ? FOR UPDATE");
    if (!$stmt) throw new Exception("Prepare failed (select user): " . $conn->error);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) {
        $stmt->close();
        throw new Exception("User not found.");
    }
    $userRow = $res->fetch_assoc();
    $current_balance = (float)$userRow['balance'];
    $active_pc_id = $userRow['active_pc_id'];
    $stmt->close();

    // Deduct rate from balance (one minute worth)
    $new_balance = round($current_balance - $rate, 2); // round to 2 decimals

    // Update users.balance
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE user_id = ?");
    if (!$stmt) throw new Exception("Prepare failed (update balance): " . $conn->error);
    $stmt->bind_param('di', $new_balance, $user_id);
    $stmt->execute();
    if ($stmt->errno) {
        $stmt->close();
        throw new Exception("Failed to update balance: " . $stmt->error);
    }
    $stmt->close();

    // Update session: increment duration_minutes by 1 and add rate to total_cost
    // Ensure session is still active before updating
    $stmt = $conn->prepare("SELECT status, duration_minutes, total_cost FROM user_sessions WHERE session_id = ? FOR UPDATE");
    if (!$stmt) throw new Exception("Prepare failed (select session): " . $conn->error);
    $stmt->bind_param('i', $session_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) {
        $stmt->close();
        throw new Exception("Session not found.");
    }
    $sessionRow = $res->fetch_assoc();
    $session_status = $sessionRow['status'];
    $stmt->close();

    if ($session_status !== 'active') {
        // Nothing to do if session is not active
        $conn->commit();
        json_exit($conn, 'ok', 'Session not active, no update performed.', [
            'balance' => number_format($new_balance, 2),
            'session_status' => $session_status
        ]);
    }

    // Perform the session update
    $stmt = $conn->prepare("
        UPDATE user_sessions 
        SET duration_minutes = duration_minutes + 1,
            total_cost = total_cost + ?
        WHERE session_id = ?
    ");
    if (!$stmt) throw new Exception("Prepare failed (update session): " . $conn->error);
    $stmt->bind_param('di', $rate, $session_id);
    $stmt->execute();
    if ($stmt->errno) {
        $stmt->close();
        throw new Exception("Failed to update session: " . $stmt->error);
    }
    $stmt->close();

    // If new_balance <= 0 -> end session and free PC
    if ($new_balance <= 0) {
        // End the session
        $stmt = $conn->prepare("UPDATE user_sessions SET status = 'ended', end_time = NOW() WHERE session_id = ?");
        if (!$stmt) throw new Exception("Prepare failed (end session): " . $conn->error);
        $stmt->bind_param('i', $session_id);
        $stmt->execute();
        $stmt->close();

        // Clear user's active_pc_id
        $stmt = $conn->prepare("UPDATE users SET active_pc_id = NULL WHERE user_id = ?");
        if (!$stmt) throw new Exception("Prepare failed (clear active_pc): " . $conn->error);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

        // If we have a pc id, set computer status to 'available'
        if (!empty($active_pc_id)) {
            $stmt = $conn->prepare("UPDATE computers SET status = 'available' WHERE computer_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $active_pc_id);
                $stmt->execute();
                $stmt->close();
            } // ignore failing to update pc to avoid fatal
        }

        $conn->commit();
        json_exit($conn, 'ended', 'Balance exhausted. Session ended.', [
            'balance' => number_format(max($new_balance, 0), 2),
            'session_status' => 'ended'
        ]);
    }

    // Otherwise commit and return updated values
    $conn->commit();
    json_exit($conn, 'ok', 'Session updated.', [
        'balance' => number_format($new_balance, 2),
        'session_status' => 'active'
    ]);

} catch (Exception $e) {
    // rollback on error
    if ($conn->errno) $conn->rollback();
    json_exit($conn, 'error', 'Server error: ' . $e->getMessage());
}
