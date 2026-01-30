<?php
session_start();
include('../../db_connect.php');

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $computer_id = (int)$_POST['computer_id'];

    // Check if computer exists
    $check_sql = "SELECT computer_id, status FROM computers WHERE computer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $computer_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Computer not found.";
        header("Location: ../computers.php");
        exit();
    }

    $computer = $result->fetch_assoc();

    // Check if computer is in use
    if ($computer['status'] === 'in-use') {
        $_SESSION['error'] = "Cannot delete computer that is currently in use.";
        header("Location: ../computers.php");
        exit();
    }

    // Check for active sessions
    $session_check = $conn->query("SELECT session_id FROM user_sessions WHERE computer_id = $computer_id AND status = 'active'");
    if ($session_check && $session_check->num_rows > 0) {
        $_SESSION['error'] = "Cannot delete computer with active sessions.";
        header("Location: ../computers.php");
        exit();
    }

    // Delete computer
    $delete_sql = "DELETE FROM computers WHERE computer_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $computer_id);

    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Computer deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete computer.";
    }

    header("Location: ../computers.php");
    exit();
}
?>
