<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
require_once '../includes/dbconfig.inc.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'managers') {
    echo "<main class='main-content'><p>Access denied. Only managers can view this page.</p></main>";
    include('footer.php');
    exit;
}

$params = [];
$where = [];

if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $where[] = "r.start_date >= :from_date AND r.end_date <= :to_date";
    $params[':from_date'] = $_GET['from_date'];
    $params[':to_date'] = $_GET['to_date'];
}
if (!empty($_GET['location'])) {
    $where[] = "f.location LIKE :location";
    $params[':location'] = '%' . $_GET['location'] . '%';
}
if (!empty($_GET['available_on'])) {
    $where[] = "r.start_date <= :available_on AND r.end_date >= :available_on";
    $params[':available_on'] = $_GET['available_on'];
}
if (!empty($_GET['owner_id'])) {
    $where[] = "f.owner_id = :owner_id";
    $params[':owner_id'] = $_GET['owner_id'];
}
if (!empty($_GET['customer_id'])) {
    $where[] = "r.customer_id = :customer_id";
    $params[':customer_id'] = $_GET['customer_id'];
}

$sql = "
SELECT r.*, f.id AS flat_id, f.location, f.monthly_rent,
       o.id AS owner_id, o.name AS owner_name, o.city AS owner_city, o.email AS owner_email, o.mobile AS owner_mobile,
       c.id AS customer_id, c.name AS customer_name, c.address AS customer_address, c.email AS customer_email, c.mobile AS customer_mobile, c.phone AS customer_phone
FROM rentals r
JOIN flats f ON r.flat_id = f.id
JOIN owners o ON f.owner_id = o.id
JOIN customers c ON r.customer_id = c.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY r.start_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
    <link rel="stylesheet" href="../css/style.css">
    <h2>Inquire Flats</h2>

    <section class="search-section">
        <form method="GET" class="filter-form">
            <label>From Date: <input type="date" name="from_date" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>"></label>
            <label>To Date: <input type="date" name="to_date" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>"></label>
            <label>Location: <input type="text" name="location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>"></label>
            <label>Available On: <input type="date" name="available_on" value="<?= htmlspecialchars($_GET['available_on'] ?? '') ?>"></label>
            <label>Owner ID: <input type="number" name="owner_id" value="<?= htmlspecialchars($_GET['owner_id'] ?? '') ?>"></label>
            <label>Customer ID: <input type="number" name="customer_id" value="<?= htmlspecialchars($_GET['customer_id'] ?? '') ?>"></label>
            <button type="submit">Filter</button>
        </form>

        <?php if (count($results) === 0): ?>
            <p>No matching flats found.</p>
        <?php else: ?>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Rent ($)</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Location</th>
                        <th>Owner</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr>
                            <td>
                                <a href="flat_details.php?id=<?= $r['flat_id'] ?>" class="details-link" target="_blank">#<?= $r['flat_id'] ?></a>
                            </td>
                            <td><?= $r['monthly_rent'] ?></td>
                            <td><?= $r['start_date'] ?></td>
                            <td><?= $r['end_date'] ?></td>
                            <td><?= htmlspecialchars($r['location']) ?></td>
                            <td>
                                <a href="user_card.php?id=<?= $r['owner_id'] ?>" class="owner-link" target="_blank">
                                    <?= htmlspecialchars($r['owner_name']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="user_card.php?id=<?= $r['customer_id'] ?>" class="owner-link" target="_blank">
                                    <?= htmlspecialchars($r['customer_name']) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<?php include('footer.php'); ?>
