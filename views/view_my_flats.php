<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owners') {
    echo "<main class='main-content'><p>You must be logged in as an owner to view this page.</p></main>";
    include('footer.php');
    exit;
}

$owner_id = $_SESSION['owners_id'];
$stmt = $pdo->prepare("SELECT * FROM flats WHERE owner_id = :owner_id ORDER BY created_at DESC");
$stmt->execute([':owner_id' => $owner_id]);
$flats = $stmt->fetchAll();
?>

<main class="main-content">
<link rel="stylesheet" href="../css/style.css">

    <h2>My Flats</h2>

    <?php if (count($flats) === 0): ?>
        <p>You haven’t added any flats yet.</p>
    <?php else: ?>
        <ul class="flat-list">
            <?php foreach ($flats as $flat): ?>
                <li>
                    <strong><?php echo htmlspecialchars($flat['location']); ?></strong> -
                    <?php echo htmlspecialchars($flat['address']); ?> |
                    Rent: $<?php echo $flat['monthly_rent']; ?> |
                    Status: 
                    <?php
                        if ($flat['is_approved'] == 0) echo "🕒 Pending Approval";
                        elseif ($flat['is_rented']) echo "✅ Rented";
                        else echo "✔️ Available";
                    ?>
                    <br>
    <a href="flat_details.php?id=<?php echo $flat['id']; ?>" class="nav-link">View Details</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
