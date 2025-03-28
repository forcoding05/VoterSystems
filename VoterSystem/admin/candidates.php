<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Define allowed parties array
$allowedParties = [
    "President",
    "Vice President",
    "Tresurer",
    "Security"
];

// Handle candidate operations
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $name  = trim($_POST['name']);
        $party = trim($_POST['party']);
        if ($name && $party) {
            // Validate that the selected party is allowed
            if (!in_array($party, $allowedParties)) {
                $error = "Invalid candidate party.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO candidates (name, party) VALUES (?, ?)");
                $stmt->execute([$name, $party]);
                header("Location: candidates.php");
                exit();
            }
        }
    } elseif ($_POST['action'] == 'delete') {
        // Validate candidate ID
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id === false) {
            die("Invalid candidate ID.");
        }
        
        // Check if candidate has any votes
        $stmt = $pdo->prepare("SELECT votes FROM candidates WHERE id = ?");
        $stmt->execute([$id]);
        $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($candidate && $candidate['votes'] > 0) {
            die("Cannot delete candidate because they have already received votes.");
        }
        
        // If no votes, proceed with deletion
        $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: candidates.php");
        exit();
    } elseif ($_POST['action'] == 'edit') {
        $id    = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $name  = trim($_POST['name']);
        $party = trim($_POST['party']);
        if ($name && $party && $id !== false) {
            if (!in_array($party, $allowedParties)) {
                $error = "Invalid candidate party.";
            } else {
                $stmt = $pdo->prepare("UPDATE candidates SET name = ?, party = ? WHERE id = ?");
                $stmt->execute([$name, $party, $id]);
                header("Location: candidates.php");
                exit();
            }
        }
    }
}

// Fetch all candidates for display
$stmt = $pdo->prepare("SELECT * FROM candidates");
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Candidate Management</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/AdminCandidates.css">
</head>
<body class="container mt-5">
  <div class="header-section">
    <a href="dashboard.php" class="btn btn-outline-light">Back to Dashboard</a>
    <h1>Candidate Management</h1>
    <a href="../logout.php" class="btn btn-danger">Logout</a>
  </div>
  
  <!-- Dashboard Welcome Header -->
  <div class="dashboard-header">
    <h2>Manage Your Candidates</h2>
    <p>Add, edit, or delete candidates as needed.</p>
  </div>
  
  <!-- Add Candidate Form -->
  <div class="card">
    <h4 class="mb-3">Add Candidate</h4>
    <form method="post" action="candidates.php">
        <input type="hidden" name="action" value="add">
        <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="Candidate Name" required>
        </div>
        <div class="mb-3">
            <select name="party" class="form-control" required>
                <option value="">Position</option>
                <?php foreach ($allowedParties as $pos): ?>
                    <option value="<?= htmlspecialchars($pos) ?>"><?= htmlspecialchars($pos) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Candidate</button>
    </form>
  </div>
  
  <!-- Candidate List -->
  <div class="card">
    <h4 class="mb-3">Existing Candidates</h4>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Position</th>
            <th>Votes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($candidates as $candidate): ?>
          <tr>
            <td><?= htmlspecialchars($candidate['name']) ?></td>
            <td><?= htmlspecialchars($candidate['party']) ?></td>
            <td><?= $candidate['votes'] ?></td>
            <td>
              <!-- Edit Form -->
              <form method="post" action="candidates.php" class="d-inline-block">
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
                  <div class="input-group">
                      <input type="text" name="name" value="<?= htmlspecialchars($candidate['name']) ?>" class="form-control" required>
                      <select name="party" class="form-control" required>
                          <?php foreach ($allowedParties as $pos): ?>
                            <option value="<?= htmlspecialchars($pos) ?>" <?= ($candidate['party'] == $pos) ? 'selected' : '' ?>>
                              <?= htmlspecialchars($pos) ?>
                            </option>
                          <?php endforeach; ?>
                      </select>
                      <button type="submit" class="btn btn-warning btn-sm">Edit</button>
                  </div>
              </form>
              <!-- Delete Form -->
              <form method="post" action="candidates.php" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
