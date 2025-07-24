<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/dbconfig.inc.php';
include('header.php');
include('nav.php');

if (!isset($_SESSION['customers_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../scripts/login.php');
    exit;
}

$flat_id = $_GET['id'] ?? null;
$error = '';
$success = '';
$show_payment_form = false;

if ($flat_id) {
    $stmt = $pdo->prepare("SELECT f.*, o.id AS owner_id, o.name AS owner_name, o.address AS owner_address, o.mobile AS owner_mobile
                           FROM flats f JOIN owners o ON f.owner_id = o.id
                           WHERE f.id = :id AND f.is_approved = 1");
    $stmt->execute([':id' => $flat_id]);
    $flat = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $flat = false;
}

if (!$flat) {
    echo "<main class='main-content'><p>Flat not found or not approved.</p></main>";
    include('footer.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_payment'])) {
        // Final confirmation
        $start = $_SESSION['rent_start'];
        $end = $_SESSION['rent_end'];
        $card = $_POST['card'];
        
        if (!preg_match('/^\d{9}$/', $card)) {
            $error = "Credit card must be exactly 9 digits.";
            $show_payment_form = true;
        } else {
            $stmt = $pdo->prepare("INSERT INTO rentals (flat_id, customer_id, start_date, end_date, credit_card)
                                   VALUES (:flat, :cust, :start, :end, :card)");
            $stmt->execute([
                ':flat' => $flat_id,
                ':cust' => $_SESSION['customers_id'],
                ':start' => $start,
                ':end' => $end,
                ':card' => $card
            ]);
            $pdo->prepare("UPDATE flats SET is_rented = 1 WHERE id = :id")->execute([':id' => $flat_id]);
            
            $message = "Your rental for Flat #{$flat['reference_number']} has been confirmed.\nYou can collect the key from {$flat['owner_name']} (📞 {$flat['owner_mobile']}).";
            $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)")
            ->execute([$_SESSION['customers_id'], 'customers', $message]);
            
            $pdo->prepare("INSERT INTO notifications (user_id, role, message) VALUES (?, ?, ?)")
            ->execute([$flat['owner_id'], 'owners', "{$flat['owner_name']}, {$flat['monthly_rent']} flat has been rented by customer #{$_SESSION['customers_id']}."]);
            
            unset($_SESSION['rent_start'], $_SESSION['rent_end']);
            $success = $message;
        }
    } else {
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];
        $start_date = DateTime::createFromFormat('Y-m-d', $start);
        $end_date = DateTime::createFromFormat('Y-m-d', $end);
        $min_end = (clone $start_date)->modify('+1 month');
        
        if (!$start_date || $start_date < new DateTime()) {
            $error = "Start date must be today or later.";
        } elseif (!$end_date || $end_date < $min_end) {
            $error = "End date must be at least 1 month after start date.";
        } else {
            $_SESSION['rent_start'] = $start;
            $_SESSION['rent_end'] = $end;
            $show_payment_form = true;
        }
    }
}
?>

<main class="main-content">
    <h2>Rent Flat #<?= htmlspecialchars($flat['reference_number']) ?></h2>

    <?php if ($error): ?>
        <p class="error-msg"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p class="success-msg"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <section class="rent-summary">
        <p><strong>Flat ID:</strong> <?= $flat['id'] ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($flat['location']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($flat['address']) ?></p>
        <p><strong>Monthly Rent:</strong> $<?= $flat['monthly_rent'] ?></p>
        <p><strong>Owner Name:</strong> <a href="../views/user_card.php?id=<?= $flat['owner_id'] ?>" target="_blank"><?= $flat['owner_name'] ?></a></p>
        <p><strong>Owner ID:</strong> <?= $flat['owner_id'] ?></p>
        <p><strong>Owner Address:</strong> <?= htmlspecialchars($flat['owner_address']) ?></p>
    </section>

    <?php if (!$success && !$show_payment_form): ?>
        <form method="POST" class="profile-form">
            <label>Start Date:
                <input type="date" name="start_date" required>
            </label>
            <label>End Date:
                <input type="date" name="end_date" required>
            </label>
            <button type="submit" class="cta-button">Continue</button>
        </form>
    <?php elseif ($show_payment_form): ?>
        <form method="POST" class="profile-form">
            <label>Credit Card Number (9 digits):
                <input type="text" name="card" pattern="\d{9}" required>
            </label>
            <button type="submit" name="confirm_payment" class="cta-button">Confirm Rent</button>
        </form>
    <?php endif; ?>
</main>

<?php include('footer.php'); ?>
