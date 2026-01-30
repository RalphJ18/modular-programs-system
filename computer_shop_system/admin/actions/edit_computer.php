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
    $computer_name = trim($_POST['computer_name']);
    $type_id = (int)$_POST['type_id'];

    // Validation
    if (empty($computer_name)) {
        $_SESSION['error'] = "Computer name is required.";
        header("Location: ../computers.php");
        exit();
    }

    // Check if name already exists (excluding current)
    $check_sql = "SELECT computer_id FROM computers WHERE computer_name = ? AND computer_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $computer_name, $computer_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Computer name already exists.";
        header("Location: ../computers.php");
        exit();
    }

    // Update computer
    $update_sql = "UPDATE computers SET computer_name = ?, type_id = ? WHERE computer_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $computer_name, $type_id, $computer_id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Computer updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update computer.";
    }

    header("Location: ../computers.php");
    exit();
}
?>
