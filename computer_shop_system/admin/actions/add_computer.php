<?php
session_start();
include('../../db_connect.php');

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $computer_name = trim($_POST['computer_name']);
    $type_id = (int)$_POST['type_id'];

    // Validation
    if (empty($computer_name)) {
        $_SESSION['error'] = "Computer name is required.";
        header("Location: ../computers.php");
        exit();
    }

    // Check if name already exists
    $check_sql = "SELECT computer_id FROM computers WHERE computer_name = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $computer_name);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Computer name already exists.";
        header("Location: ../computers.php");
        exit();
    }

    // Insert new computer
    $insert_sql = "INSERT INTO computers (computer_name, type_id, status) VALUES (?, ?, 'available')";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("si", $computer_name, $type_id);

    if ($insert_stmt->execute()) {
        $_SESSION['success'] = "Computer added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add computer.";
    }

    header("Location: ../computers.php");
    exit();
}
?>
