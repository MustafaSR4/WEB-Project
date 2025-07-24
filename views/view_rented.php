<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
include '../includes/dbconfig.inc.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customers') {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> as a customer to view rentals.</p></main>";
    include('footer.php');
    exit;
}

$customer_id = $_SESSION['customers_id'];
$now = date('Y-m-d');

//  Automatically release expired rentals
$releaseStmt = $pdo->prepare("
    UPDATE flats
    SET is_rented = 0
    WHERE id IN (
        SELECT flat_id FROM rentals
        WHERE end_date < :today AND customer_id = :cust
    )
");
$releaseStmt->execute([':today' => $now, ':cust' => $customer_id]);

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_rental_id'])) {
    $rental_id = $_POST['cancel_rental_id'];
    
    $checkStmt = $pdo->prepare("SELECT * FROM rentals WHERE id = :id AND customer_id = :cust AND start_date > :today");
    $checkStmt->execute([
        ':id' => $rental_id,
        ':cust' => $customer_id,
        ':today' => $now
    ]);
    $rental = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rental) {
        $pdo->prepare("DELETE FROM rentals WHERE id = :id")->execute([':id' => $rental_id]);
        $pdo->prepare("UPDATE flats SET is_rented = 0 WHERE id = :flat_id")->execute([':flat_id' => $rental['flat_id']]);
        $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)")
        ->execute([$customer_id, 'customers', 'You canceled your rental for Flat #' . $rental['flat_id'] . '.']);
        
        $_SESSION['cancel_msg'] = "Rental canceled successfully. The flat is now available again.";
        header("Location: view_rented.php");
        exit;
    }
}

/// Fetch rentals for the customer
$stmt = $pdo->prepare("
    SELECT r.*, f.location, f.address, f.monthly_rent, f.id AS flat_id,
           o.id AS owner_id, o.name AS owner_name, o.city AS owner_city, o.email AS owner_email, o.mobile AS owner_mobile
    FROM rentals r
    JOIN flats f ON r.flat_id = f.id
    JOIN owners o ON f.owner_id = o.id
    WHERE r.customer_id = :cust
    ORDER BY r.start_date DESC
");
$stmt->execute([':cust' => $customer_id]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
    <h2>Rented Flats</h2>

    <?php if (isset($_SESSION['cancel_msg'])): ?>
        <div class="success-message"><?php echo $_SESSION['cancel_msg']; unset($_SESSION['cancel_msg']); ?></div>
    <?php endif; ?>

    <?php if (count($rentals) === 0): ?>
        <p>You have not rented any flats yet.</p>
    <?php else: ?>
        <form method="POST">
            <table class="rented-table">
                <thead>
                    <tr>
                        <th>Reference #</th>
                        <th>Location</th>
                        <th>Rent ($)</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                        <th>Owner</th>
                        <th>Released</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rentals as $r): 
                        $status = ($r['end_date'] < $now) ? 'expired' : (($r['start_date'] > $now) ? 'upcoming' : 'current');
                    ?>
                        <tr class="<?php echo $status; ?>">
                            <td>
                                <a href="flat_details.php?id=<?php echo $r['flat_id']; ?>" class="details-link" target="_blank">
                                    #<?php echo $r['flat_id']; ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($r['location']); ?></td>
                            <td>$<?php echo $r['monthly_rent']; ?></td>
                            <td><?php echo $r['start_date']; ?></td>
                            <td><?php echo $r['end_date']; ?></td>
                            <td class="rental-status"><?php echo ucfirst($status); ?></td>
                            <td>
                                <a href="user_card.php?id=<?php echo $r['owner_id']; ?>" target="_blank" class="owner-link">
                                    <strong><?php echo htmlspecialchars($r['owner_name']); ?></strong>
                                </a>
                            </td>
                            <td><?php echo ($status === 'expired') ? '✔' : '—'; ?></td>
                            <td>
                                <?php if ($status === 'upcoming'): ?>
                                    <button type="submit" name="cancel_rental_id" value="<?php echo $r['id']; ?>" onclick="return confirm('Cancel this rental?');">
                                        ❌ Cancel
                                    </button>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
