<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
include '../includes/dbconfig.inc.php';

$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION[$role . '_id'] ?? null;

$message_id = $_GET['id'] ?? null;
if (!$role || !$user_id || !$message_id) {
    echo "<main class='main-content'><p>Invalid request.</p></main>";
    include('footer.php');
    exit;
}

// Fetch the message
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = :id AND to_role = :role AND to_id = :uid");
$stmt->execute([':id' => $message_id, ':role' => $role, ':uid' => $user_id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    echo "<main class='main-content'><p>Message not found.</p></main>";
    include('footer.php');
    exit;
}

// Mark as read
if ($message['status'] === 'unread') {
    $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = :id")->execute([':id' => $message_id]);
}

// Handle reply
$replySuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body']);
    if ($body) {
        $reply = $pdo->prepare("INSERT INTO messages (to_role, to_id, from_role, from_id, title, body, status, created_at)
                                VALUES (:to_role, :to_id, :from_role, :from_id, :title, :body, 'unread', NOW())");
        $reply->execute([
            ':to_role' => $message['from_role'],
            ':to_id' => $message['from_id'],
            ':from_role' => $role,
            ':from_id' => $user_id,
            ':title' => 'Re: ' . $message['title'],
            ':body' => $body
        ]);
        $replySuccess = "✅ Reply sent successfully.";
    }
}
?>

<main class="main-content">
<link rel="stylesheet" href="../css/style.css">
<h2>📨 View Message</h2>

<table class="message-table">
    <tr>
        <th>From</th>
        <td><?php echo ucfirst($message['from_role']) . " #" . htmlspecialchars($message['from_id']); ?></td>
    </tr>
    <tr>
        <th>Title</th>
        <td><?php echo htmlspecialchars($message['title']); ?></td>
    </tr>
    <tr>
        <th>Date</th>
        <td><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></td>
    </tr>
    <tr>
        <th>Body</th>
        <td><?php echo nl2br(htmlspecialchars($message['body'])); ?></td>
    </tr>
</table>

<hr>
<h3>✍️ Reply</h3>
<?php if ($replySuccess): ?>
    <p class="success-msg"><?php echo $replySuccess; ?></p>
<?php endif; ?>

<form method="POST">
    <textarea name="body" rows="5" placeholder="Type your reply here..." required></textarea><br>
    <button type="submit">Send Reply</button>
</form>
</main>

<?php include('footer.php'); ?>
