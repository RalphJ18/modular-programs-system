<?php
/**
 * cleanup_sessions.php
 * 
 * Automatic cleanup script for session and computer state consistency.
 * Include in login pages, dashboards, or scheduled cron tasks.
 */

include('../db_connect.php');

// 🧹 1. End any user sessions active more than 10 hours (600 minutes)
$conn->query("
    UPDATE user_sessions
    SET status = 'ended', end_time = NOW()
    WHERE status = 'active'
      AND TIMESTAMPDIFF(MINUTE, start_time, NOW()) > 600
");

// 🧍‍♂️ 2. Clear users who have no active sessions but still have a PC assigned
$conn->query("
    UPDATE users u
    LEFT JOIN (
        SELECT user_id FROM user_sessions WHERE status = 'active'
    ) s ON u.user_id = s.user_id
    SET u.active_pc_id = NULL
    WHERE s.user_id IS NULL AND u.active_pc_id IS NOT NULL
");

// 💻 3. Mark computers as available if they are 'in-use' but without active users
$conn->query("
    UPDATE computers c
    LEFT JOIN users u ON c.computer_id = u.active_pc_id
    SET c.status = 'available'
    WHERE u.user_id IS NULL AND c.status = 'in-use'
");

// 🧾 4. Optional: record system cleanup in logs (admin_id=0 means system)
$conn->query("
    INSERT INTO activity_logs (user_id, activity)
    VALUES (0, 'System cleanup executed: expired sessions closed, orphaned PCs freed.')
");
?>
