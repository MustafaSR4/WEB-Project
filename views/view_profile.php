<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/dbconfig.inc.php';
include('header.php');
include('nav.php');

// Get session role and ID
$role = $_SESSION['user_role'] ?? '';
$user_id = $_SESSION[$role . '_id'] ?? null;

if (!$role || !$user_id || !in_array($role, ['customers', 'owners', 'managers'])) {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> to access your profile.</p></main>";
    include('footer.php');
    exit;
}

$table = $role;
$message = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($role === 'managers') {
        $stmt = $pdo->prepare("
            UPDATE managers SET
                name = :name,
                email = :email,
                username = :username
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'     => $_POST['name'],
            ':email'    => $_POST['email'],
            ':username' => $_POST['username'],
            ':id'       => $user_id
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE $table SET
                name = :name,
                address = :address,
                email = :email,
                mobile = :mobile,
                phone = :phone
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'   => $_POST['name'],
            ':address'=> $_POST['address'],
            ':email'  => $_POST['email'],
            ':mobile' => $_POST['mobile'],
            ':phone'  => $_POST['phone'],
            ':id'     => $user_id
        ]);
    }
    
    $message = "Profile updated successfully!";
}

// Fetch profile
$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<main class="main-content">
    <h2>User Profile</h2>

    <?php if (!empty($message)): ?>
        <p class="success-msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" class="profile-form">
        <label>ID:
            <input type="text" value="<?php echo htmlspecialchars($user['id']); ?>" readonly>
        </label>

        <label>Name:
            <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
        </label>

        <?php if ($role === 'managers'): ?>
            <label>Username:
                <input type="text" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>">
            </label>

            <label>Email:
                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
            </label>
        <?php else: ?>
            <label>Address:
                <input type="text" name="address" required value="<?php echo htmlspecialchars($user['address']); ?>">
            </label>

            <label>Email:
                <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
            </label>

            <label>Mobile:
                <input type="text" name="mobile" required value="<?php echo htmlspecialchars($user['mobile']); ?>">
            </label>

            <label>Phone:
                <input type="text" name="phone" required value="<?php echo htmlspecialchars($user['phone']); ?>">
            </label>
        <?php endif; ?>

        <button type="submit">Update Profile</button>
    </form>
</main>

<?php include('footer.php'); ?>
