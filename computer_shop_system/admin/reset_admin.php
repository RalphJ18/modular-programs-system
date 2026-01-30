<?php
include('../db_connect.php');

// Delete any existing admin
$conn->query("DELETE FROM users WHERE email='admin@shop.com'");

// Hash new password
$hashed_password = password_hash("admin123", PASSWORD_DEFAULT);

// Reinsert admin
$sql = "INSERT INTO users (full_name, email, password, role_id)
        VALUES ('Admin User', 'admin@shop.com', '$hashed_password', 1)";

if ($conn->query($sql)) {
    echo "✅ Admin account reset successfully.<br>";
    echo "Email: admin@shop.com<br>Password: admin123";
} else {
    echo "❌ Error resetting admin: " . $conn->error;
}
?>
