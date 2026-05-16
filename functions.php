<?php
function logAction($conn, $uid, $text) { $uid = $uid ?: 'NULL'; $conn->query("INSERT INTO activity_logs (user_id, action_text) VALUES ($uid, '$text')"); }
?>
