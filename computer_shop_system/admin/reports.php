<?php
session_start();
include('../db_connect.php');

// Admin session check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Get report type from URL parameter
$report_type = isset($_GET['type']) ? $_GET['type'] : 'daily';

// Set date ranges based on report type
$today = date("Y-m-d");
$current_month = date("m");
$current_year = date("Y");

if ($report_type == 'daily') {
    $date_filter = "DATE(s.start_time) = '$today'";
    $topup_filter = "DATE(topup_date) = '$today'";
    $period_label = "Today";
} elseif ($report_type == 'weekly') {
    $date_filter = "s.start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $topup_filter = "topup_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $period_label = "Last 7 Days";
} elseif ($report_type == 'monthly') {
    $date_filter = "MONTH(s.start_time) = '$current_month' AND YEAR(s.start_time) = '$current_year'";
    $topup_filter = "MONTH(topup_date) = '$current_month' AND YEAR(topup_date) = '$current_year'";
    $period_label = "This Month";
}

// TOTAL SALES (Session Income)
$sales_query = "SELECT SUM(
    CASE WHEN s.status = 'ended' THEN s.total_cost
    ELSE TIMESTAMPDIFF(MINUTE, s.start_time, NOW()) *
         (CASE WHEN ct.type_name = 'VIP' THEN st.vip_rate ELSE st.basic_rate END) / 60
    END
) AS total FROM user_sessions s
JOIN computers c ON s.computer_id = c.computer_id
JOIN computer_types ct ON c.type_id = ct.type_id
JOIN settings st ON 1=1
WHERE $date_filter";
$total_sales = $conn->query($sales_query)->fetch_assoc()['total'] ?? 0;

// TOTAL TOP-UP INCOME
$topup_query = "SELECT SUM(amount) AS total FROM topup_history WHERE $topup_filter";
$total_topup = $conn->query($topup_query)->fetch_assoc()['total'] ?? 0;

// TOTAL USERS (Active users in period)
$users_query = "SELECT COUNT(DISTINCT s.user_id) AS total FROM user_sessions s WHERE $date_filter";
$total_users = $conn->query($users_query)->fetch_assoc()['total'] ?? 0;

// TOTAL COMPUTERS USED
$computers_query = "SELECT COUNT(DISTINCT s.computer_id) AS total FROM user_sessions s WHERE $date_filter";
$total_computers = $conn->query($computers_query)->fetch_assoc()['total'] ?? 0;

// TOTAL SESSIONS
$sessions_query = "SELECT COUNT(*) AS total FROM user_sessions s WHERE $date_filter";
$total_sessions = $conn->query($sessions_query)->fetch_assoc()['total'] ?? 0;

// AVERAGE SPENDING PER USER
$avg_spending = $total_users > 0 ? $total_sales / $total_users : 0;

// TOP SPENDERS
$top_spenders_query = "SELECT u.full_name, u.email,
                    SUM(
                        CASE WHEN s.status = 'ended' THEN s.total_cost
                        ELSE TIMESTAMPDIFF(MINUTE, s.start_time, NOW()) *
                             (CASE WHEN ct.type_name = 'VIP' THEN st.vip_rate ELSE st.basic_rate END) / 60
                        END
                    ) AS total_spent,
                    COUNT(s.session_id) AS session_count
                    FROM user_sessions s
                    JOIN users u ON s.user_id = u.user_id
                    JOIN computers c ON s.computer_id = c.computer_id
                    JOIN computer_types ct ON c.type_id = ct.type_id
                    JOIN settings st ON 1=1
                    WHERE $date_filter
                    GROUP BY u.user_id
                    ORDER BY total_spent DESC
                    LIMIT 10";
$top_spenders = $conn->query($top_spenders_query);

// SESSION BREAKDOWN BY PC TYPE
$session_breakdown_query = "SELECT ct.type_name,
                           COUNT(s.session_id) AS session_count,
                           SUM(
                               CASE WHEN s.status = 'ended' THEN s.total_cost
                               ELSE TIMESTAMPDIFF(MINUTE, s.start_time, NOW()) *
                                    (CASE WHEN ct.type_name = 'VIP' THEN st.vip_rate ELSE st.basic_rate END) / 60
                               END
                           ) AS revenue
                           FROM user_sessions s
                           JOIN computers c ON s.computer_id = c.computer_id
                           JOIN computer_types ct ON c.type_id = ct.type_id
                           JOIN settings st ON 1=1
                           WHERE $date_filter
                           GROUP BY ct.type_name";
$session_breakdown = $conn->query($session_breakdown_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📊 Reports - Computer Shop System</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    background: url('assets/company/bgadmin.png') no-repeat center center / cover;
    background-attachment: fixed;
    min-height: 100vh;
    color: #fff;
    display: flex;
    flex-direction: column;
}

/* HEADER */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 50px;
    background: rgba(0, 0, 0, 0.65);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(6px);
    position: sticky;
    top: 0;
    z-index: 10;
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

