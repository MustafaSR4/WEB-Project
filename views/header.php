<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Role name mapping
$roleDisplayNames = [
    'customers' => 'Customer',
    'owners' => 'Owner',
    'managers' => 'Manager'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Birzeit Flat Rent</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="site-header">
  <div class="header-container">
    <div class="logo-title">
      <img src="../images/logo.jpg" alt="Birzeit Flat Rent Logo" class="logo-img">
      <h1 class="site-title">Birzeit Flat Rent</h1>
    </div>

    <nav class="header-nav">
      <?php if (isset($_SESSION['username']) && isset($_SESSION['user_role'])): ?>
        <div class="user-greeting">
          <span>
            Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
            (<?php
              $roleKey = $_SESSION['user_role'];
              echo htmlspecialchars($roleDisplayNames[$roleKey] ?? 'User');
            ?>)
          </span> |
          <a href="../views/view_profile.php" class="nav-link profile-link">Profile</a> |
          <a href="../scripts/logout.php" class="nav-link logout-link">Logout</a> |
          <a href="about_us.php" class="nav-link">About Us</a>
        </div>
      <?php else: ?>
        <div class="user-greeting">
          <a href="../scripts/login.php" class="nav-link login-link">Login</a> |
          <a href="about_us.php" class="nav-link about-link">About Us</a>
        </div>
      <?php endif; ?>
    </nav>
  </div>
  <hr class="header-divider">
</header>
