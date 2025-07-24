<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="../css/style.css">

<nav class="side-nav">
	<ul class="nav-list">
		<li><a href="index.php" class="nav-link">Home</a></li>

    <?php if (isset($_SESSION['user_role'])): ?>

        <?php if ($_SESSION['user_role'] === 'customers'): ?>
            <li><a href="search_flats.php" class="nav-link">Search Flats</a></li>
		<li><a href="view_rented.php" class="nav-link">My Rentals</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['user_role'] === 'owners'): ?>
            <li><a href="add_flat.php" class="nav-link">Add Flat</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['user_role'] === 'managers'): ?>
      <li><a href="manage_flats.php" class="nav-link">Approve Flats</a></li>
		<li><a href="view_all_appointments.php" class="nav-link">View
				Appointments</a></li>
				<li><a href="inquire_flats.php" class="nav-link"> Inquire Flats</a></li>

      
    <?php endif; ?>
<?php if ($_SESSION['user_role'] === 'owners'): ?>
    <li><a href="view_appointments.php" class="nav-link">Appointments</a></li>
		<li><a href="view_my_flats.php" class="nav-link">My Flats</a></li>
		
<?php endif; ?>

        <li><a href="view_profile.php" class="nav-link">Profile</a></li>
		<li><a href="messages.php" class="nav-link">Messages</a></li>
		<li><a href="send_message.php" class="details-link">️Send a Message</a></li>

		<li><a href="notifications.php" class="nav-link">Notifications</a></li>

    <?php else: ?>
        <li><a href="../scripts/login.php" class="nav-link">Login</a></li>
    <?php endif; ?>
  </ul>
</nav>
