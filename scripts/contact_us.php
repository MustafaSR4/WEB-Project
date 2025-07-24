<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('header.php');
include('nav.php');
?>

<main class="main-content">
    <link rel="stylesheet" href="../css/style.css">

    <h2>Contact Us</h2>
<?php if (isset($_GET['sent'])): ?>
    <p class="success-msg">Your message has been sent successfully. Thank you!</p>
<?php elseif (isset($_GET['error'])): ?>
    <p class="error-msg">Please fill all fields correctly before submitting.</p>
<?php endif; ?>

    <section class="contact-info">
        <p><strong>Birzeit Flat Rent Agency</strong></p>
        <p>Address: Main Street, Birzeit, Palestine</p>
        <p>Email: <a href="mailto:info@birzeitflatrent.ps">info@birzeitflatrent.ps</a></p>
        <p>Phone: <a href="tel:+97022987654">+970 2 298 7654</a></p>
    </section>

    <section class="contact-form-section">
        <h3>Send Us a Message</h3>
	<form method="POST" action="send_contact_message.php" class="contact-form">
            <label>Your Name:
                <input type="text" name="name" required>
            </label>
            <label>Your Email:
                <input type="email" name="email" required>
            </label>
            <label>Subject:
                <input type="text" name="subject" required>
            </label>
            <label>Message:
                <textarea name="message" rows="5" required></textarea>
            </label>
            <button type="submit">Send Message</button>
        </form>
    </section>
</main>

<?php include('footer.php'); ?>
