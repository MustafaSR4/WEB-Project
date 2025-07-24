<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

$owner_id = $_GET['id'] ?? null;

if (!$owner_id || !is_numeric($owner_id)) {
    echo "<main class='main-content'><p>Invalid owner ID.</p></main>";
    include('footer.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM owners WHERE id = :id");
$stmt->execute([':id' => $owner_id]);
$owner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$owner) {
    echo "<main class='main-content'><p>Owner not found.</p></main>";
    include('footer.php');
    exit;
}
?>

<main class="main-content">
    <h2>Owner Profile</h2>

    <section class="user-card">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($owner['name']); ?></p>
        <p><strong>City:</strong> <?php echo htmlspecialchars($owner['city']); ?></p>
        <p><strong>Phone:</strong> 📞 <?php echo htmlspecialchars($owner['mobile']); ?></p>
        <p><strong>Email:</strong> ✉️ <a href="mailto:<?php echo htmlspecialchars($owner['email']); ?>"><?php echo htmlspecialchars($owner['email']); ?></a></p>
    </section>
</main>

<?php include('footer.php'); ?>
