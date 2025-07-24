<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

// Ensure only customers can access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customers') {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> to request an appointment.</p></main>";
    include('footer.php');
    exit;
}

$customer_id = $_SESSION['customers_id'];
$flat_id = $_GET['id'] ?? null;

// Validate flat ID
if (!$flat_id) {
    echo "<main class='main-content'><p>No flat selected.</p></main>";
    include('footer.php');
    exit;
}

// Fetch flat + owner info
$stmt = $pdo->prepare("SELECT f.*, o.name AS owner_name, o.id AS owner_id FROM flats f JOIN owners o ON f.owner_id = o.id WHERE f.id = :id");
$stmt->execute([':id' => $flat_id]);
$flat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$flat) {
    echo "<main class='main-content'><p>Flat not found or not approved.</p></main>";
    include('footer.php');
    exit;
}

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date']) && isset($_POST['time'])) {
    $date = $_POST['date'];
    $time = $_POST['time'];
    
    $stmt = $pdo->prepare("SELECT 1 FROM appointments WHERE flat_id = :flat AND date = :date AND time = :time");
    $stmt->execute([
        ':flat' => $flat_id,
        ':date' => $date,
        ':time' => $time
    ]);
    
    if ($stmt->rowCount() > 0) {
        $message = "<p class='error-msg'>This time slot is already taken. Please choose another.</p>";
    } else {
        $insert = $pdo->prepare("INSERT INTO appointments (flat_id, customer_id, date, time, status)
                                 VALUES (:flat, :cust, :date, :time, 'pending')");
        $insert->execute([
            ':flat' => $flat_id,
            ':cust' => $customer_id,
            ':date' => $date,
            ':time' => $time
        ]);
        
        // Notify customer
        $msg_customer = "Your appointment request for Flat #$flat_id on $date at $time has been submitted.";
        $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)")
        ->execute([$customer_id, 'customers', $msg_customer]);
        
        // Notify owner
        $msg_owner = "New appointment request from customer #$customer_id for Flat #$flat_id on $date at $time.";
        $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)")
        ->execute([$flat['owner_id'], 'owners', $msg_owner]);
        
        $message = "<p class='success-msg'>Appointment request sent successfully. Waiting for owner's confirmation.</p>";
    }
}
?>

<main class="main-content">
<link rel="stylesheet" href="../css/style.css">
<h2>Request Appointment to View Flat #<?= htmlspecialchars($flat['reference_number'] ?? $flat['id']) ?></h2>

<?php if (isset($message)) echo $message; ?>

<table class="slot-table">
    <thead>
        <tr><th>Date</th><th>Time</th><th>Action</th></tr>
    </thead>
    <tbody>
        <?php
        $today = new DateTime();
        $slots = ['09:00', '11:00', '13:00', '15:00', '17:00'];

        for ($d = 0; $d < 7; $d++) {
            $date = (clone $today)->modify("+$d days")->format('Y-m-d');
            foreach ($slots as $time) {
                $check = $pdo->prepare("SELECT 1 FROM appointments WHERE flat_id = :flat AND date = :date AND time = :time");
                $check->execute([':flat' => $flat_id, ':date' => $date, ':time' => $time]);
                $isTaken = $check->rowCount() > 0;

                echo "<tr>";
                echo "<td>$date</td><td>$time</td><td>";
                if ($isTaken) {
                    echo "<span class='slot-taken'>Booked</span>";
                } else {
                    echo "<form method='POST' style='display:inline'>
                            <input type='hidden' name='date' value='$date'>
                            <input type='hidden' name='time' value='$time'>
                            <button type='submit'>Book</button>
                          </form>";
                }
                echo "</td></tr>";
            }
        }
        ?>
    </tbody>
</table>
</main>

<?php include('footer.php'); ?>
