<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/dbconfig.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO customers
        (national_id, name, address, dob, email, mobile, phone, username, password)
        VALUES (:nid, :name, :addr, :dob, :email, :mobile, :phone, :username, :pass)");
    $stmt->execute([
        ':nid' => $_SESSION['cust_national_id'],
        ':name' => $_SESSION['cust_name'],
        ':addr' => $_SESSION['cust_address'],
        ':dob' => $_SESSION['cust_dob'],
        ':email' => $_SESSION['cust_email'],
        ':mobile' => $_SESSION['cust_mobile'],
        ':phone' => $_SESSION['cust_phone'],
        ':username' => $_SESSION['cust_username'],
        ':pass' => $_SESSION['cust_password']
    ]);
    $id = $pdo->lastInsertId();
    session_destroy();
    echo "<h2>Registration Complete!</h2>";
    echo "<p>Your Customer ID is: <strong>" . str_pad($id, 9, '0', STR_PAD_LEFT) . "</strong></p>";
    echo "<p>You can now log in to rent flats.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration - Step 3</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Confirm Your Details</h2>
    <form method="POST">
        <p><strong>National ID:</strong> <?= $_SESSION['cust_national_id'] ?></p>
        <p><strong>Name:</strong> <?= $_SESSION['cust_name'] ?></p>
        <p><strong>Address:</strong> <?= $_SESSION['cust_address'] ?></p>
        <p><strong>Date of Birth:</strong> <?= $_SESSION['cust_dob'] ?></p>
        <p><strong>Email:</strong> <?= $_SESSION['cust_email'] ?></p>
        <p><strong>Mobile:</strong> <?= $_SESSION['cust_mobile'] ?></p>
        <p><strong>Phone:</strong> <?= $_SESSION['cust_phone'] ?></p>
        <p><strong>Username:</strong> <?= $_SESSION['cust_username'] ?></p>
        <button type="submit">Confirm & Register</button>
    </form>
</body>
</html>
