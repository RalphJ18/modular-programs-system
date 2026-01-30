<?php
include('../includes/cleanup_sessions.php');
session_start();
include('../db_connect.php');

// If logged in but not as VIP, log out first
if (isset($_SESSION['user_id']) && $_SESSION['pc_type'] !== 'VIP') {
    session_unset();
    session_destroy();
    header("Location: vip_login.php");
    exit();
}

// If already logged in as VIP, go straight to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['pc_type'] === 'VIP') {
    header("Location: vip_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE email = ? AND role_id = 2 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $active_session_check = $conn->query("
                SELECT * FROM user_sessions 
                WHERE user_id = {$user['user_id']} AND status = 'active' 
                LIMIT 1
            ");

            if ($user['active_pc_id'] !== NULL || ($active_session_check && $active_session_check->num_rows > 0)) {
                echo "<script>alert('You are already logged in on another PC. Please log out first.'); window.location.href='vip_login.php';</script>";
                exit();
            }

            // Find available VIP PC
            $pc_query = "SELECT * FROM computers WHERE type_id = 2 AND status = 'available' LIMIT 1";
            $pc_result = $conn->query($pc_query);

            if ($pc_result && $pc_result->num_rows > 0) {
                $pc = $pc_result->fetch_assoc();
                $pc_id = $pc['computer_id'];

                $conn->query("UPDATE computers SET status='in-use' WHERE computer_id=$pc_id");
                $conn->query("UPDATE users SET active_pc_id=$pc_id WHERE user_id={$user['user_id']}");
                $conn->query("INSERT INTO user_sessions (user_id, computer_id, start_time, status) 
                              VALUES ({$user['user_id']}, $pc_id, NOW(), 'active')");

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['pc_id'] = $pc_id;
                $_SESSION['pc_type'] = 'VIP';

                header("Location: vip_dashboard.php");
                exit();
            } else {
                echo "<script>alert('No available VIP computer at the moment.'); window.location.href='vip_login.php';</script>";
            }
        } else {
            echo "<script>alert('Incorrect password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('User not found.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>💎 VIP PC Login - NetCafe System</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background: url('assets/vipbg.jpg') no-repeat center center/cover;
    background-attachment: fixed;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
}

/* Container */
.login-container {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 215, 0, 0.4);
    border-radius: 20px;
    padding: 50px 60px;
    width: 90%;
    max-width: 420px;
    text-align: center;
    box-shadow: 0 0 25px rgba(255, 215, 0, 0.2);
    animation: fadeIn 0.8s ease-out;
}

/* Header */
h2 {
    font-size: 1.8rem;
    font-weight: 700;
    background: linear-gradient(90deg, #FFD700, #FFB700);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 30px;
}

/* Input Fields */
input[type="email"], input[type="password"] {
    width: 100%;
    padding: 14px;
    margin: 10px 0 20px;
    border: none;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

input[type="email"]:focus, input[type="password"]:focus {
    background: rgba(255, 255, 255, 0.25);
    border: 1px solid #FFD700;
}

/* Premium Button */
button {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(90deg, #FFD700, #E6B800);
    color: #000;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
}

button:hover {
    transform: scale(1.05);
    background: linear-gradient(90deg, #FFF5B0, #FFD700);
    box-shadow: 0 0 25px rgba(255, 215, 0, 0.6);
}

/* Footer note */
.note {
    color: #FFD700;
    font-size: 0.9rem;
    margin-top: 25px;
    opacity: 0.9;
}

/* Animations */
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(25px);}
    to {opacity: 1; transform: translateY(0);}
}
</style>
</head>
<body>

<div class="login-container">
    <h2>VIP PC Login</h2>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Enter VIP Lounge</button>
    </form>

    <p class="note">For VIP PCs only — Exclusive Access Area</p>
</div>

</body>
</html>
