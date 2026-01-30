<?php
session_start();
include('../db_connect.php');

// must be admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ---- handle search ----
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// ---- fetch users (non-archived only) ----
$sql = "SELECT * FROM users WHERE role_id = 2 AND is_archived = 0";
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (full_name LIKE '%$s%' OR email LIKE '%$s%')";
}
$sql .= " ORDER BY full_name ASC";
$users = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>👥 Manage Users - Admin Panel</title>
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

    /* MAIN */
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
        max-width: 1200px;
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

    /* TOP BUTTONS */
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

    /* SEARCH BAR */
    .search-bar {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-bottom: 25px;
    }

    .search-bar input {
        padding: 10px 15px;
        border-radius: 8px;
        border: none;
        outline: none;
        font-size: 1rem;
        width: 280px;
        background: rgba(255, 255, 255, 0.9);
        color: #222;
    }

    .search-bar button {
        background: linear-gradient(90deg, #b30000, #ff4d4d);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-bar button:hover {
        transform: scale(1.05);
        background: linear-gradient(90deg, #ff6666, #b30000);
    }

    .search-bar a {
        color: #fff;
        text-decoration: underline;
        font-size: 0.9rem;
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

    /* ACTION BUTTONS */
    td form {
        display: inline-block;
        margin: 2px;
    }

    td input[type="number"] {
        padding: 6px;
        border-radius: 6px;
        border: none;
        width: 70px;
        text-align: center;
    }

    td button {
        background: linear-gradient(90deg, #b30000, #ff4d4d);
        border: none;
        color: #fff;
        border-radius: 6px;
        padding: 6px 10px;
        cursor: pointer;
        transition: 0.3s;
        font-size: 0.9rem;
    }

    td button:hover {
        transform: scale(1.1);
        background: linear-gradient(90deg, #ff6666, #b30000);
    }

    .footer {
        text-align: center;
        margin-top: 25px;
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.9);
    }

    @media (max-width: 768px) {
        .table-container { padding: 20px; }
        th, td { font-size: 0.85rem; padding: 10px; }
        .search-bar { flex-direction: column; gap: 8px; }
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
        <h3>👥 Manage Users</h3>

        <!-- 🔗 Navigation Links -->
        <div class="nav-links">
            <a href="add_user.php">➕ Add User</a>
            <a href="topup_history.php">💰 Top-up History</a>
            <a href="archived_users.php">📦 Archived Users</a>
        </div>

        <!-- 🔍 Search -->
        <form class="search-bar" method="GET" action="">
            <input type="text" name="search" placeholder="Search name or email..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <a href="manage_users.php">Clear</a>
        </form>

        <table>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Balance (₱)</th>
                <th>Active PC</th>
                <th>Actions</th>
            </tr>
            <?php
            $i = 1;
            while ($row = $users->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo number_format($row['balance'], 2); ?></td>
                <td>
                    <?php
                    if ($row['active_pc_id']) {
                        $pc = $conn->query("SELECT computer_name FROM computers WHERE computer_id={$row['active_pc_id']}");
                        echo ($pc && $pc->num_rows > 0) ? $pc->fetch_assoc()['computer_name'] : 'Unknown';
                    } else echo "<i>None</i>";
                    ?>
                </td>
                <td>
                    <!-- Add Credits -->
                    <form action="add_credits.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <input type="number" name="amount" min="1" step="0.01" placeholder="₱" required>
                        <button type="submit" title="Add Credits">💰</button>
                    </form>

                    <!-- Reset Password -->
                    <form action="actions/reset_password.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" title="Reset Password">🔑</button>
                    </form>

                    <!-- Logout User -->
                    <form action="actions/logout_user.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" title="Force Logout" onclick="return confirm('Force logout this user?');">🚪</button>
                    </form>

                    <!-- Archive User -->
                    <form action="actions/archive_user.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" title="Archive User" onclick="return confirm('Archive this user?');">📦</button>
                    </form>

                    <!-- Delete User -->
                    <form action="actions/delete_user.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" title="Delete User" style="background:#cc0000;" onclick="return confirm('⚠️ Permanently delete this user and all data?');">🗑️</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</main>

</body>
</html>
