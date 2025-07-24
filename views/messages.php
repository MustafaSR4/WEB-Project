<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
include '../includes/dbconfig.inc.php';

// Detect user role and ID
$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION[$role . '_id'] ?? null;

if (!$role || !$user_id) {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> to view your messages.</p></main>";
    include('footer.php');
    exit;
}

// Fetch messages addressed to this user
$stmt = $pdo->prepare("SELECT * FROM messages WHERE to_role = :role AND to_id = :id ORDER BY created_at DESC");
$stmt->execute([':role' => $role, ':id' => $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
<link rel="stylesheet" href="../css/style.css">

    <h2>Your Messages</h2>

    <?php if (count($messages) === 0): ?>
        <p>No messages yet.</p>
    <?php else: ?>
        <table class="messages-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Title</th>
                    <th>From</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr class="<?php echo $msg['status'] === 'unread' ? 'unread-message' : 'read-message'; ?>">
                        <td><?php echo ucfirst($msg['status']); ?></td>
                        <td>
                            <a href="view_message.php?id=<?php echo $msg['id']; ?>">
                                <?php echo htmlspecialchars($msg['title']); ?>
                            </a>
                        </td>
                        <td><?php echo ucfirst($msg['from_role']) . " #" . $msg['from_id']; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
