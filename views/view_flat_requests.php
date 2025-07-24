<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'managers') {
    echo "<main class='main-content'><p>Please <a href='../scripts/login.php'>log in</a> as a manager to view requests.</p></main>";
    include('footer.php');
    exit;
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flat_id']) && isset($_POST['action'])) {
    $flat_id = $_POST['flat_id'];
    if ($_POST['action'] === 'approve') {
        // Generate 6-digit reference number
        // First check if reference number is already set
        $check = $pdo->prepare("SELECT reference_number FROM flats WHERE id = :id");
        $check->execute([':id' => $flat_id]);
        $current = $check->fetchColumn();
        
        if (!$current) {
            $reference = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("UPDATE flats SET is_approved = 1, reference_number = :ref WHERE id = :id");
            $stmt->execute([':ref' => $reference, ':id' => $flat_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE flats SET is_approved = 1 WHERE id = :id");
            $stmt->execute([':id' => $flat_id]);
        }
        
    } elseif ($_POST['action'] === 'reject') {
        $stmt = $pdo->prepare("UPDATE flats SET is_approved = -1 WHERE id = :id");
        $stmt->execute([':id' => $flat_id]);
    }
}

$stmt = $pdo->prepare("SELECT f.*, o.name AS owner_name FROM flats f JOIN owners o ON f.owner_id = o.id WHERE f.is_approved = 0 ORDER BY f.created_at DESC");
$stmt->execute();
$flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
    <link rel="stylesheet" href="../css/style.css">
    <h2>Flat Approval Requests</h2>

    <?php if (count($flats) === 0): ?>
        <p>No pending flat approvals.</p>
    <?php else: ?>
        <table class="flats-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reference #</th>
                    <th>Location</th>
                    <th>Monthly Rent</th>
                    <th>Bedrooms</th>
                    <th>Bathrooms</th>
                    <th>Owner</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flats as $flat): ?>
                    <tr>
                        <td>#<?php echo $flat['id']; ?></td>
                        <td><?php echo $flat['reference_number'] ? '#' . $flat['reference_number'] : '—'; ?></td>
                        <td><?php echo htmlspecialchars($flat['location']); ?></td>
                        <td>$<?php echo htmlspecialchars($flat['monthly_rent']); ?></td>
                        <td><?php echo htmlspecialchars($flat['bedrooms']); ?></td>
                        <td><?php echo htmlspecialchars($flat['bathrooms']); ?></td>
                        <td><?php echo htmlspecialchars($flat['owner_name']); ?></td>
                        <td>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="flat_id" value="<?php echo $flat['id']; ?>">
                                <button type="submit" name="action" value="approve" onclick="return confirm('Approve this flat?')">✅ Approve</button>
                                <button type="submit" name="action" value="reject" onclick="return confirm('Reject this flat?')">❌ Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
