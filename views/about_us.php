<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
?>
<link rel="stylesheet" href="../css/style.css">

<main class="about-main">
    <h2>About Us</h2>

    <!-- ✅ Agency Section -->
    <section class="about-section">
        <h3>The Agency</h3>
        <p>
            Founded in 2024, <strong>Birzeit Flat Rent</strong> is a digital rental agency based in Palestine, offering secure and convenient rental services. 
            Our mission is to streamline the flat renting experience by connecting tenants and property owners through a transparent and efficient system.
        </p>
        <p>
            In 2025, we received a national innovation award for our outstanding contribution to real estate digitalization.
            Our leadership team consists of certified real estate consultants, system engineers, and a dedicated customer support team that ensures every client receives professional assistance.
        </p>
    </section>

    <!-- City Section -->
    <section class="about-section">
        <h3>The City</h3>
        <p>
            <strong>Birzeit</strong>, located north of Ramallah in the West Bank, is known for its cultural richness and intellectual heritage. 
            It is home to <strong>Birzeit University</strong>, a prominent academic institution attracting students from across the region.
        </p>
        <p>
            The town features picturesque hills, a temperate Mediterranean climate, and local landmarks like the old town market and olive groves.
            Famous products include olive oil, soap, and traditional embroidery.
        </p>
        <p>
            Notable figures from Birzeit include educators, artists, and social reformers. Learn more at 
            <a href="https://en.wikipedia.org/wiki/Birzeit" target="_blank" rel="noopener noreferrer">Wikipedia</a>.
        </p>
    </section>

    <!-- Main Business Activities -->
    <section class="about-section">
        <h3>Main Business Activities</h3>
        <ul class="business-list">
            <li>🏠 Listing of available flats and rental offers</li>
            <li>📅 Flat preview scheduling and appointments</li>
            <li>💬 Communication between customers and flat owners</li>
            <li>🔐 Secure rental and payment processing</li>
            <li>🧾 Managing tenant histories and past rentals</li>
            <li>📩 Messaging and notifications between roles</li>
            <li>📊 Managerial oversight for rental approvals</li>
        </ul>
    </section>
</main>

<?php include('footer.php'); ?>
