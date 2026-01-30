<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$type = "";
$amount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);

    if ($user_id > 0 && $amount > 0) {
        // Fetch current balance
        $result = $conn->query("SELECT balance, full_name FROM users WHERE user_id = $user_id");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $new_balance = $user['balance'] + $amount;
            $user_name = $user['full_name'];

            // Update balance
            $conn->query("UPDATE users SET balance = $new_balance WHERE user_id = $user_id");

            // Log the transaction
            $conn->query("
                INSERT INTO topup_history (user_id, amount, date_added) 
                VALUES ($user_id, $amount, NOW())
            ");

            // Success message
            $message = "₱" . number_format($amount, 2) . " added successfully to <b>$user_name</b>!";
            $type = "success";
        } else {
            $message = "User not found.";
            $type = "error";
        }
    } else {
        $message = "Invalid user or amount.";
        $type = "error";
    }
} else {
    header("Location: manage_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Credits - Computer Shop System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('assets/company/bgadmin.png') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            color: #fff;
        }

        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
            opacity: 0;
            animation: fadeIn 0.4s forwards;
        }

        .modal {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 50px 70px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            transform: translateY(40px);
            opacity: 0;
            animation: slideUp 0.4s 0.1s forwards;
        }

        .modal.success {
            border-top: 6px solid #00ff99;
        }

        .modal.error {
            border-top: 6px solid #ff4d4d;
        }

        .modal h2 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #fff;
        }

        .modal p {
            font-size: 1.05rem;
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.9);
        }

        .btn {
            display: inline-block;
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #ff6666, #b30000);
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

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="modal <?php echo $type; ?>">
        <?php if ($type === 'success'): ?>
            <div class="icon success">✔</div>
            <h2>Top-up Successful!</h2>
            <p><?php echo $message; ?></p>
            <a href="manage_users.php" class="btn">Back to Manage Users</a>
        <?php elseif ($type === 'error'): ?>
            <div class="icon error">✖</div>
            <h2>Error</h2>
            <p><?php echo $message; ?></p>
            <a href="manage_users.php" class="btn">Try Again</a>
        <?php else: ?>
            <div class="icon error">⚠</div>
            <h2>Oops!</h2>
            <p>Something went wrong. Please try again.</p>
            <a href="manage_users.php" class="btn">Back</a>
        <?php endif; ?>
    </div>
</body>
</html>
