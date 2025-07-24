<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../includes/dbconfig.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO owners
        (national_id, name, address, dob, email, mobile, phone, bank_name, bank_branch, account_number, username, password)
        VALUES (:nid, :name, :addr, :dob, :email, :mobile, :phone, :bank, :branch, :account, :username, :pass)");
    $stmt->execute([
        ':nid' => $_SESSION['owner_national_id'],
        ':name' => $_SESSION['owner_name'],
        ':addr' => $_SESSION['owner_address'],
        ':dob' => $_SESSION['owner_dob'],
        ':email' => $_SESSION['owner_email'],
        ':mobile' => $_SESSION['owner_mobile'],
        ':phone' => $_SESSION['owner_phone'],
        ':bank' => $_SESSION['owner_bank'],
        ':branch' => $_SESSION['owner_branch'],
        ':account' => $_SESSION['owner_account'],
        ':username' => $_SESSION['owner_username'],
        ':pass' => $_SESSION['owner_password']
    ]);
    $id = $pdo->lastInsertId();
    session_destroy();
    echo "<h2>Registration Complete!</h2>";
    echo "<p>Your Owner ID is <strong>" . str_pad($id, 9, '0', STR_PAD_LEFT) . "</strong></p>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Owner Registration - Step 3</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<h2>Confirm Owner Information</h2>
<form method="POST">
    <p><strong>Name:</strong> <?= $_SESSION['owner_name'] ?></p>
    <p><strong>National ID:</strong> <?= $_SESSION['owner_national_id'] ?></p>
    <p><strong>Address:</strong> <?= $_SESSION['owner_address'] ?></p>
    <p><strong>DOB:</strong> <?= $_SESSION['owner_dob'] ?></p>
    <p><strong>Email:</strong> <?= $_SESSION['owner_email'] ?></p>
    <p><strong>Mobile:</strong> <?= $_SESSION['owner_mobile'] ?></p>
    <p><strong>Phone:</strong> <?= $_SESSION['owner_phone'] ?></p>
    <p><strong>Bank:</strong> <?= $_SESSION['owner_bank'] ?>, <?= $_SESSION['owner_branch'] ?> (<?= $_SESSION['owner_account'] ?>)</p>
    <p><strong>Username:</strong> <?= $_SESSION['owner_username'] ?></p>
    <button type="submit">Confirm & Register</button>
</form>
</body>
</html>
