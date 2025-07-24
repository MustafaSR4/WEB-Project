<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/dbconfig.inc.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $error = "Username must be a valid email address.";
    } elseif (!preg_match('/^\d.{4,13}[a-z]$/', $password)) {
        $error = "Password must be 6-15 characters, start with a digit, and end with a lowercase letter.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE username = :u");
        $stmt->execute([':u' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already exists.";
        } else {
            $_SESSION['cust_username'] = $username;
            $_SESSION['cust_password'] = password_hash($password, PASSWORD_DEFAULT);
            header("Location: register_customer_step3.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration - Step 2</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Customer Registration - Step 2</h2>

    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

    <form method="POST">
        <label>Username : <input type="text" name="username" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <label>Confirm Password: <input type="password" name="confirm_password" required></label><br>

        <button type="submit">Next</button>
    </form>
</body>
</html>
