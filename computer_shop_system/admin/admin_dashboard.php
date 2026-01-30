<?php
session_start();
include('../db_connect.php');

// ✅ Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Default values
$shop_name = "Computer Shop System";
$basic_rate = 29.00;
$vip_rate = 49.00;

// ✅ Fetch settings safely
$sql = "SELECT shop_name, basic_rate, vip_rate FROM settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $shop_name = $row['shop_name'];
    $basic_rate = $row['basic_rate'];
    $vip_rate = $row['vip_rate'];
}

// ✅ Safe counts
$total_users = 0;
$total_pcs = 0;
$total_sessions = 0;
$daily_income = 0;

$res_users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role_id = 2");
if ($res_users && $res_users->num_rows > 0) {
    $total_users = (int)$res_users->fetch_assoc()['total'];
}

$res_pcs = $conn->query("SELECT COUNT(*) AS total FROM computers");
if ($res_pcs && $res_pcs->num_rows > 0) {
    $total_pcs = (int)$res_pcs->fetch_assoc()['total'];
}

$res_sessions = $conn->query("SELECT COUNT(*) AS total FROM user_sessions WHERE status='active'");
if ($res_sessions && $res_sessions->num_rows > 0) {
    $total_sessions = (int)$res_sessions->fetch_assoc()['total'];
}

$today = date("Y-m-d");
$res_income = $conn->query("
    SELECT SUM(
        CASE WHEN s.status = 'ended' THEN s.total_cost
        ELSE TIMESTAMPDIFF(MINUTE, s.start_time, NOW()) * 
             (CASE WHEN ct.type_name = 'VIP' THEN st.vip_rate ELSE st.basic_rate END) / 60
        END
    ) AS total_income
    FROM user_sessions s
    JOIN computers c ON s.computer_id = c.computer_id
    JOIN computer_types ct ON c.type_id = ct.type_id
    JOIN settings st ON 1=1
    WHERE DATE(s.start_time) = '$today'
");
if ($res_income && $res_income->num_rows > 0) {
    $daily_income = (float)($res_income->fetch_assoc()['total_income'] ?? 0);
}

// Daily top-up income
$res_topup = $conn->query("
    SELECT SUM(amount) AS total_topup
    FROM topup_history
    WHERE DATE(topup_date) = '$today'
");
if ($res_topup && $res_topup->num_rows > 0) {
    $daily_topup = (float)($res_topup->fetch_assoc()['total_topup'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($shop_name); ?> - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ==== RESET ==== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: url('assets/company/bgadmin.png') no-repeat center center/cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #fff;
        }

        /* ==== HEADER ==== */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 50px;
            background: rgba(0, 0, 0, 0.65);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(6px);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-left img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }

        .header-left h2 {
            font-size: 2rem;
            background: linear-gradient(90deg, #ff4d4d, #b30000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .logout {
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            color: #fff;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout:hover {
            background: linear-gradient(90deg, #ff6666, #b30000);
            transform: scale(1.05);
        }

        /* ==== MAIN CONTENT ==== */
        main {
            flex: 1;
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 60px;
            width: 100%;
            max-width: 1000px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .welcome {
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        /* ==== STATS CARDS ==== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
            justify-items: center;
        }

        .stat-card {
            position: relative;
            background: linear-gradient(145deg, #ff4d4d, #b30000);
            border-radius: 10px;
            padding: 25px 15px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            transform: skew(-10deg);
            transition: all 0.3s ease;
            width: 200px;
        }

        .stat-card:hover {
            transform: skew(-10deg) scale(1.05);
        }

        .stat-card h4,
        .stat-card p {
            transform: skew(10deg);
        }

        .stat-card h4 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            opacity: 0.9;
        }

        .stat-card p {
            font-size: 1.6rem;
            font-weight: 700;
        }

        /* Centered Today's Income */
        .stats-grid .stat-card:last-child {
            grid-column: 1 / -1;
            justify-self: center;
            width: 250px;
        }

        /* ==== NAVIGATION GRID ==== */
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }

        .nav-card {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            text-align: center;
            padding: 25px;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
        }

        .nav-card:hover {
            background: linear-gradient(145deg, rgba(255, 77, 77, 0.9), rgba(179, 0, 0, 0.9));
            transform: scale(1.05);
        }

        .nav-card a {
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
        }

        hr {
            margin: 30px 0;
            border: none;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <img src="assets/company/company.png" alt="Company Logo">
            <h2><?php echo htmlspecialchars($shop_name); ?></h2>
        </div>
        <a href="admin_logout.php" class="logout">🚪 Logout</a>
    </header>

    <main>
        <div class="dashboard-card">
            <p class="welcome">Welcome, <b><?php echo htmlspecialchars($_SESSION['admin_name']); ?></b></p>

            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Registered Users</h4>
                    <p><?php echo $total_users; ?></p>
                </div>
                <div class="stat-card">
                    <h4>Total Computers</h4>
                    <p><?php echo $total_pcs; ?></p>
                </div>
                <div class="stat-card">
                    <h4>Active Sessions</h4>
                    <p><?php echo $total_sessions; ?></p>
                </div>
                <div class="stat-card">
                    <h4>Today's Spending</h4>
                    <p>₱<?php echo number_format($daily_income, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h4>Today's Top-up</h4>
                    <p>₱<?php echo number_format($daily_topup, 2); ?></p>
                </div>
            </div>

            <hr>

            <div class="nav-grid">
                <div class="nav-card"><a href="add_user.php">➕ Add New User</a></div>
                <div class="nav-card"><a href="manage_users.php">👥 Manage Users</a></div>
                <div class="nav-card"><a href="computers.php">💻 Computer Management</a></div>
                <div class="nav-card"><a href="sessions.php">⏱️ Session Monitoring</a></div>
                <div class="nav-card"><a href="reports.php">📑 Reports</a></div>
                <div class="nav-card"><a href="settings.php">⚙️ Settings</a></div>
            </div>

            <hr>
            <p style="text-align:center;"><b>Current Rates:</b> Basic ₱<?php echo number_format($basic_rate, 2); ?>/hr | VIP ₱<?php echo number_format($vip_rate, 2); ?>/hr</p>

            <div class="footer">
                System version 1.0 — Developed By Ralph Jay P. Maano
            </div>
        </div>
    </main>

</body>
</html>
