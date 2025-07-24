<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

$from_role = $_SESSION['user_role'] ?? '';
$from_id = $_SESSION[$from_role . '_id'] ?? null;

if (!$from_role || !$from_id) {
    echo "<main class='main-content'><p>Please log in to send a message.</p></main>";
    include('footer.php');
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to_role = $_POST['to_role'];
    $to_id = $_POST['to_id'];
    $title = $_POST['title'];
    $body = $_POST['body'];
    
    $stmt = $pdo->prepare("INSERT INTO messages (to_role, to_id, from_role, from_id, title, body)
                           VALUES (:to_role, :to_id, :from_role, :from_id, :title, :body)");
    $stmt->execute([
        ':to_role' => $to_role,
        ':to_id' => $to_id,
        ':from_role' => $from_role,
        ':from_id' => $from_id,
        ':title' => $title,
        ':body' => $body
    ]);
    
    $success = "Message sent successfully!";
}
?>

<main class="main-content">
<link rel="stylesheet" href="../css/style.css">

    <h2>Send a Message</h2>

    <?php if ($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success-msg"><?php echo $success; ?></p><?php endif; ?>

    <form method="POST" class="profile-form">
        <label>To Role:
            <select name="to_role" required>
                <option value="owners">Owner</option>
                <option value="managers">Manager</option>
            </select>
        </label>

        <label>To ID:
            <input type="number" name="to_id" required>
        </label>

        <label>Title:
            <input type="text" name="title" required>
        </label>

        <label>Message Body:
            <textarea name="body" rows="5" required></textarea>
        </label>

        <button type="submit">Send</button>
    </form>
</main>

<?php include('footer.php'); ?>
