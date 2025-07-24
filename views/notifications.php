<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
include '../includes/dbconfig.inc.php';

// ✅ Detect user role and ID from session
$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION[$role . '_id'] ?? null;

if (!$role || !$user_id) {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> to view your notifications.</p></main>";
    include('footer.php');
    exit;
}

// ✅ Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_time'])) {
    $markStmt = $pdo->prepare("UPDATE notifications SET status = 'read' WHERE role = :role AND user_id = :id AND created_at = :time");
    $markStmt->execute([
        ':role' => $role,
        ':id' => $user_id,
        ':time' => $_POST['mark_read_time']
    ]);
}

// ✅ Fetch messages
$notifications = [];

$msgStmt = $pdo->prepare("
    SELECT 'Message' AS type, title, body, from_role, from_id, status, created_at
    FROM messages
    WHERE to_role = :role AND to_id = :id
    ORDER BY created_at DESC
");
$msgStmt->execute([':role' => $role, ':id' => $user_id]);
$messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);
$notifications = array_merge($notifications, $messages);

// ✅ Fetch system notifications
$sysStmt = $pdo->prepare("
    SELECT 'System' AS type, message AS title, message AS body,
           'system' AS from_role, NULL AS from_id, status, created_at
    FROM notifications
    WHERE role = :role AND user_id = :id
    ORDER BY created_at DESC
");
$sysStmt->execute([':role' => $role, ':id' => $user_id]);
$systemNotes = $sysStmt->fetchAll(PDO::FETCH_ASSOC);
$notifications = array_merge($notifications, $systemNotes);

// ✅ Sort all notifications by created_at (newest first)
usort($notifications, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
    
    // ✅ Group by date
    $grouped = [];
    foreach ($notifications as $note) {
        $dateKey = date('Y-m-d', strtotime($note['created_at']));
        $grouped[$dateKey][] = $note;
    }
    ?>

<main class="main-content">
    <h2>Notifications</h2>

    <?php if (count($notifications) === 0): ?>
        <p>No notifications at the moment.</p>
    <?php else: ?>
        <?php foreach ($grouped as $date => $notes): ?>
            <h3><?php echo date('F j, Y', strtotime($date)); ?></h3>
            <ul class="notification-list">
                <?php foreach ($notes as $note): ?>
                    <li class="notification-item <?php echo ($note['status'] === 'unread') ? 'unread' : 'read'; ?>">
                        <div class="notification-title">
                            <?php if ($note['status'] === 'unread'): ?>
                                <span class="new-icon">🔔</span>
                            <?php endif; ?>
                            <strong>
                                <?php if ($note['type'] === 'System'): ?>
                                    🛎️ <?php echo htmlspecialchars($note['title']); ?>
                                <?php else: ?>
                                    📩 <?php echo htmlspecialchars($note['title']); ?>
                                <?php endif; ?>
                            </strong>
                        </div>
                        <p class="notification-body"><?php echo htmlspecialchars($note['body']); ?></p>
                        <small class="notification-meta">
                            From:
                            <?php
                            echo ($note['from_role'] === 'system')
                                ? 'System'
                                : ucfirst($note['from_role']) . ' #' . htmlspecialchars($note['from_id']);
                            ?> |
                            Date: <?php echo date('Y-m-d H:i', strtotime($note['created_at'])); ?>
                        </small>

                        <?php if ($note['status'] === 'unread'): ?>
                            <form method="POST" style="margin-top: 6px;">
                                <input type="hidden" name="mark_read_time" value="<?php echo htmlspecialchars($note['created_at']); ?>">
                                <button type="submit" class="mark-read-btn">Mark as Read</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
