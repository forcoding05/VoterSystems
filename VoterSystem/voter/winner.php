<?php
session_start();
require_once '../db.php';

// Fetch voting end time from the database
$stmt = $pdo->prepare("SELECT voting_end_time FROM settings LIMIT 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$votingEndTime = isset($settings['voting_end_time']) ? strtotime($settings['voting_end_time']) : null;

// Prevent access if voting end time is not set


// Check if voting is still ongoing
if (time() < $votingEndTime) {
    // Redirect back if voting is still ongoing
    header("Location: ../voter/vote.php");
    exit();
}

// Fetch the candidate with the highest votes
$stmt = $pdo->prepare("SELECT name, party, votes FROM candidates ORDER BY votes DESC LIMIT 1");
$stmt->execute();
$winner = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch total votes to calculate percentage
$stmt = $pdo->prepare("SELECT SUM(votes) AS total_votes FROM candidates");
$stmt->execute();
$totalVotes = $stmt->fetch(PDO::FETCH_ASSOC)['total_votes'] ?? 0;

// Calculate winner's percentage
$winnerPercentage = ($totalVotes > 0 && $winner) ? round(($winner['votes'] / $totalVotes) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Winner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Election Winner</h2>

        <?php if ($winner && $winner['votes'] > 0): ?>
            <div class="card mt-4 text-center">
                <div class="card-body">
                    <h3 class="card-title">🏆 Winner: <?= htmlspecialchars($winner['name']); ?></h3>
                    <p class="card-text"><strong>Party:</strong> <?= htmlspecialchars($winner['party']); ?></p>
                    <p class="card-text"><strong>Votes:</strong> <?= $winner['votes']; ?></p>
                    <p class="card-text"><strong>Winning Percentage:</strong> <?= $winnerPercentage; ?>%</p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-4 text-center">No winner yet. No votes were cast.</div>
        <?php endif; ?>
            
        <div class="text-center mt-4">
            <a href="../voter/vote.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
