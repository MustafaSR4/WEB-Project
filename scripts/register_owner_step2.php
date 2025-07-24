<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../includes/dbconfig.inc.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $c = $_POST['confirm_password'];
    
    if (!filter_var($u, FILTER_VALIDATE_EMAIL)) {
        $error = "Username must be a valid email.";
    } elseif (!preg_match('/^\d.{4,13}[a-z]$/', $p)) {
        $error = "Password must be 6-15 chars, start with digit, end with lowercase.";
    } elseif ($p !== $c) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM owners WHERE username = :u");
        $stmt->execute([':u' => $u]);
        if ($stmt->fetchColumn()) {
            $error = "Email already used.";
        } else {
            $_SESSION['owner_username'] = $u;
            $_SESSION['owner_password'] = password_hash($p, PASSWORD_DEFAULT);
            header("Location: register_owner_step3.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Owner Registration - Step 2</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<h2>Owner Registration - Step 2</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <label>Username (Email): <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <label>Confirm Password: <input type="password" name="confirm_password" required></label><br>
    <button type="submit">Next</button>
</form>
</body>
</html>
