<?php
session_start();
include('../db_connect.php');

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all top-up records (join users table)
$query = "
    SELECT t.*, u.full_name, u.email
    FROM topup_history t
    JOIN users u ON t.user_id = u.user_id
    ORDER BY t.date_added DESC
";
$result = $conn->query($query);

// Compute total top-up amount
$total_query = $conn->query("SELECT SUM(amount) AS total_topup FROM topup_history");
$total_amount = 0;
if ($total_query && $total_query->num_rows > 0) {
    $total_amount = $total_query->fetch_assoc()['total_topup'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>💰 Top-up History - Computer Shop System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
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

        /* HEADER */
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

        /* MAIN CONTENT */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px;
        }

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

        h3 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 25px;
            background: linear-gradient(90deg, #ff4d4d, #b30000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        /* NAV BUTTONS */
        .nav-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 25px;
        }

        .nav-links a {
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            transform: scale(1.08);
            background: linear-gradient(90deg, #ff6666, #b30000);
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;
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
            font-size: 1rem;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.08);
        }

        tr:hover {
            background: rgba(255, 77, 77, 0.2);
            transition: 0.3s;
        }

        .total {
            font-weight: bold;
            background: rgba(255, 77, 77, 0.4);
        }

        .total td {
            color: #fff;
        }

        @media (max-width: 768px) {
            .table-container { padding: 20px; }
            th, td { font-size: 0.85rem; padding: 10px; }
            .nav-links { flex-direction: column; }
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
    <div class="table-container">
        <h3>💰 Top-up History</h3>

        <!-- NAV LINKS -->
        <div class="nav-links">
            <a href="manage_users.php">👥 Manage Users</a>
            <a href="archived_users.php">📦 Archived Users</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Amount (₱)</th>
                    <th>Date Added</th>
                </tr>
                <?php 
                $i = 1;
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo date("F j, Y g:i A", strtotime($row['date_added'])); ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="total">
                    <td colspan="3" style="text-align:right;">Total Credits Added:</td>
                    <td colspan="2">₱<?php echo number_format($total_amount, 2); ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p style="text-align:center; color:#fff; opacity:0.9;"><i>No top-up records found.</i></p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
