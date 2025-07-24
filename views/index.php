<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
?>
<link rel="stylesheet" href="../css/style.css">

<main class="home-main">
    <section class="hero-section">
        <h2>Welcome to <span class="brand-highlight">Birzeit Flat Rent</span></h2>
<p class="hero-subtitle">Find your next home today — Easy, Fast, and Reliable!</p>
    
    <p><a href="../scripts/register_customer_step1.php" class="cta-button">Sign Up as Customer</a></p>
    <p><a href="../scripts/register_owner_step1.php" class="cta-button">Sign Up as Owner</a></p>
    
        </section>
    
    

    <section class="features-section">
        <h3>Why Choose Us?</h3>
        <ul class="feature-list">
            <li>🔍 Smart Search Filters for price, location, bedrooms, and more</li>
            <li>📅 Request flat previews and manage your appointments online</li>
            <li>📝 Transparent rental conditions with clear photos and details</li>
            <li>📩 Messaging system to connect with owners and managers</li>
        </ul>
    </section>

    
</main>

<?php include('footer.php'); ?>
