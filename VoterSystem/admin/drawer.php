<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    return;
}
$user = $_SESSION['user'];
?>
<head>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<!-- Styled Bootstrap Offcanvas Component -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasDrawer" aria-labelledby="offcanvasDrawerLabel" style="background: rgba(255,255,255,0.95);">
  <div class="offcanvas-header" style="border-bottom: 1px solid #ccc;">
    <!-- Resized Logo -->
    <i class='bx bxs-user-circle' style="font-size: 5rem; margin-right: 5px;"></i>
    <h5 class="offcanvas-title" id="offcanvasDrawerLabel" style="font-weight: bold;">User Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="mb-3" style="padding: 10px;">
      <p style="font-size: 1.2rem; font-weight: bold;">Hello, <?= htmlspecialchars($user['name']); ?>!</p>
      <p style="font-size: 1rem;">Role: <?= htmlspecialchars($user['role']); ?></p>
    </div>
    <hr>
    <ul class="list-group">
      <?php if ($user['role'] === 'admin'): ?>
        <li class="list-group-item">
          <a href="dashboard.php" style="text-decoration: none; color: inherit;">Dashboard</a>
        </li>
        <li class="list-group-item">
          <a href="candidates.php" style="text-decoration: none; color: inherit;">Manage Candidates</a>
        </li>
        <li class="list-group-item">
          <a href="winner.php" style="text-decoration: none; color: inherit;">Voter Result</a>
        </li>
      <?php else: ?>
        <li class="list-group-item">
          <a href="vote.php" style="text-decoration: none; color: inherit;">Vote Now</a>
        </li>
        <li class="list-group-item">
          <a href="winner.php" style="text-decoration: none; color: inherit;">Voter Result</a>
        </li>
      <?php endif; ?>
      <li class="list-group-item">
        <a href="../logout.php" style="text-decoration: none; color: inherit;">Logout</a>
      </li>
    </ul>
  </div>
</div>
