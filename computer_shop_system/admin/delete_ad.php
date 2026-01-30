<?php
include('../../db_connect.php');

// Fetch current ad file
$result = $conn->query("SELECT ad_image FROM settings LIMIT 1");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ad_image = $row['ad_image'];

    // Delete file if exists
    $file_path = "../assets/uploads/" . $ad_image;
    if (!empty($ad_image) && file_exists($file_path)) {
        unlink($file_path);
    }

    // Clear ad fields in DB
    $conn->query("UPDATE settings SET ad_image=NULL, ad_link=NULL, last_updated=NOW()");
}

echo "<script>alert('Advertisement deleted successfully!'); window.location.href='../settings.php';</script>";
exit();
?>
