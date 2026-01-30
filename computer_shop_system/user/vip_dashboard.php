<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['pc_type'] != 'VIP') {
    header("Location: vip_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pc_id = $_SESSION['pc_id'];

$user = $conn->query("SELECT * FROM users WHERE user_id = $user_id")->fetch_assoc();

$settings_query = $conn->query("SELECT vip_rate, ad_image, ad_link, shop_name FROM settings LIMIT 1");
$settings = $settings_query->fetch_assoc();
$rate_per_hour = $settings['vip_rate'];
$rate_per_minute = $rate_per_hour / 60;
$ad_image = $settings['ad_image'];
$ad_link = $settings['ad_link'];
$shop_name = $settings['shop_name'] ?? "NetCafe Elite";

$session_result = $conn->query("SELECT * FROM user_sessions WHERE user_id = $user_id AND status='active' ORDER BY session_id DESC LIMIT 1");
$session = $session_result ? $session_result->fetch_assoc() : null;

if (!$session) {
    echo "<script>
        alert('Your session has ended or was terminated by the admin.');
        window.location.href = 'logout.php';
    </script>";
    exit();
}

$session_id = $session['session_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>💎 VIP Dashboard - <?php echo htmlspecialchars($shop_name); ?></title>
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
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* HEADER SECTION */
.header {
    text-align: center;
    margin-top: 40px;
}

.header img {
    width: 110px;
    height: 110px;
    object-fit: contain;
    margin-bottom: 10px;
}

.header h1 {
    font-size: 2rem;
    background: linear-gradient(90deg, #FFD700, #FFB700);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 10px;
}

.header p {
    color: #FFD700;
    font-size: 1.1rem;
    opacity: 0.9;
}

/* DASHBOARD GRID */
.dashboard {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 40px;
    margin: 60px 0;
}

/* HEXAGON CARD DESIGN */
.hex-card {
    position: relative;
    width: 180px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    clip-path: polygon(50% 0%, 93% 25%, 93% 75%, 50% 100%, 7% 75%, 7% 25%);
    border: 1px solid rgba(255, 215, 0, 0.3);
    backdrop-filter: blur(10px);
    text-align: center;
    padding: 30px 10px;
    box-shadow: 0 0 20px rgba(255, 215, 0, 0.2);
    transition: 0.3s ease;
}

.hex-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 30px rgba(255, 215, 0, 0.6);
}

.hex-card h3 {
    color: #FFD700;
    margin-bottom: 10px;
    font-size: 1.1rem;
}

.hex-card span {
    font-size: 1.4rem;
    font-weight: 600;
}

/* AD SECTION */
.ad-container {
    text-align: center;
    margin-bottom: 40px;
}

.ad-container img {
    width: 600px;
    max-width: 90%;
    border-radius: 12px;
    border: 1px solid rgba(255, 215, 0, 0.3);
    box-shadow: 0 0 25px rgba(255, 215, 0, 0.3);
}

.ad-container small {
    display: block;
    margin-top: 8px;
    color: #FFD700;
    font-size: 0.9rem;
}

/* BUTTON */
button {
    background: linear-gradient(90deg, #FFD700, #E6B800);
    border: none;
    color: #000;
    font-weight: 700;
    font-size: 1rem;
    padding: 14px 40px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 0 15px rgba(255, 215, 0, 0.4);
    margin-bottom: 50px;
}

button:hover {
    transform: scale(1.05);
    background: linear-gradient(90deg, #FFF3A1, #FFD700);
    box-shadow: 0 0 25px rgba(255, 215, 0, 0.6);
}

/* TIME TEXT */
#timeLeft {
    color: #FFD700;
    font-weight: 600;
}

/* RESPONSIVE */
@media (max-width: 700px) {
    .dashboard {
        gap: 25px;
    }
    .hex-card {
        width: 160px;
        height: 180px;
    }
}
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <img src="assets/company.png" alt="Company Logo">
    <h1><?php echo htmlspecialchars($shop_name); ?></h1>
    <p>VIP PC Lounge — Exclusive Access</p>
</div>

<!-- DASHBOARD HEX CARDS -->
<div class="dashboard">
    <div class="hex-card">
        <h3>Balance</h3>
        <span>₱<span id="balance"><?php echo number_format($user['balance'], 2); ?></span></span>
    </div>
    <div class="hex-card">
        <h3>Rate</h3>
        <span>₱<?php echo number_format($rate_per_hour, 2); ?>/hr</span>
    </div>
    <div class="hex-card">
        <h3>PC Type</h3>
        <span>VIP</span>
    </div>
    <div class="hex-card">
        <h3>Time Left</h3>
        <span id="timeLeft">--:--:--</span>
    </div>
</div>

<!-- ADVERTISEMENT -->
<?php if (!empty($ad_image) && file_exists("../admin/assets/uploads/" . $ad_image)): ?>
<div class="ad-container">
    <a href="<?php echo !empty($ad_link) ? htmlspecialchars($ad_link) : '#'; ?>" target="_blank">
        <img src="../admin/assets/uploads/<?php echo htmlspecialchars($ad_image); ?>" alt="Advertisement">
    </a>
    <small>Advertisement powered by NetCafe Elite</small>
</div>
<?php endif; ?>

<!-- LOGOUT BUTTON -->
<form action="vip_logout.php" method="POST">
    <button type="submit">Logout</button>
</form>

<script>
let balance = <?php echo $user['balance']; ?>;
const ratePerMin = <?php echo $rate_per_minute; ?>;
const userId = <?php echo $user_id; ?>;
const sessionId = <?php echo $session_id; ?>;

let timeLeft = balance / ratePerMin * 60; // seconds

function formatTime(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

function updateTimer() {
    if (balance <= 0 || timeLeft <= 0) {
        alert("Your time is up. Logging out...");
        window.location.href = "vip_logout.php";
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
        window.location.href = "vip_logout.php";
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
                window.location.href = "vip_logout.php";
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
</body>
</html>
