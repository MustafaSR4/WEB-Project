<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/dbconfig.inc.php';
include('header.php');
include('nav.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owners') {
    echo "<p class='error-message'>You must be logged in as an owner to access this page.</p>";
    include('footer.php');
    exit;
}

$owner_id = $_SESSION['owners_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $monthly_rent = $_POST['monthly_rent'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? '';
    $bathrooms = $_POST['bathrooms'] ?? '';
    $furnished = isset($_POST['furnished']) ? 1 : 0;
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $size = $_POST['size'] ?? '';
    $rent_conditions = trim($_POST['rent_conditions'] ?? '');
    $heating = isset($_POST['heating']) ? 1 : 0;
    $air_condition = isset($_POST['air_condition']) ? 1 : 0;
    $access_control = isset($_POST['access_control']) ? 1 : 0;
    $parking = isset($_POST['parking']) ? 1 : 0;
    $playground = isset($_POST['playground']) ? 1 : 0;
    $storage = isset($_POST['storage']) ? 1 : 0;
    $backyard = $_POST['backyard'] ?? '';
    
    if ($location && $address && is_numeric($monthly_rent) && is_numeric($bedrooms) && is_numeric($bathrooms) && is_numeric($size)) {
        $stmt = $pdo->prepare("INSERT INTO flats
            (owner_id, location, address, monthly_rent, bedrooms, bathrooms, is_furnished, rent_conditions, size_sqm, has_heating, has_air_condition, has_access_control, has_parking, has_playground, has_storage, backyard_type, start_date, end_date, is_approved, is_rented, created_at)
            VALUES
            (:owner_id, :location, :address, :rent, :bedrooms, :bathrooms, :furnished, :conditions, :size, :heating, :ac, :access, :parking, :playground, :storage, :backyard, :start, :end, 0, 0, NOW())");
        
        $stmt->execute([
            ':owner_id' => $owner_id,
            ':location' => $location,
            ':address' => $address,
            ':rent' => $monthly_rent,
            ':bedrooms' => $bedrooms,
            ':bathrooms' => $bathrooms,
            ':furnished' => $furnished,
            ':conditions' => $rent_conditions,
            ':size' => $size,
            ':heating' => $heating,
            ':ac' => $air_condition,
            ':access' => $access_control,
            ':parking' => $parking,
            ':playground' => $playground,
            ':storage' => $storage,
            ':backyard' => $backyard,
            ':start' => $start_date,
            ':end' => $end_date
        ]);
        
        $message = "Flat submitted successfully. Waiting for manager approval.";
    } else {
        $message = "Please fill in all required fields with valid values.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Flat</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="main-content">
    <h2>Add a New Flat for Rent</h2>

    <?php if ($message): ?>
        <p class="<?= str_starts_with($message, 'Flat submitted') ? 'success-message' : 'error-message'; ?>">
            <?= htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST" class="profile-form">
        <label>Location:
            <input type="text" name="location" required>
        </label>
        <label>Address:
            <input type="text" name="address" required>
        </label>
        <label>Monthly Rent (USD):
            <input type="number" name="monthly_rent" step="0.01" min="0" required>
        </label>
        <label>Bedrooms:
            <input type="number" name="bedrooms" min="0" required>
        </label>
        <label>Bathrooms:
            <input type="number" name="bathrooms" min="0" required>
        </label>
        <label>Flat Size (m²):
            <input type="number" name="size" min="0" required>
        </label>
        <label>Available From:
            <input type="date" name="start_date" required>
        </label>
        <label>Available Until:
            <input type="date" name="end_date" required>
        </label>
        <label>Rent Conditions:
            <textarea name="rent_conditions" rows="3"></textarea>
        </label>
        <label><input type="checkbox" name="furnished" value="1"> Furnished</label>
        <label><input type="checkbox" name="heating" value="1"> Heating System</label>
        <label><input type="checkbox" name="air_condition" value="1"> Air Conditioning</label>
        <label><input type="checkbox" name="access_control" value="1"> Access Control</label>
        <label><input type="checkbox" name="parking" value="1"> Car Parking</label>
        <label><input type="checkbox" name="playground" value="1"> Playground</label>
        <label><input type="checkbox" name="storage" value="1"> Storage</label>
        <label>Backyard Type:
            <select name="backyard">
                <option value="">None</option>
                <option value="individual">Individual</option>
                <option value="shared">Shared</option>
            </select>
        </label>
        <button type="submit">Submit Flat</button>
    </form>
</main>
</body>
</html>
<?php include('footer.php'); ?>
