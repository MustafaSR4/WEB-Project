<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

// Only allow access if user is a manager
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'managers') {
    echo "<main class='main-content'><p>You must be logged in as a manager to view this page.</p></main>";
    include('footer.php');
    exit;
}

// Fetch all appointment requests with flat and customer info
$stmt = $pdo->prepare("
    SELECT a.id AS appointment_id, f.address, f.location, a.date, a.time, 
           c.name AS customer_name, c.email, a.status
    FROM appointments a
    JOIN flats f ON a.flat_id = f.id
    JOIN customers c ON a.customer_id = c.id
    ORDER BY a.date DESC, a.time DESC
");
$stmt->execute();
$appointments = $stmt->fetchAll();
?>

<main class="main-content">
<link rel="stylesheet" href="../css/style.css">

    <h2>All Appointments (Manager View)</h2>

    <?php if (count($appointments) === 0): ?>
        <p>No appointments found.</p>
    <?php else: ?>
        <table class="table-appointments">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Flat</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $index => $appt): ?>
                    <tr>
                        <td>#<?php echo $appt['appointment_id']; ?></td>
                        <td><?php echo htmlspecialchars($appt['address']); ?></td>
                        <td><?php echo htmlspecialchars($appt['location']); ?></td>
                        <td><?php echo $appt['date']; ?></td>
                        <td><?php echo $appt['time']; ?></td>
                        <td><?php echo htmlspecialchars($appt['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($appt['email']); ?></td>
                        <td><?php echo $appt['status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
