<?php
session_start();
include('../db_connect.php');

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch computer types for dropdown
$types_sql = "SELECT type_id, type_name FROM computer_types ORDER BY type_name ASC";
$types_result = $conn->query($types_sql);

// Fetch all computers with types and current users
$sql = "SELECT c.computer_id, c.computer_name, c.status, ct.type_name, c.type_id, u.full_name AS logged_user
        FROM computers c
        JOIN computer_types ct ON c.type_id = ct.type_id
        LEFT JOIN users u ON u.active_pc_id = c.computer_id
        ORDER BY c.computer_id ASC";
$result = $conn->query($sql);

// Handle messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>💻 Computer Management - Computer Shop System</title>
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

        /* STATUS BADGES */
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
        }

        .status.available { background: #28a745; }
        .status.in-use { background: #ff9800; }
        .status.offline { background: #d9534f; }

        /* VIP HIGHLIGHT */
        .vip-row {
            background: rgba(255, 215, 0, 0.25) !important;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from { box-shadow: 0 0 10px rgba(255, 215, 0, 0.3); }
            to { box-shadow: 0 0 25px rgba(255, 215, 0, 0.6); }
        }

        @media (max-width: 768px) {
            .table-container { padding: 20px; }
            th, td { font-size: 0.9rem; padding: 10px; }
        }

        /* FORM SECTION */
        .form-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-section h4 {
            margin-bottom: 15px;
            color: #fff;
        }

        .form-section form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .form-section input, .form-section select {
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 0.9rem;
        }

        .form-section input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-section button {
            background: linear-gradient(90deg, #28a745, #20c997);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .form-section button:hover {
            transform: scale(1.05);
        }

        /* MESSAGES */
        .message {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .message.success {
            background: rgba(40, 167, 69, 0.8);
            color: #fff;
        }

        .message.error {
            background: rgba(220, 53, 69, 0.8);
            color: #fff;
        }

        /* EDIT BUTTON */
        .edit-btn {
            background: linear-gradient(90deg, #ffc107, #fd7e14);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .edit-btn:hover {
            transform: scale(1.05);
        }

        /* DELETE BUTTON */
        .delete-btn {
            background: linear-gradient(90deg, #dc3545, #c82333);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            margin-left: 5px;
        }

        .delete-btn:hover {
            transform: scale(1.05);
        }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            margin: 15% auto;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        .modal h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .modal form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .modal input, .modal select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .modal button {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modal button:hover {
            transform: scale(1.05);
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
        <h3>💻 Computer Management</h3>

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Add Computer Form -->
        <div class="form-section">
            <h4>Add New Computer</h4>
            <form action="actions/add_computer.php" method="POST">
                <input type="text" name="computer_name" placeholder="Computer Name (e.g., PC06)" required>
                <select name="type_id" required>
                    <option value="">Select Type</option>
                    <?php while ($type = $types_result->fetch_assoc()): ?>
                        <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">➕ Add Computer</button>
            </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Computer Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Current User</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?php echo ($row['type_name'] === 'VIP') ? 'vip-row' : ''; ?>">
                            <td><?php echo $row['computer_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['computer_name']); ?></td>
                            <td>
                                <?php
                                    echo ($row['type_name'] === 'VIP')
                                        ? "<b style='color:#ffd700;'>🌟 VIP</b>"
                                        : htmlspecialchars($row['type_name']);
                                ?>
                            </td>
                            <td>
                                <?php
                                    if ($row['status'] === 'available') {
                                        echo "<span class='status available'>Available</span>";
                                    } elseif ($row['status'] === 'in-use') {
                                        echo "<span class='status in-use'>In Use</span>";
                                    } else {
                                        echo "<span class='status offline'>Offline</span>";
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    echo $row['logged_user']
                                        ? htmlspecialchars($row['logged_user'])
                                        : "<i>None</i>";
                                ?>
                            </td>
                            <td>
                                <button class="edit-btn" onclick="openEditModal(<?php echo $row['computer_id']; ?>, '<?php echo addslashes($row['computer_name']); ?>', <?php echo $row['type_id']; ?>)">✏️ Edit</button>
                                <form action="actions/delete_computer.php" method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                    <input type="hidden" name="computer_id" value="<?php echo $row['computer_id']; ?>">
                                    <button type="submit" class="delete-btn">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;"><i>No computers found in the system.</i></p>
        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h4>Edit Computer</h4>
            <form action="actions/edit_computer.php" method="POST">
                <input type="hidden" name="computer_id" id="edit_computer_id">
                <input type="text" name="computer_name" id="edit_computer_name" placeholder="Computer Name" required>
                <select name="type_id" id="edit_type_id" required>
                    <option value="">Select Type</option>
                    <?php
                    $types_result->data_seek(0); // Reset pointer
                    while ($type = $types_result->fetch_assoc()): ?>
                        <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">💾 Save Changes</button>
            </form>
        </div>
    </div>
</main>

<script>
function openEditModal(id, name, typeId) {
    document.getElementById('edit_computer_id').value = id;
    document.getElementById('edit_computer_name').value = name;
    document.getElementById('edit_type_id').value = typeId;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function confirmDelete() {
    return confirm('Are you sure you want to delete this computer? This action cannot be undone.');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>
