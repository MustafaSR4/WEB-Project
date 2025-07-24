<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/dbconfig.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $message_body = trim($_POST['message'] ?? '');
    
    // Validate inputs
    if ($name && $email && $subject && $message_body) {
        // Get all manager IDs
        $managersStmt = $pdo->query("SELECT id FROM managers");
        $managers = $managersStmt->fetchAll(PDO::FETCH_COLUMN); // Get IDs directly
        
        if ($managers) {
            $stmt = $pdo->prepare("
                INSERT INTO messages (to_role, to_id, from_role, from_id, title, body, status, created_at)
                VALUES ('managers', :to_id, 'system', NULL, :title, :body, 'unread', NOW())
            ");
            
            $title = htmlspecialchars($subject);
            $body = "Message from contact form:\n\nFrom: " . htmlspecialchars($name) . " <$email>\n\n" . htmlspecialchars($message_body);
            
            foreach ($managers as $mgr_id) {
                $stmt->execute([
                    ':to_id' => $mgr_id,
                    ':title' => $title,
                    ':body' => $body
                ]);
            }
            
            header('Location: contact_us.php?sent=1');
            exit;
        }
    }
    
    // Fallback on error
    header('Location: contact_us.php?error=1');
    exit;
}
?>
