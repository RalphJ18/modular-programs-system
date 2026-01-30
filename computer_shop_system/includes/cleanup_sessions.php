<?php
include('../db_connect.php');

// End any sessions older than 10 hours
$conn->query("
    UPDATE user_sessions
    SET status='ended', end_time=NOW()
    WHERE status='active'
      AND TIMESTAMPDIFF(MINUTE, start_time, NOW()) > 600
");

// Calculate total_cost for newly ended sessions
$conn->query("
    UPDATE user_sessions s
    JOIN computers c ON s.computer_id = c.computer_id
    JOIN computer_types ct ON c.type_id = ct.type_id
    JOIN settings st ON 1=1
    SET s.total_cost = TIMESTAMPDIFF(MINUTE, s.start_time, s.end_time) *
                      CASE
                        WHEN ct.type_name = 'VIP' THEN st.vip_rate / 60
                        ELSE st.basic_rate / 60
                      END
    WHERE s.status = 'ended' AND s.total_cost IS NULL
");

// Clear users linked to PCs but without active sessions
$conn->query("
    UPDATE users u
    LEFT JOIN user_sessions s ON u.user_id = s.user_id AND s.status='active'
    SET u.active_pc_id = NULL
    WHERE s.session_id IS NULL AND u.active_pc_id IS NOT NULL
");

// Free PCs that are still 'in-use' but have no active users
$conn->query("
    UPDATE computers c
    LEFT JOIN users u ON c.computer_id = u.active_pc_id
    SET c.status='available'
    WHERE u.user_id IS NULL AND c.status='in-use'
");
?>
