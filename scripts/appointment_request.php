<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/dbconfig.inc.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: ../views/login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$flat_id = $_GET['flat_id'] ?? null;

if (!$flat_id) {
    echo "Invalid flat ID.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    // Check for already booked appointment
    $check = $pdo->prepare("SELECT * FROM appointments WHERE flat_id = :flat_id AND date = :date AND time = :time");
    $check->execute(['flat_id' => $flat_id, 'date' => $date, 'time' => $time]);
    
    if ($check->rowCount() > 0) {
        echo "<p style='color:red;'>Time slot already booked.</p>";
    } else {
        // Insert new appointment
        $stmt = $pdo->prepare("INSERT INTO appointments (flat_id, customer_id, date, time) VALUES (:flat_id, :cust_id, :date, :time)");
        $stmt->execute([
            'flat_id' => $flat_id,
            'cust_id' => $customer_id,
            'date' => $date,
            'time' => $time
        ]);
        
        // Notify owner (this would usually insert into a messages table)
        echo "<p style='color:green;'>Appointment request sent successfully. Await owner's confirmation.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Flat Preview Appointment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Request Appointment for Flat #<?php echo htmlspecialchars($flat_id); ?></h2>
    <form method="POST">
        <label for="date">Select Date:</label>
        <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>"><br>

        <label for="time">Select Time:</label>
        <input type="time" name="time" required><br>

        <button type="submit">Request Appointment</button>
    </form>
</body>
</html>
