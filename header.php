<?php
if (session_status() == PHP_SESSION_NONE) session_start();




?>
<div class="container">
  <div class="topbar" role="navigation" aria-label="Main">
    <div class="left">
      <a href="view.php">Home</a>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') { echo ' | <a href="admin.php">Admin</a>'; } ?>
      <?php if (isset($_SESSION['user'])) { echo ' | <a href="messages.php">Messages</a>'; } ?>
    </div>
    <div class="right" aria-hidden="false">
      <?php if (isset($_SESSION['user'])): ?>
        <span class="greeting">Hi, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a> | <a href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</div>
