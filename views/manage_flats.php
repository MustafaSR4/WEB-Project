<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

// //Manager-only access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'managers') {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> as a manager to approve flats.</p></main>";
    include('footer.php');
    exit;
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flat_id']) && isset($_POST['action'])) {
    $flat_id = $_POST['flat_id'];
    $action = $_POST['action'];
    
    // Get owner ID first
    $ownerStmt = $pdo->prepare("SELECT owner_id FROM flats WHERE id = :id");
    $ownerStmt->execute([':id' => $flat_id]);
    $ownerData = $ownerStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ownerData) {
        $owner_id = $ownerData['owner_id'];
        
        if ($action === 'approve') {
            $pdo->prepare("UPDATE flats SET is_approved = 1 WHERE id = :id")->execute([':id' => $flat_id]);
            $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)")
            ->execute([$owner_id, 'owners', '✅ Your flat #' . $flat_id . ' was approved by the manager.']);
        } elseif ($action === 'reject') {
            $pdo->prepare("DELETE FROM flats WHERE id = :id")->execute([':id' => $flat_id]);
            $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)")
            ->execute([$owner_id, 'owners', '❌ Your flat #' . $flat_id . ' was rejected by the manager.']);
        }
    }
    
    header("Location: manage_flats.php");
    exit;
}

// Fetch unapproved flats
$stmt = $pdo->query("
    SELECT f.*, o.name AS owner_name
    FROM flats f
    JOIN owners o ON f.owner_id = o.id
    WHERE f.is_approved = 0
    ORDER BY f.created_at DESC
");
$flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
    <h2>Manage Flat Approvals</h2>

    <?php if (count($flats) === 0): ?>
        <p>All flats have been reviewed.</p>
    <?php else: ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Ref #</th>
                    <th>Owner</th>
                    <th>Location</th>
                    <th>Address</th>
                    <th>Rent</th>
                    <th>Bedrooms</th>
                    <th>Bathrooms</th>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flats as $flat): ?>
                    <tr>
                        <td>#<?php echo $flat['id']; ?></td>
                        <td><?php echo htmlspecialchars($flat['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($flat['location']); ?></td>
                        <td><?php echo htmlspecialchars($flat['address']); ?></td>
                        <td>$<?php echo $flat['monthly_rent']; ?></td>
                        <td><?php echo $flat['bedrooms']; ?></td>
                        <td><?php echo $flat['bathrooms']; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="flat_id" value="<?php echo $flat['id']; ?>">
                                <button type="submit" name="action" value="approve">✅ Approve</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="flat_id" value="<?php echo $flat['id']; ?>">
                                <button type="submit" name="action" value="reject" onclick="return confirm('Are you sure you want to reject this flat?');">❌ Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