.back-btn {
    background: linear-gradient(90deg, #b30000, #ff4d4d);
    color: #fff;
    padding: 10px 22px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: linear-gradient(90deg, #ff6666, #b30000);
    transform: scale(1.05);
}

/* MAIN */
main {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 20px;
}

/* REPORT BUTTONS */
.report-buttons {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
}

.report-btn {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    padding: 15px 30px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    border: 1px solid rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(8px);
    transition: all 0.3s ease;
}

.report-btn:hover, .report-btn.active {
    background: linear-gradient(90deg, #ff4d4d, #b30000);
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.report-header {
    text-align: center;
    margin-bottom: 30px;
}

.report-header h4 {
    font-size: 1.5rem;
    color: #fff;
    background: linear-gradient(90deg, #ff4d4d, #b30000);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
}

/* CARD GRID */
.cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    margin-bottom: 40px;
}

.card {
    background: rgba(255, 255, 255, 0.12);
    border-radius: 18px;
    padding: 25px 35px;
    width: 280px;
    text-align: center;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.6);
}

.card h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: #ffd4d4;
}

.card p {
    font-size: 1.4rem;
    font-weight: 700;
    color: #fff;
}

/* TABLE SECTION */
.table-container {
    background: rgba(255, 255, 255, 0.12);
    border-radius: 20px;
    padding: 40px 50px;
    width: 95%;
    max-width: 1100px;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    overflow-x: auto;
}

h3.section-title {
    text-align: center;
    font-size: 1.8rem;
    margin-bottom: 25px;
    background: linear-gradient(90deg, #ff4d4d, #b30000);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 15px;
    overflow: hidden;
}
th, td {
    padding: 14px 10px;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}
th {
    background: linear-gradient(90deg, rgba(255,77,77,0.8), rgba(179,0,0,0.8));
    color: #fff;
    font-weight: 600;
}
tr:nth-child(even) {
    background: rgba(255, 255, 255, 0.08);
}
tr:hover {
    background: rgba(255, 77, 77, 0.2);
    transition: 0.3s;
}

/* Highlight top spender */
tr:first-child td {
    background: rgba(255, 215, 0, 0.3);
    font-weight: 700;
    color: #fff9b1;
}

footer {
    text-align: center;
    margin-top: 30px;
    font-size: 0.9rem;
    opacity: 0.85;
}
</style>
</head>
<body>

<header>
    <div class="header-left">
        <img src="assets/company/company.png" alt="Company Logo">
        <h2>Computer Shop System</h2>
    </div>
    <a href="admin_dashboard.php" class="back-btn">🏠 Dashboard</a>
</header>

<main>
    <!-- Report Type Buttons -->
    <div class="report-buttons">
        <a href="?type=daily" class="report-btn <?php echo $report_type == 'daily' ? 'active' : ''; ?>">📅 Daily Report</a>
        <a href="?type=weekly" class="report-btn <?php echo $report_type == 'weekly' ? 'active' : ''; ?>">📆 Weekly Report</a>
        <a href="?type=monthly" class="report-btn <?php echo $report_type == 'monthly' ? 'active' : ''; ?>">🗓️ Monthly Report</a>
    </div>

    <!-- Report Header -->
    <div class="report-header">
        <h4><?php echo $period_label; ?> Report</h4>
    </div>

    <!-- Key Metrics Cards -->
    <div class="cards">
        <div class="card">
            <h3>💰 Total Sales</h3>
            <p>₱<?php echo number_format($total_sales, 2); ?></p>
        </div>
        <div class="card">
            <h3>💳 Top-up Income</h3>
            <p>₱<?php echo number_format($total_topup, 2); ?></p>
        </div>
        <div class="card">
            <h3>👥 Total Users</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="card">
            <h3>💻 Computers Used</h3>
            <p><?php echo $total_computers; ?></p>
        </div>
        <div class="card">
            <h3>🧾 Total Sessions</h3>
            <p><?php echo $total_sessions; ?></p>
        </div>
        <div class="card">
            <h3>📊 Avg Spending/User</h3>
            <p>₱<?php echo number_format($avg_spending, 2); ?></p>
        </div>
    </div>

    <!-- Top Spenders Table -->
    <div class="table-container">
        <h3 class="section-title">🏅 Top 10 Spenders (<?php echo $period_label; ?>)</h3>

        <?php if ($top_spenders && $top_spenders->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Total Spent (₱)</th>
                        <th>Sessions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    while ($row = $top_spenders->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>₱<?php echo number_format($row['total_spent'], 2); ?></td>
                            <td><?php echo $row['session_count']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;"><i>No user session data for this period.</i></p>
        <?php endif; ?>
    </div>

    <!-- Session Breakdown Table -->
    <div class="table-container">
        <h3 class="section-title">📈 Session Breakdown by PC Type (<?php echo $period_label; ?>)</h3>

        <?php if ($session_breakdown && $session_breakdown->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>PC Type</th>
                        <th>Sessions</th>
                        <th>Revenue (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $session_breakdown->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                            <td><?php echo $row['session_count']; ?></td>
                            <td>₱<?php echo number_format($row['revenue'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;"><i>No session data for this period.</i></p>
        <?php endif; ?>
    </div>

    <footer>
        <p>📅 Report generated on <?php echo date("F j, Y g:i A"); ?> | Period: <?php echo $period_label; ?></p>
    </footer>
</main>

</body>
</html>
