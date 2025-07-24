<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
include '../includes/dbconfig.inc.php';

// Must be logged in as owner
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owners') {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> as an owner to view appointments.</p></main>";
    include('footer.php');
    exit;
}

$owner_id = $_SESSION['owners_id'];

// Handle appointment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['action'])) {
    $status = $_POST['action'] === 'accept' ? 'accepted' : 'rejected';
    // Fetch appointment to get customer_id and flat_id
    $apptStmt = $pdo->prepare("SELECT customer_id, flat_id FROM appointments WHERE id = :id");
    $apptStmt->execute([':id' => $_POST['appointment_id']]);
    $appt = $apptStmt->fetch(PDO::FETCH_ASSOC);
    
    $status = $_POST['action'] === 'accept' ? 'accepted' : 'rejected';
    
    // Update appointment status
    $stmt = $pdo->prepare("UPDATE appointments SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $_POST['appointment_id']]);
    
    // Insert notification for the customer
    $message = "Your appointment for Flat #{$appt['flat_id']} was {$status} by the owner.";
    $notify = $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)");
    $notify->execute([$appt['customer_id'], 'customers', $message]);
    
}

// Get appointment requests for flats owned by this owner
$stmt = $pdo->prepare("
    SELECT a.*, f.location, f.address, c.name AS customer_name
    FROM appointments a
    JOIN flats f ON a.flat_id = f.id
    JOIN customers c ON a.customer_id = c.id
    WHERE f.owner_id = :owner_id
    ORDER BY a.date DESC, a.time DESC
");
$stmt->execute([':owner_id' => $owner_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
<link rel="stylesheet" href="../css/style.css">

    <h2>View Appointments</h2>

    <?php if (count($appointments) === 0): ?>
        <p>No appointments yet.</p>
    <?php else: ?>
        <table class="appointments-table">
            <thead>
                <tr>
                    <th>Flat</th>
                    <th>Address</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appt): ?>
                    <tr class="<?php echo $appt['status']; ?>">
                        <td>#<?php echo $appt['flat_id']; ?></td>
                        <td><?php echo htmlspecialchars($appt['address']); ?></td>
                        <td><?php echo $appt['date']; ?></td>
                        <td><?php echo $appt['time']; ?></td>
                        <td><?php echo htmlspecialchars($appt['customer_name']); ?></td>
                        <td class="status"><?php echo ucfirst($appt['status']); ?></td>
                        <td>
                            <?php if ($appt['status'] === 'pending'): ?>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                    <button type="submit" name="action" value="accept">Accept</button>
                                    <button type="submit" name="action" value="reject">Reject</button>
                                </form>
                            <?php else: ?>
                                <em>No action</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
