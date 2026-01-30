<?php
session_start();
include('../../db_connect.php');

// Verify admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$message = "";
$type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $credits = floatval($_POST['credits']);
    $payment_method = $_POST['payment_method'];
    $reference_number = isset($_POST['reference_number']) ? trim($_POST['reference_number']) : null;

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Email already exists!";
        $type = "error";
    } else {
        // Default password = 123 (hashed)
        $hashed_password = password_hash("123", PASSWORD_DEFAULT);
        $role_id = 2; // user role

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, balance, role_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdi", $full_name, $email, $hashed_password, $credits, $role_id);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            // Insert topup record
            $topup_stmt = $conn->prepare("INSERT INTO topups (user_id, amount) VALUES (?, ?)");
            $topup_stmt->bind_param("id", $user_id, $credits);
            $topup_stmt->execute();
            $topup_id = $conn->insert_id;

            // Insert payment record
            $payment_stmt = $conn->prepare("INSERT INTO payments (topup_id, payment_method, amount) VALUES (?, ?, ?)");
            $payment_stmt->bind_param("isd", $topup_id, $payment_method, $credits);
            $payment_stmt->execute();

            // Insert topup history for income tracking
            $history_stmt = $conn->prepare("INSERT INTO topup_history (user_id, amount, topup_date) VALUES (?, ?, NOW())");
            $history_stmt->bind_param("id", $user_id, $credits);
            $history_stmt->execute();

            // Log action
            $admin_id = $_SESSION['admin_id'];
            $activity = "Added new user: $full_name ($email) with ₱$credits via $payment_method";
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
            $log_stmt->bind_param("is", $admin_id, $activity);
            $log_stmt->execute();

            $message = "User added successfully!";
            $type = "success";
        } else {
            $message = "Error adding user. Please try again.";
            $type = "error";
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User Status - Computer Shop System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('../assets/company/bgadmin.png') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }

        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
            opacity: 0;
            animation: fadeIn 0.8s forwards;
        }

        .modal {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 40px 60px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            transform: translateY(40px);
            opacity: 0;
            animation: slideUp 0.8s 0.3s forwards;
        }

        .modal.success {
            border-top: 6px solid #00ff99;
        }

        .modal.error {
            border-top: 6px solid #ff4d4d;
        }

        .modal h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #fff;
        }

        .modal p {
            color: #eee;
            font-size: 1rem;
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #ff6666, #b30000);
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .icon.success {
            color: #00ff99;
        }

        .icon.error {
            color: #ff4d4d;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="modal <?php echo $type; ?>">
        <?php if ($type === 'success'): ?>
            <div class="icon success">✔</div>
            <h2>Success!</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="../add_user.php" class="btn">Back to Add User</a>
        <?php elseif ($type === 'error'): ?>
            <div class="icon error">✖</div>
            <h2>Error</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="../add_user.php" class="btn">Try Again</a>
        <?php else: ?>
            <div class="icon error">⚠</div>
            <h2>Oops!</h2>
            <p>Something went wrong. Please try again.</p>
            <a href="../add_user.php" class="btn">Back</a>
        <?php endif; ?>
    </div>
</body>
</html>
