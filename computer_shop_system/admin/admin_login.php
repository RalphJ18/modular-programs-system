<?php
session_start();
include('../db_connect.php');

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Login process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE email = ? AND role_id = 1 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['user_id'];
            $_SESSION['admin_name'] = $row['full_name'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "❌ Invalid password.";
        }
    } else {
        $error = "⚠️ Admin account not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🔐 Admin Login - Computer Shop System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: url('assets/company/bgadmin.png') no-repeat center center/cover;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            width: 90%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
            color: white;
            animation: fadeIn 0.6s ease-out;
        }

        .login-container img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        h2 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            background: linear-gradient(90deg, #ff4d4d, #b30000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        label {
            display: block;
            text-align: left;
            margin: 10px 0 5px;
            font-weight: 500;
            color: #ffcccc;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 1rem;
            outline: none;
            margin-bottom: 15px;
            transition: background 0.3s ease;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            background: rgba(255, 255, 255, 0.25);
        }

        button {
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #ff6666, #b30000);
        }

        .error {
            margin-top: 15px;
            color: #ff8080;
            background: rgba(255, 0, 0, 0.2);
            padding: 10px;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/company/company.png" alt="Company Logo">
        <h2>Admin Login</h2>

        <form method="POST" action="">
            <label>Email:</label>
            <input type="text" name="email" required placeholder="Enter admin email">

            <label>Password:</label>
            <input type="password" name="password" required placeholder="Enter password">

            <button type="submit">Login</button>
        </form>

        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
    </div>
</body>
</html>
