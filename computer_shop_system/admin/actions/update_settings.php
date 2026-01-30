<?php
session_start();
include('../../db_connect.php');

// Ensure only admin access
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$message = "";
$type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_name = trim($_POST['shop_name']);
    $basic_rate = floatval($_POST['basic_rate']);
    $vip_rate = floatval($_POST['vip_rate']);

    // Update the settings table
    $stmt = $conn->prepare("UPDATE settings 
                            SET shop_name = ?, basic_rate = ?, vip_rate = ?, last_updated = NOW() 
                            WHERE setting_id = 1");
    $stmt->bind_param("sdd", $shop_name, $basic_rate, $vip_rate);

    if ($stmt->execute()) {
        // Log admin activity
        $admin_id = $_SESSION['admin_id'];
        $activity = "Updated settings: Shop name = '$shop_name', Basic rate = ₱$basic_rate/hr, VIP rate = ₱$vip_rate/hr";
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
        $log_stmt->bind_param("is", $admin_id, $activity);
        $log_stmt->execute();

        $message = "Settings updated successfully!";
        $type = "success";
    } else {
        $message = "Error updating settings. Please try again.";
        $type = "error";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Settings - Computer Shop System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('../assets/company/bgadmin.png') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
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

        @keyframes fadeIn { to { opacity: 1; } }
        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="modal <?php echo $type; ?>">
        <?php if ($type === 'success'): ?>
            <div class="icon success">✔</div>
            <h2>Success</h2>
            <p><?php echo $message; ?></p>
            <a href="../settings.php" class="btn">Back to Settings</a>
        <?php elseif ($type === 'error'): ?>
            <div class="icon error">✖</div>
            <h2>Error</h2>
            <p><?php echo $message; ?></p>
            <a href="../settings.php" class="btn">Try Again</a>
        <?php else: ?>
            <div class="icon error">⚠</div>
            <h2>Oops!</h2>
            <p>Something went wrong. Please try again.</p>
            <a href="../settings.php" class="btn">Back</a>
        <?php endif; ?>
    </div>

    <script>
        // Auto redirect after 3 seconds (optional)
        setTimeout(() => {
            window.location.href = "../settings.php";
        }, 3000);
    </script>
</body>
</html>
