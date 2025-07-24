<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include ('header.php');
include ('nav.php');
require_once '../includes/dbconfig.inc.php';

$flat_id = $_GET['id'] ?? 1;

$stmt = $pdo->prepare("SELECT * FROM flats WHERE id = :id AND is_approved = 1");
$stmt->execute([
    ':id' => $flat_id
]);
$flat = $stmt->fetch(PDO::FETCH_ASSOC);

if (! $flat) {
    echo "<main class='main-content'><p>Flat not found or not approved yet.</p></main>";
    include ('footer.php');
    exit();
}

function findImage($basePath)
{
    $extensions = [
        '.jpg',
        '.jpeg',
        '.png'
    ];
    foreach ($extensions as $ext) {
        if (file_exists($basePath . $ext)) {
            return $basePath . $ext;
        }
    }
    return '../images/placeholder.jpg';
}

$base = '../images/';
$living_img = findImage($base . "living{$flat['id']}");
$kitchen_img = findImage($base . "kitchen{$flat['id']}");
$bedroom_img = findImage($base . "bedroom{$flat['id']}");
?>

<main class="flat-main">
	<link rel="stylesheet" href="../css/style.css">

	<h2>Flat Details</h2>

	<section class="flatcard">
		<div class="flat-photos">
			<figure>
				<img src="<?php echo $living_img; ?>" alt="Living Room">
				<figcaption>Living Room</figcaption>
			</figure>
			<figure>
				<img src="<?php echo $kitchen_img; ?>" alt="Kitchen">
				<figcaption>Kitchen</figcaption>
			</figure>
			<figure>
				<img src="<?php echo $bedroom_img; ?>" alt="Bedroom">
				<figcaption>Bedroom</figcaption>
			</figure>
		</div>

		<div class="flat-details">
			<p>
				<strong>Address:</strong> <?php echo htmlspecialchars($flat['address']); ?></p>
			<p>
				<strong>Price:</strong> $<?php echo htmlspecialchars($flat['monthly_rent']); ?>/month</p>
			<p>
				<strong>Rental Conditions:</strong> <?= htmlspecialchars($flat['rent_conditions'] ?? '—') ?></p>
			<p>
				<strong>Size:</strong> <?= htmlspecialchars($flat['size_sqm']) ?> m²</p>
			<p>
				<strong>Heating:</strong> <?= $flat['has_heating'] ? 'Available' : 'Not available' ?></p>
			<p>
				<strong>Air Conditioning:</strong> <?= $flat['has_air_condition'] ? 'Yes' : 'No' ?></p>
			<p>
				<strong>Access Control:</strong> <?= $flat['has_access_control'] ? 'Secured building entry' : 'No' ?></p>

			<p>
				<strong>Extra Features:</strong>
    <?= $flat['has_parking'] ? 'Parking, ' : '' ?>
    <?= $flat['backyard_type'] ? ucfirst($flat['backyard_type']) . ' backyard, ' : '' ?>
    <?= $flat['has_playground'] ? 'Playground, ' : '' ?>
    <?= $flat['has_storage'] ? 'Storage' : '' ?>
</p>


            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customers'): ?>
                <div class="side-links">
				<a href="../scripts/rent_flat.php?id=<?php echo $flat['id']; ?>"
					class="details-link">🏠 Rent This Flat</a> <a
					href="request_appointment.php?id=<?php echo $flat['id']; ?>"
					class="details-link">📅 Request Appointment</a>
			</div>
            <?php endif; ?>
        </div>

		<aside class="flat-aside">
			<h3>Nearby Landmarks</h3>
			<ul>
				<li><a href="https://www.google.com/maps/place/Birzeit+University/@31.9591925,35.1794322,17z/data=!3m1!4b1!4m6!3m5!1s0x151d2bf5d33c13f3:0x4293b4ea8be2cf6e!8m2!3d31.9591925!4d35.1820071!16s%2Fg%2F11t9s3ttxq?entry=ttu&g_ep=EgoyMDI1MDYxMC4xIKXMDSoASAFQAw%3D%3D"
					target="_blank"> Birzeit University</a></li>
				<li><a href="https://www.google.com/maps/place/Shot+Market/@31.9677395,35.1865038,15.78z/data=!4m10!1m2!2m1!1sBirzeit+market!3m6!1s0x151d2b3ea9bbf1d3:0x92fd91aecf064c61!8m2!3d31.9653287!4d35.1949399!15sCg5CaXJ6ZWl0IG1hcmtldJIBDWdyb2Nlcnlfc3RvcmWqAUMQASoKIgZtYXJrZXQoADIfEAEiG9rOeVYi4oINrav_J_7y3v_fHWgu1Bp70roi1DISEAIiDmJpcnplaXQgbWFya2V04AEA!16s%2Fg%2F11t7c8d1lx?entry=ttu&g_ep=EgoyMDI1MDYxMC4xIKXMDSoASAFQAw%3D%3D"
					target="_blank"> Shot Market </a></li>
				<li><a href="https://www.google.com/maps/place/Birzeit+Pharmacy/@31.9677516,35.1916359,17z/data=!4m9!1m2!2m1!1sBirzeit+market!3m5!1s0x151d2a17fa47f98d:0xe5cb619e3fe1b23b!8m2!3d31.9708913!4d35.193475!16s%2Fg%2F11f1lqtzgg?entry=ttu&g_ep=EgoyMDI1MDYxMC4xIKXMDSoASAFQAw%3D%3D" target="_blank">
						Birzeit Pharmacy</a></li>
			</ul>
		</aside>
	</section>
</main>

<?php include('footer.php'); ?>
