<?php
// login.php
session_start();
require_once 'db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verified = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user && password_verify($password, $user['password'])){
        $_SESSION['user'] = $user;
        if($user['role'] == 'admin'){
            header("Location: admin/dashboard.php");
        } else {
            header("Location: voter/vote.php");
        }
        exit();
    } else {
        $error = "Invalid credentials or account not verified.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Boxicons CSS for icons -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/login.css">
  <style>
    /* Custom styles for input with right-side icon */
    .input-group .form-control {
      border-right: 0; /* Remove right border from input */
    }
    .input-group .input-group-text {
      border-left: 0; /* Remove left border from appended icon */
      background: transparent; /* Transparent background */
      padding: 0.5rem 0.75rem;
    }
  </style>
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
  <div class="login-container">
    <h2>Login</h2>
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" action="login.php" class="login-form">
      <!-- Email Input with Icon on the Right -->
      <div class="mb-3">
        <div class="input-group">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
          <span class="input-group-text"><i class='bx bx-user'></i></span>
        </div>
      </div>
      <!-- Password Input with Icon on the Right -->
      <div class="mb-3">
        <div class="input-group">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
      <p class="mt-2 text-center">Don't have an account? <a href="register.php">Register here</a></p>
    </form>
  </div>
  
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
