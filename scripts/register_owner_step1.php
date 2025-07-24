<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('/^\d+$/', $_POST['national_id'])) $errors[] = "National ID must be digits only.";
    if (!preg_match('/^[A-Za-z\s]+$/', $_POST['name'])) $errors[] = "Name must contain only letters and spaces.";
    if (!$_POST['house_no'] || !$_POST['street'] || !$_POST['city'] || !$_POST['postal_code']) $errors[] = "Complete address required.";
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (!preg_match('/^\d{6,}$/', $_POST['mobile'])) $errors[] = "Mobile number must be at least 6 digits.";
    if (!preg_match('/^\d{6,}$/', $_POST['phone'])) $errors[] = "Phone number must be at least 6 digits.";
    if (!$_POST['bank'] || !$_POST['branch'] || !$_POST['account']) $errors[] = "Complete bank details required.";
    
    if (empty($errors)) {
        $_SESSION['owner_national_id'] = $_POST['national_id'];
        $_SESSION['owner_name'] = $_POST['name'];
        $_SESSION['owner_address'] = $_POST['house_no'] . ", " . $_POST['street'] . ", " . $_POST['city'] . " - " . $_POST['postal_code'];
        $_SESSION['owner_dob'] = $_POST['dob'];
        $_SESSION['owner_email'] = $_POST['email'];
        $_SESSION['owner_mobile'] = $_POST['mobile'];
        $_SESSION['owner_phone'] = $_POST['phone'];
        $_SESSION['owner_bank'] = $_POST['bank'];
        $_SESSION['owner_branch'] = $_POST['branch'];
        $_SESSION['owner_account'] = $_POST['account'];
        header('Location: register_owner_step2.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Owner Registration - Step 1</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<h2>Owner Registration - Step 1</h2>
<?php foreach ($errors as $e): echo "<p style='color:red;'>$e</p>"; endforeach; ?>
<form method="POST">
    <label>National ID: <input type="text" name="national_id" required></label><br>
    <label>Name: <input type="text" name="name" required></label><br>
    <fieldset><legend>Address</legend>
        <label>House No: <input type="text" name="house_no" required></label><br>
        <label>Street: <input type="text" name="street" required></label><br>
        <label>City: <input type="text" name="city" required></label><br>
        <label>Postal Code: <input type="text" name="postal_code" required></label><br>
    </fieldset>
    <label>DOB: <input type="date" name="dob" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Mobile: <input type="text" name="mobile" required></label><br>
    <label>Phone: <input type="text" name="phone" required></label><br>
    <fieldset><legend>Bank Info</legend>
        <label>Bank Name: <input type="text" name="bank" required></label><br>
        <label>Bank Branch: <input type="text" name="branch" required></label><br>
        <label>Account Number: <input type="text" name="account" required></label><br>
    </fieldset>
    <button type="submit">Next</button>
</form>
</body>
</html>
