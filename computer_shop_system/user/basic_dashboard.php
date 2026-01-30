<?php
session_start();
include('../db_connect.php');

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['pc_type'] != 'Basic') {
    header("Location: basic_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pc_id = $_SESSION['pc_id'];

// Fetch user info
$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

// Fetch rate & ad
$settings_query = $conn->query("SELECT basic_rate, ad_image, ad_link FROM settings LIMIT 1");
$settings = $settings_query->fetch_assoc();
$rate_per_hour = $settings['basic_rate'];
$rate_per_minute = $rate_per_hour / 60;
$ad_image = $settings['ad_image'];
$ad_link = $settings['ad_link'];

// Fetch active session
$session_result = $conn->query("SELECT * FROM user_sessions WHERE user_id = $user_id AND status='active' ORDER BY session_id DESC LIMIT 1");
$session = $session_result ? $session_result->fetch_assoc() : null;

if (!$session) {
    echo "<script>alert('Your session has ended or was terminated by the admin.'); window.location.href = 'basic_logout.php';</script>";
    exit();
}

$session_id = $session['session_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>💻 Basic PC Dashboard - Computer Shop System</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* {margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif;}

body {
    background: url('assets/bg.jpg') no-repeat center center/cover;
    background-attachment: fixed;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
}

.dashboard {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.25);
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    padding: 40px;
    width: 90%;
    max-width: 650px;
    text-align: center;
    animation: fadeIn 0.7s ease-out;
}

h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
    background: linear-gradient(90deg, #ff4d4d, #b30000);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
}

.user-info {
    margin-bottom: 20px;
    color: #ffcccc;
}

.info-card {
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 15px;
    margin: 10px 0;
    border-left: 4px solid #ff4d4d;
    animation: slideUp 0.5s ease;
}

.info-card span {
    font-size: 1.1rem;
    font-weight: 600;
}

#timeLeft {
    font-size: 1.6rem;
    font-weight: 700;
    color: #fff;
}

.ad-section img {
    width: 100%;
    max-width: 600px;
    border-radius: 10px;
    border: 2px solid #ff4d4d;
    box-shadow: 0 0 20px rgba(255,77,77,0.4);
    margin-bottom: 15px;
    animation: glow 2.5s ease-in-out infinite alternate;
}

.logout-btn {
    background: linear-gradient(90deg, #b30000, #ff4d4d);
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.logout-btn:hover {
    transform: scale(1.05);
    background: linear-gradient(90deg, #ff6666, #b30000);
}

/* Animations */
@keyframes fadeIn {
    from {opacity:0; transform:translateY(25px);}
    to {opacity:1; transform:translateY(0);}
}
@keyframes slideUp {
    from {opacity:0; transform:translateY(10px);}
    to {opacity:1; transform:translateY(0);}
}
@keyframes glow {
    from {box-shadow: 0 0 15px rgba(255,77,77,0.2);}
    to {box-shadow: 0 0 30px rgba(255,77,77,0.6);}
}
</style>
<script>
let balance = <?php echo $user['balance']; ?>;
const ratePerMin = <?php echo $rate_per_minute; ?>;
const userId = <?php echo $user_id; ?>;
const sessionId = <?php echo $session_id; ?>;
let timeLeft = balance / ratePerMin * 60;

function formatTime(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    return `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
}

function updateTimer() {
    if (balance <= 0 || timeLeft <= 0) {
        alert("Your time is up. Logging out...");
        window.location.href = "basic_logout.php";
        return;
    }
    balance -= ratePerMin;
    timeLeft -= 60;
    document.getElementById("balance").innerText = balance.toFixed(2);
    document.getElementById("timeLeft").innerText = formatTime(timeLeft);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "update_session.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("user_id=" + userId + "&session_id=" + sessionId + "&rate=" + ratePerMin);

    setTimeout(updateTimer, 60000);
}

function countdown() {
    if (timeLeft <= 0) {
        alert("Your time has expired.");
        window.location.href = "basic_logout.php";
        return;
    }
    timeLeft--;
    document.getElementById("timeLeft").innerText = formatTime(timeLeft);
    setTimeout(countdown, 1000);
}

function checkSessionStatus() {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "check_session_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status !== 'active') {
                alert("Your session has been ended by the admin.");
                window.location.href = "basic_logout.php";
            }
        }
    };
    xhr.send("user_id=" + userId + "&session_id=" + sessionId);
    setTimeout(checkSessionStatus, 5000);
}

window.onload = function() {
    document.getElementById("timeLeft").innerText = formatTime(timeLeft);
    updateTimer();
    countdown();
    checkSessionStatus();
};
</script>
</head>
<body>

<div class="dashboard">
    <h2>💻 Basic PC Dashboard</h2>
    <p class="user-info">Welcome, <b><?php echo htmlspecialchars($user['full_name']); ?></b></p>

    <?php if (!empty($ad_image) && file_exists("../admin/assets/uploads/" . $ad_image)): ?>
    <div class="ad-section">
        <a href="<?php echo !empty($ad_link) ? htmlspecialchars($ad_link) : '#'; ?>" target="_blank">
            <img src="../admin/assets/uploads/<?php echo htmlspecialchars($ad_image); ?>" alt="Advertisement">
        </a>
        <p><small>Advertisement powered by NetCafe</small></p>
    </div>
    <?php endif; ?>

    <div class="info-card"><span>💰 Balance:</span> ₱<span id="balance"><?php echo number_format($user['balance'],2); ?></span></div>
    <div class="info-card"><span>⏱ Rate:</span> ₱<?php echo number_format($rate_per_hour,2); ?>/hour</div>
    <div class="info-card"><span>🖥 PC Type:</span> Basic</div>
    <div class="info-card"><span>⏳ Time Left:</span> <span id="timeLeft">--:--:--</span></div>

    <form action="basic_logout.php" method="POST">
        <button type="submit" class="basic_logout-btn">🚪 Logout</button>
    </form>
</div>

</body>
</html>
