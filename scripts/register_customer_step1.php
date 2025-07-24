<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate fields
    if (!preg_match('/^\d+$/', $_POST['national_id'])) {
        $errors[] = "National ID must contain digits only.";
    }
    if (!preg_match('/^[A-Za-z\s]+$/', $_POST['name'])) {
        $errors[] = "Name must contain only letters and spaces.";
    }
    if (!$_POST['house_no'] || !$_POST['street'] || !$_POST['city'] || !$_POST['postal_code']) {
        $errors[] = "Complete address is required.";
    }
    if (!preg_match('/^\d{4,}$/', $_POST['postal_code'])) {
        $errors[] = "Postal code must be at least 4 digits.";
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (!preg_match('/^\d{6,}$/', $_POST['mobile'])) {
        $errors[] = "Mobile number must contain at least 6 digits.";
    }
    if (!preg_match('/^\d{6,}$/', $_POST['phone'])) {
        $errors[] = "Telephone number must contain at least 6 digits.";
    }
    
    if (empty($errors)) {
        $_SESSION['cust_national_id'] = $_POST['national_id'];
        $_SESSION['cust_name'] = $_POST['name'];
        $_SESSION['cust_address'] = $_POST['house_no'] . ", " . $_POST['street'] . ", " . $_POST['city'] . " - " . $_POST['postal_code'];
        $_SESSION['cust_dob'] = $_POST['dob'];
        $_SESSION['cust_email'] = $_POST['email'];
        $_SESSION['cust_mobile'] = $_POST['mobile'];
        $_SESSION['cust_phone'] = $_POST['phone'];
        header('Location: register_customer_step2.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Registration - Step 1</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Customer Registration - Step 1</h2>

    <?php foreach ($errors as $e): ?>
        <p style="color:red;"><?= $e ?></p>
    <?php endforeach; ?>

    <form method="POST">
        <label>National ID Number: <input type="text" name="national_id" required></label><br>
        <label>Full Name: <input type="text" name="name" required></label><br>

        <fieldset>
            <legend>Address</legend>
            <label>Flat/House No.: <input type="text" name="house_no" required></label><br>
            <label>Street Name: <input type="text" name="street" required></label><br>
            <label>City: <input type="text" name="city" required></label><br>
            <label>Postal Code: <input type="text" name="postal_code" required></label><br>
        </fieldset>

        <label>Date of Birth: <input type="date" name="dob" required></label><br>
        <label>Email Address: <input type="email" name="email" required></label><br>
        <label>Mobile Number: <input type="text" name="mobile" required></label><br>
        <label>Telephone Number: <input type="text" name="phone" required></label><br>

        <button type="submit">Next</button>
    </form>
</body>
</html>
