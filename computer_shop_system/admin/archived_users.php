<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$res = $conn->query("SELECT * FROM users WHERE role_id=2 AND is_archived=1 ORDER BY full_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📦 Archived Users - Computer Shop System</title>
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
        max-width: 1000px;
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

    td form {
        display: inline-block;
        margin: 2px;
    }

    td button {
        background: linear-gradient(90deg, #b30000, #ff4d4d);
        border: none;
        color: #fff;
        border-radius: 6px;
        padding: 8px 12px;
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
    }
</style>
</head>
<body>

<header>
    <div class="header-left">
        <img src="assets/company/company.png" alt="Company Logo">
        <h2>Computer Shop System</h2>
    </div>
    <a href="manage_users.php" class="back-btn">⬅ Back to Manage Users</a>
</header>

<main>
    <div class="table-container">
        <h3>📦 Archived Users</h3>

        <table>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Balance (₱)</th>
                <th>Restore</th>
            </tr>
            <?php $i = 1; while ($u = $res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><?php echo number_format($u['balance'], 2); ?></td>
                <td>
                    <form action="actions/restore_user.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $u['user_id']; ?>">
                        <button type="submit" onclick="return confirm('Restore this user?');">♻️ Restore</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</main>

</body>
</html>
