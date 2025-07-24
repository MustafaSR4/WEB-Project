<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/dbconfig.inc.php';

function sendMessage($pdo, $to_role, $to_id, $title, $body) {
    $stmt = $pdo->prepare("INSERT INTO messages (to_role, to_id, title, body, status) VALUES (:to_role, :to_id, :title, :body, 'unread')");
    $stmt->execute([
        ':to_role' => $to_role,
        ':to_id' => $to_id,
        ':title' => $title,
        ':body' => $body
    ]);
}
?>