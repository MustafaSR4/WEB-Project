<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



include('header.php');
include('nav.php');
include '../includes/dbconfig.inc.php';

$valid_sort_columns = ['reference_number', 'location', 'monthly_rent'];
$sort = in_array($_GET['sort'] ?? '', $valid_sort_columns) ? $_GET['sort'] : ($_COOKIE['sort'] ?? 'monthly_rent');
$dir = ($_GET['dir'] ?? ($_COOKIE['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

setcookie('sort', $sort, time() + 3600);
setcookie('dir', $dir, time() + 3600);

$params = [];
$available_after = $_GET['available_after'] ?? null;

// SQL
$sql = "
SELECT f.*, MAX(r.end_date) AS latest_end
FROM flats f
LEFT JOIN rentals r ON f.id = r.flat_id
WHERE f.is_approved = 1 AND f.reference_number IS NOT NULL
";

if ($available_after) {
    $sql .= " AND (
        f.is_rented = 0
        OR (f.is_rented = 1 AND r.end_date <= :available_after)
    )";
    $params[':available_after'] = $available_after;
} else {
    $sql .= " AND f.is_rented = 0";
}

// Filters
if (!empty($_GET['location'])) {
    $sql .= " AND f.location LIKE :location";
    $params[':location'] = '%' . $_GET['location'] . '%';
}
if (!empty($_GET['address'])) {
    $sql .= " AND f.address LIKE :address";
    $params[':address'] = '%' . $_GET['address'] . '%';
}
if (!empty($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $sql .= " AND f.monthly_rent >= :min_price";
    $params[':min_price'] = $_GET['min_price'];
}
if (!empty($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $sql .= " AND f.monthly_rent <= :max_price";
    $params[':max_price'] = $_GET['max_price'];
}
if (!empty($_GET['bedrooms']) && is_numeric($_GET['bedrooms'])) {
    $sql .= " AND f.bedrooms = :bedrooms";
    $params[':bedrooms'] = $_GET['bedrooms'];
}
if (!empty($_GET['bathrooms']) && is_numeric($_GET['bathrooms'])) {
    $sql .= " AND f.bathrooms = :bathrooms";
    $params[':bathrooms'] = $_GET['bathrooms'];
}
if (isset($_GET['furnished']) && in_array($_GET['furnished'], ['yes', 'no'])) {
    $sql .= " AND f.is_furnished = :furnished";
    $params[':furnished'] = $_GET['furnished'] === 'yes' ? 1 : 0;
}

$sql .= " GROUP BY f.id ORDER BY $sort $dir";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$flats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function for column sorting links
function sort_column($column, $label, $currentSort, $currentDir)
{
    $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    $icon = '';
    if ($currentSort === $column) {
        $icon = $currentDir === 'asc' ? ' ▲' : ' ▼';
    }
    $queryParams = $_GET;
    $queryParams['sort'] = $column;
    $queryParams['dir'] = $newDir;
    $url = '?' . http_build_query($queryParams);
    return "<a href=\"$url\">$label$icon</a>";
}
?>

<main class="main-content">
    <link rel="stylesheet" href="../css/style.css">
    <h2>Search Flats</h2>

    <section class="search-section">
        <form method="GET" class="filter-form">
            <label>Location: <input type="text" name="location" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>"></label>
            <label>Address: <input type="text" name="address" value="<?= htmlspecialchars($_GET['address'] ?? '') ?>"></label>
            <label>Min Price: <input type="number" name="min_price" min="0" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>"></label>
            <label>Max Price: <input type="number" name="max_price" min="0" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>"></label>
            <label>Bedrooms: <input type="number" name="bedrooms" min="0" value="<?= htmlspecialchars($_GET['bedrooms'] ?? '') ?>"></label>
            <label>Bathrooms: <input type="number" name="bathrooms" min="0" value="<?= htmlspecialchars($_GET['bathrooms'] ?? '') ?>"></label>
            <label>Furnished:
                <select name="furnished">
                    <option value="">Any</option>
                    <option value="yes" <?= ($_GET['furnished'] ?? '') === 'yes' ? 'selected' : '' ?>>Yes</option>
                    <option value="no" <?= ($_GET['furnished'] ?? '') === 'no' ? 'selected' : '' ?>>No</option>
                </select>
            </label>
            <label>Available After:
                <input type="date" name="available_after" value="<?= htmlspecialchars($_GET['available_after'] ?? '') ?>">
            </label>
            <button type="submit" class="filter-button">Filter</button>
            
        </form>

        <?php if (count($flats) === 0): ?>
            <p>No flats found matching the criteria.</p>
        <?php else: ?>
            <table class="results-table">
                <thead>
                    <tr>
                        <th><?= sort_column('reference_number', 'Ref #', $sort, $dir) ?></th>
                        <th><?= sort_column('location', 'Location', $sort, $dir) ?></th>
                        <th>Address</th>
                        <th><?= sort_column('monthly_rent', 'Rent ($)', $sort, $dir) ?></th>
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Furnished</th>
                        <th>Available From</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($flats as $flat): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($flat['reference_number']) ?></td>
                            <td><?= htmlspecialchars($flat['location']) ?></td>
                            <td><?= htmlspecialchars($flat['address']) ?></td>
                            <td><?= $flat['monthly_rent'] ?></td>
                            <td><?= $flat['bedrooms'] ?></td>
                            <td><?= $flat['bathrooms'] ?></td>
                            <td><?= $flat['is_furnished'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <?php
                                if (!$flat['is_rented']) {
                                    echo "<span class='available-now'>Immediately</span>";
                                } else {
                                    echo "<span class='available-later'>" . htmlspecialchars($flat['latest_end']) . "</span>";
                                }
                                ?>
                            </td>
                            <td><a href="flat_details.php?id=<?= $flat['id'] ?>" target="_blank" class="details-link">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<?php include('footer.php'); ?>
<!--  if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customers') { -->
<!--      echo "<main class='main-content'><p>You must be logged in as a customer to access this page.</p></main>"; -->
<!--     include('footer.php'); -->
<!--      exit; -->
<!--  } -->
