<?php
session_start();
include('../db_connect.php');

// ✅ Security check
if (!isset($_SESSION['user_id']) || $_SESSION['pc_type'] !== 'Basic') {
    header("Location: basic_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1️⃣ End active sessions and calculate total_cost
$conn->query("
    UPDATE user_sessions 
    SET end_time = NOW(), status = 'ended'
    WHERE user_id = $user_id AND status = 'active'
");

// 1.5️⃣ Calculate total_cost for ended sessions
$settings = $conn->query("SELECT basic_rate FROM settings LIMIT 1")->fetch_assoc();
$rate_per_minute = $settings['basic_rate'] / 60;

$conn->query("
    UPDATE user_sessions 
    SET total_cost = TIMESTAMPDIFF(MINUTE, start_time, end_time) * $rate_per_minute
    WHERE user_id = $user_id AND status = 'ended' AND total_cost IS NULL
");

// 2️⃣ Free assigned PC
$res = $conn->query("SELECT active_pc_id FROM users WHERE user_id = $user_id");
if ($res && $res->num_rows > 0) {
    $pc_id = $res->fetch_assoc()['active_pc_id'];
    if (!empty($pc_id)) {
        $conn->query("UPDATE computers SET status='available' WHERE computer_id=$pc_id");
    }
}

// 3️⃣ Clear user’s active_pc_id
$conn->query("UPDATE users SET active_pc_id=NULL WHERE user_id=$user_id");

// 4️⃣ Include global cleanup
$cleanup = realpath(__DIR__ . '/../includes/cleanup_sessions.php');
if ($cleanup && file_exists($cleanup)) {
    include($cleanup);
}

// 5️⃣ Double-safety cleanup
$conn->query("UPDATE user_sessions SET status='ended' WHERE user_id=$user_id AND status='active'");
$conn->query("UPDATE users SET active_pc_id=NULL WHERE user_id=$user_id");
$conn->query("
    UPDATE computers c
    LEFT JOIN users u ON c.computer_id = u.active_pc_id
    SET c.status='available'
    WHERE u.user_id IS NULL AND c.status='in-use'
");

// 6️⃣ Destroy session
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Goodbye - Computer Shop</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{
  background:linear-gradient(135deg,#007BFF,#00C6FF);
  height:100vh;display:flex;flex-direction:column;
  justify-content:center;align-items:center;
  color:#fff;text-align:center;animation:fadeIn 1s ease-out forwards;
}
.logo{width:120px;height:120px;object-fit:contain;margin-bottom:25px;animation:popIn 1s ease-out forwards;}
h1{font-size:2rem;background:linear-gradient(90deg,#fff,#d9f1ff);
-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:10px;}
p{color:#e0f7ff;font-size:1.1rem;margin-bottom:25px;}
.fade-text{font-size:1rem;color:#cce5ff;opacity:0.8;}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
@keyframes popIn{from{opacity:0;transform:scale(0.8);}to{opacity:1;transform:scale(1);}}
@keyframes fadeOut{to{opacity:0;transform:translateY(-10px);}}
</style>
</head>
<body>

<img src="assetss/company.png" alt="Company Logo" class="logo">
<h1>Thank You for Using</h1>
<p><b>NetCafe Basic</b> — Your trusted workstation.</p>
<p class="fade-text">Redirecting you to login...</p>

<script>
setTimeout(() => {
  document.body.style.animation = "fadeOut 1s ease-out forwards";
  setTimeout(() => {
    window.location.href = "basic_login.php";
  }, 900);
}, 3000);
</script>

</body>
</html>
