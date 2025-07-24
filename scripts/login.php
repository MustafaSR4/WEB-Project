<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/dbconfig.inc.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $roles = ['customers', 'owners', 'managers'];
    
    foreach ($roles as $role) {
        $stmt = $pdo->prepare("SELECT * FROM $role WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //save data in session
        if ($user && $password === $user['password']) {
            $_SESSION['user_role'] = $role;
            $_SESSION[$role . '_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header('Location: ../views/index.php');
            exit;
        }
    }
    
    $error = "Invalid credentials.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="main-content">
        <h2>Login</h2>
        <?php if ($error): ?>
            <p class="error-msg"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" class="profile-form">
            <label>Username:
                <input type="text" name="username" required>
            </label>
            <label>Password:
                <input type="password" name="password" required>
            </label>
            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>
