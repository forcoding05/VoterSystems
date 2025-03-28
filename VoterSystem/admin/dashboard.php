<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/AdminDashboard.css">
  <style>
      .timer-container {
    width: 20%;
    padding: 15px;
    border: 2px solid #007bff;
    border-radius: 10px;
    text-align: center;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    top: -6em;
    left: 8em;
}

.timer-container h4 {
    font-weight: bold;
    color: white;
}

.timer-container input {
    width: 100%;
    padding: 8px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    text-align: center;
}

.timer-container button {
    width: 100%;
    padding: 8px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.timer-container button:hover {
    background: #0056b3;
}

.timer-container p {
    margin-top: 10px;
    font-weight: bold;
    color: #dc3545;
}

.dashboard-header {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    margin-top: -75px;
  }
  </style>
</head>
<body>
  <div class="container mt-5">
    <!-- Header with Offcanvas Toggle and Logout -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDrawer" aria-controls="offcanvasDrawer">
        Menu
      </button>
      <h1>Admin Dashboard</h1>
      <a href="../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
  <!-- Voting Timer Section -->
  <div class="timer-container">
    <h4>Set Voting Timer</h4>
    <input type="number" id="timerInput" placeholder="Enter minutes">
    <button class="btn btn-primary" onclick="setVotingTime()">Set Timer</button>
    <p>Time Left: <span id="timerDisplay">--</span></p>
  </div>

    <!-- Include the navigation drawer -->
    <?php include 'drawer.php'; ?>
    
    <!-- Dashboard Welcome Header -->
    <div class="dashboard-header text-center">
      <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']); ?>!</h2>
      <p>Manage your voters and candidates from this central hub.</p>
    </div>
    
    <!-- Registered Voters Section -->
    <div class="mb-5">
      <h4>Registered Voters</h4>
      <?php
        $stmt = $pdo->prepare("SELECT id, name, email, has_voted FROM users WHERE role = 'voter'");
        $stmt->execute();
        $voters = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Voted?</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($voters as $voter): ?>
            <tr>
              <td><?= htmlspecialchars($voter['name']) ?></td>
              <td><?= htmlspecialchars($voter['email']) ?></td>
              <td><?= $voter['has_voted'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning text-dark">No</span>' ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Voting Results Section -->
    <div>
      <h4>Voting Results</h4>
      <?php
        $stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY votes DESC");
        $stmt->execute();
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Party</th>
              <th>Votes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($candidates as $candidate): ?>
            <tr>
              <td><?= htmlspecialchars($candidate['name']) ?></td>
              <td><?= htmlspecialchars($candidate['party']) ?></td>
              <td><?= $candidate['votes'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap JS Bundle (with Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/timer.js"></script>
</body>
</html>
