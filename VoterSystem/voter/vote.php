<?php
session_start();
require_once '../db.php';

// Check if voter is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'voter') {
    header("Location: ../login.php");
    exit();
}

$voter_id = $_SESSION['user']['id'];

// Fetch voting end time from the database
$stmt = $pdo->prepare("SELECT voting_end_time FROM settings LIMIT 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$votingEndTime = isset($settings['voting_end_time']) ? strtotime($settings['voting_end_time']) : null;

// Check if the voter has already voted
$stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
$stmt->execute([$voter_id]);
$userStatus = $stmt->fetch(PDO::FETCH_ASSOC);
$alreadyVoted = $userStatus && $userStatus['has_voted'];

// Fetch total votes for percentage calculation
$stmt = $pdo->prepare("SELECT SUM(votes) AS total_votes FROM candidates");
$stmt->execute();
$totalVotes = $stmt->fetch(PDO::FETCH_ASSOC)['total_votes'] ?? 0;

// Fetch all candidates
$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY votes DESC");
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if voting is disabled
$votingClosed = $votingEndTime && time() >= $votingEndTime;

// Handle vote submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['candidate_id'])) {
    if ($votingClosed) {
        $_SESSION['message'] = "⏳ Voting has ended. You can no longer vote.";
    } elseif ($alreadyVoted) {
        $_SESSION['message'] = "✅ You have already voted.";
    } else {
        $candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);

        if ($candidate_id) {
            // Record the vote
            $stmt = $pdo->prepare("INSERT INTO votes (voter_id, candidate_id) VALUES (?, ?)");
            $stmt->execute([$voter_id, $candidate_id]);

            // Update candidate's vote count
            $stmt = $pdo->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
            $stmt->execute([$candidate_id]);

            // Mark the voter as having voted
            $stmt = $pdo->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
            $stmt->execute([$voter_id]);

            $_SESSION['message'] = "🎉 Your vote has been recorded successfully!";
        } else {
            $_SESSION['message'] = "⚠️ Invalid candidate selection.";
        }
    }
    if ($votingClosed) {
      header("Location: ../winner/winner.php");
      exit();
  }  
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vote</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/Voter.css">
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
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDrawer" style="background-color: red; color: white;"> 
        Menu
      </button>
      <h1>Voter Dashboard</h1>
      <a href="../logout.php" class="btn btn-danger">Logout</a>
    </div>
    <div class="timer-container">
    <h4>Voting Timer</h4>
    <p>Time Left: <span id="voterTimerDisplay">--</span></p>
    </div>
<script>
    function updateVoterTimer() {
        let endTime = localStorage.getItem("votingEndTime");
        if (endTime) {
            let remaining = endTime - new Date().getTime();
            if (remaining > 0) {
                document.getElementById("voterTimerDisplay").innerText =
                    Math.floor(remaining / 60000) + "m " + Math.floor((remaining % 60000) / 1000) + "s";
                setTimeout(updateVoterTimer, 1000);
            } else {
                document.getElementById("voterTimerDisplay").innerText = "Voting Ended";
                document.getElementById("voteButton").disabled = true; // Disable voting button
            }
        } else {
            document.getElementById("voterTimerDisplay").innerText = "--";
        }
    }

    updateVoterTimer(); // Start timer on page load
</script>


    <!-- Include the Navigation Drawer -->
    <?php include '../admin/drawer.php'; ?>

    <div class="dashboard-header text-center">
      <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']); ?>!</h2>
      <p>Cast your vote below.</p>
    </div>
    
    <!-- Display Messages -->
    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <!-- Voting Section -->
    <div class="card p-4">
      <h4 class="mb-3">Vote for a Candidate</h4>
      
      <?php if (!$alreadyVoted && !$votingClosed): ?>
      <form method="post" action="vote.php">
          <div class="mb-3">
              <select name="candidate_id" class="form-control" required>
                  <option value="candidate_id">Select a Candidate</option>
                  <?php foreach($candidates as $candidate): ?>
                    <option value="<?= $candidate['id'] ?>">
                      <?= htmlspecialchars($candidate['name']) ?> (<?= $candidate['party'] ?>)
                    </option>
                  <?php endforeach; ?>
              </select>
          </div>
          <button type="submit" id="voteButton" class="btn btn-success" style="background-color: #0056b3;
    color: #fff;">Submit Vote</button>
      </form>
      <?php else: ?>
      <div class="alert alert-warning mt-3 text-center">
        <?= $votingClosed ? "⏳ Voting has ended. No more votes are allowed." : "✅ You have already voted." ?>
      </div>
      <?php endif; ?>
    </div>
    
    
    <!-- Real-time Voting Results -->
    <div class="card p-4 mt-4">
      <h3 class="mb-3">Real-time Voting Results</h3>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>Candidate</th>
              <th>Party</th>
              <th>Votes</th>
              <th>Percentage</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($candidates as $candidate): 
              $voteCount = $candidate['votes'];
              $percentage = ($totalVotes > 0) ? round(($voteCount / $totalVotes) * 100, 2) : 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($candidate['name']) ?></td>
              <td><?= htmlspecialchars($candidate['party']) ?></td>
              <td><?= $voteCount ?></td>
              <td><?= $percentage ?>%</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
  
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Disable vote button if voting is closed
    const votingClosed = <?= json_encode($votingClosed); ?>;
    if (votingClosed) {
        document.getElementById("voteButton")?.setAttribute("disabled", "disabled");
    }

    // Update countdown timer
    function updateTimer() {
        const endTime = <?= json_encode($votingEndTime); ?>;
        const now = Math.floor(Date.now() / 1000);
        const remainingTime = endTime - now;

        const timerDisplay = document.getElementById("timerDisplay");

        if (remainingTime <= 0) {
            timerDisplay.innerText = "⏳ Voting has ended.";
            document.getElementById("voteButton")?.setAttribute("disabled", "disabled");
        } else {
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            timerDisplay.innerText = `${minutes}m ${seconds}s`;
            setTimeout(updateTimer, 1000);
        }
    }
    
    if (!votingClosed) {
        updateTimer();
    }

    function checkVotingTime() {
    let endTime = localStorage.getItem("votingEndTime");
    if (endTime) {
        let remaining = endTime - new Date().getTime();
        if (remaining <= 0) {
            document.getElementById("timerDisplay").innerText = "Voting Ended";
            document.querySelector("form").style.display = "none"; // Hide voting form
            document.body.innerHTML += '<div class="alert alert-danger">Voting has ended.</div>';
        }
    }
}

setInterval(checkVotingTime, 1000);

  </script>
</body>
</html>
