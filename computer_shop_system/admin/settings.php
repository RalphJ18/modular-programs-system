<?php
session_start();
include('../db_connect.php');

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch current settings
$sql = "SELECT * FROM settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    $shop_name = $settings['shop_name'];
    $basic_rate = $settings['basic_rate'];
    $vip_rate = $settings['vip_rate'];
    $ad_image = $settings['ad_image'];
} else {
    $shop_name = "My Computer Shop";
    $basic_rate = 29.00;
    $vip_rate = 49.00;
    $ad_image = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>⚙️ Settings - Computer Shop System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif;}

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
        .back-btn:hover {background: linear-gradient(90deg, #ff6666, #b30000); transform: scale(1.05);}

        /* MAIN CONTENT */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 20px;
            overflow-y: auto;
        }

        .settings-container {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 20px;
            padding: 40px 50px;
            width: 95%;
            max-width: 700px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        h3 {
            text-align: center;
            font-size: 1.6rem;
            margin-bottom: 20px;
            background: linear-gradient(90deg, #ff4d4d, #b30000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        form {
            margin-bottom: 40px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #ffdede;
        }

        input[type="text"], input[type="number"], input[type="url"], input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            outline: none;
        }

        input::file-selector-button {
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 14px;
            cursor: pointer;
        }

        input::file-selector-button:hover {
            background: linear-gradient(90deg, #ff6666, #b30000);
        }

        button {
            background: linear-gradient(90deg, #b30000, #ff4d4d);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 600;
        }

        button:hover {
            transform: scale(1.05);
            background: linear-gradient(90deg, #ff6666, #b30000);
        }

        .delete-btn {
            background: #d9534f;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            color: white;
            cursor: pointer;
        }

        .delete-btn:hover {background: #c9302c;}

        img {
            border-radius: 10px;
            margin-top: 10px;
            border: 2px solid rgba(255,255,255,0.3);
        }

        p, small { color: rgba(255,255,255,0.85); text-align: center; }
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
    <div class="settings-container">
        <h3>🛠️ Update Shop Info</h3>
        <form action="actions/update_settings.php" method="POST">
            <label>Shop Name:</label>
            <input type="text" name="shop_name" value="<?php echo htmlspecialchars($shop_name); ?>" required>

            <label>Basic Rate (₱/hr):</label>
            <input type="number" step="0.01" name="basic_rate" value="<?php echo htmlspecialchars($basic_rate); ?>" required>

            <label>VIP Rate (₱/hr):</label>
            <input type="number" step="0.01" name="vip_rate" value="<?php echo htmlspecialchars($vip_rate); ?>" required>

            <button type="submit">💾 Save Changes</button>
        </form>

        <hr style="border-color: rgba(255,255,255,0.2); margin: 30px 0;">

        <h3>🖼️ Advertisement Image</h3>
        <?php if (!empty($ad_image)): ?>
            <p>Current Advertisement:</p>
            <img src="assets/uploads/<?php echo htmlspecialchars($ad_image); ?>" alt="Ad Image" width="250"><br><br>

            <form action="actions/delete_ad.php" method="POST" onsubmit="return confirm('Are you sure you want to delete the current ad?');">
                <button type="submit" class="delete-btn">🗑 Delete Advertisement</button>
            </form>
            <br>
        <?php else: ?>
            <p><i>No advertisement uploaded yet.</i></p>
        <?php endif; ?>

        <form action="actions/upload_ad.php" method="POST" enctype="multipart/form-data">
            <label>Select New Advertisement Image (JPG, PNG, GIF):</label>
            <input type="file" name="ad_image" accept=".jpg,.jpeg,.png,.gif" required>

            <label>Ad Redirect Link (Optional):</label>
            <input type="url" name="ad_link" placeholder="https://example.com">

            <button type="submit">⬆️ Upload Image & Save Link</button>
        </form>

        <hr style="border-color: rgba(255,255,255,0.2); margin: 25px 0;">
        <small>Last updated: <?php echo isset($settings['last_updated']) ? $settings['last_updated'] : 'N/A'; ?></small>
    </div>
</main>

</body>
</html>
