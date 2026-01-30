<?php
session_start();
include('../db_connect.php');

// ✅ Admin check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Fetch all sessions
$sql = "SELECT 
            s.session_id,
            u.full_name AS user_name,
            u.email,
            c.computer_name,
            ct.type_name,
            s.start_time,
            s.end_time,
            s.duration_minutes,
            s.total_cost,
            s.status
        FROM user_sessions s
        JOIN users u ON s.user_id = u.user_id
        JOIN computers c ON s.computer_id = c.computer_id
        JOIN computer_types ct ON c.type_id = ct.type_id
        ORDER BY s.session_id DESC";
$result = $conn->query($sql);

// ✅ Fetch shop name for header
$shop_name = "Computer Shop System";
$set = $conn->query("SELECT shop_name FROM settings LIMIT 1");
if ($set && $set->num_rows > 0) {
    $shop_name = $set->fetch_assoc()['shop_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🖥️ Session Monitoring - <?php echo htmlspecialchars($shop_name); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* {margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif;}
body {
    background: url('assets/company/bgadmin.png') no-repeat center center/cover;
    background-attachment: fixed;
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
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
    padding: 40px 50px;
    width: 100%;
    max-width: 1100px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    animation: fadeIn 0.8s ease;
}

/* ==== TABLE ==== */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    border-radius: 10px;
    overflow: hidden;
}
th, td {
    padding: 12px 10px;
    text-align: center;
    font-size: 0.95rem;
}
th {
    background: linear-gradient(90deg, #ff4d4d, #b30000);
    font-weight: 600;
}
tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.05);
}
tr:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* VIP highlight */
.vip-row {
    background: rgba(255, 215, 0, 0.15);
    box-shadow: inset 0 0 10px rgba(255, 215, 0, 0.3);
}

/* Status colors */
.status-active {
    color: #FFD700;
    font-weight: 700;
}
.status-ended {
    color: #00FF88;
    font-weight: 700;
}
.status-paused {
    color: #ccc;
    font-weight: 700;
}

/* Back link */
.back-link {
    display: inline-block;
    background: linear-gradient(90deg, #b30000, #ff4d4d);
    color: #fff;
    padding: 8px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}
.back-link:hover {
    background: linear-gradient(90deg, #ff6666, #b30000);
    transform: scale(1.05);
}

/* ==== FOOTER ==== */
.footer {
    text-align: center;
    margin-top: 40px;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
}

/* Animation */
@keyframes fadeIn {
    from {opacity:0; transform: translateY(20px);}
    to {opacity:1; transform: translateY(0);}
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
        <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
        <h2 style="text-align:center; margin-bottom:10px;">🖥️ Session Monitoring</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>User</th>
                        <th>Computer</th>
                        <th>Type</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration (mins)</th>
                        <th>Total Cost (₱)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
                        $is_vip = strtolower($row['type_name']) === 'vip';
                    ?>
                    <tr class="<?php echo $is_vip ? 'vip-row' : ''; ?>">
                        <td><?php echo $row['session_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?><br><small><?php echo htmlspecialchars($row['email']); ?></small></td>
                        <td><?php echo htmlspecialchars($row['computer_name']); ?></td>
                        <td>
                            <?php echo $is_vip 
                                ? "<span style='color:#FFD700;font-weight:600;'>VIP</span>" 
                                : "<span style='color:#ff9999;'>Basic</span>"; ?>
                        </td>
                        <td><?php echo date("M d, Y g:i A", strtotime($row['start_time'])); ?></td>
                        <td>
                            <?php echo $row['end_time'] 
                                ? date("M d, Y g:i A", strtotime($row['end_time'])) 
                                : "<i style='color:#ccc;'>Running...</i>"; ?>
                        </td>
                        <td><?php echo $row['duration_minutes']; ?></td>
                        <td>₱<?php echo number_format($row['total_cost'], 2); ?></td>
                        <td>
                            <?php
                                if ($row['status'] === 'active') {
                                    echo "<span class='status-active'>Active</span>";
                                } elseif ($row['status'] === 'ended') {
                                    echo "<span class='status-ended'>Ended</span>";
                                } else {
                                    echo "<span class='status-paused'>Paused</span>";
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:#fff; margin-top:20px;">No session records found.</p>
        <?php endif; ?>

        <div class="footer">
            System version 1.0 — Developed By Ralph Jay P. Maano
        </div>
    </div>
</main>

</body>
</html>
