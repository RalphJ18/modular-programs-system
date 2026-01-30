<?php
session_start();
include('../db_connect.php');

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User - Computer Shop System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* RESET */
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
            align-items: center;
            padding: 40px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 40px 50px;
            width: 450px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        .form-container h3 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.5rem;
            background: linear-gradient(90deg, #ff4d4d, #b30000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #fff;
        }

        form input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            outline: none;
            margin-bottom: 20px;
            font-size: 1rem;
            background-color: rgba(255, 255, 255, 0.9);
            color: #222;
        }

        form input:focus, form select:focus {
            box-shadow: 0 0 0 2px #ff4d4d;
        }

        form select {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            outline: none;
            margin-bottom: 20px;
            font-size: 1rem;
            background-color: rgba(255, 255, 255, 0.9);
            color: #222;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            border: none;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #ff6666, #b30000);
        }

        .note {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <img src="assets/company/company.png" alt="Company Logo">
            <h2>Computer Shop System</h2>
        </div>
        <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </header>

    <main>
        <div class="form-container">
            <h3>Add New User</h3>

            <form action="actions/add_user_action.php" method="POST">
                <label>Full Name:</label>
                <input type="text" name="full_name" placeholder="Enter user's full name" required>

                <label>Email Address (User ID):</label>
                <input type="email" name="email" placeholder="Enter user's email address" required>

                <label>Initial Credits (₱):</label>
                <input type="number" step="0.01" name="credits" placeholder="Enter initial credits" required>

                <label>Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="">Select Payment Method</option>
                    <option value="cash">Cash</option>
                    <option value="gcash">GCash</option>
                    <option value="card">Card</option>
                </select>

                <div id="reference_number_container" style="display: none;">
                    <label>Reference Number:</label>
                    <input type="text" name="reference_number" id="reference_number" placeholder="Enter reference number">
                </div>

                <button type="submit">Add User</button>
            </form>

            <p class="note">Default password is <b>123</b>. The user can change it later from their dashboard.</p>
            <div class="footer">System version 1.0 — Developed By Ralph Jay P. Maano</div>
        </div>
    </main>

    <script>
        document.getElementById('payment_method').addEventListener('change', function() {
            const method = this.value;
            const container = document.getElementById('reference_number_container');
            const input = document.getElementById('reference_number');

            if (method === 'gcash' || method === 'card') {
                container.style.display = 'block';
                input.required = true;
            } else {
                container.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        });
    </script>

</body>
</html>
