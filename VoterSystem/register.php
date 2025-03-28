<?php
// register.php
session_start();
require_once 'db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];  // "voter" or "admin"

    if(strlen($password) < 6){
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->rowCount() > 0){
            $error = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $verification_code = md5(uniqid());
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, verification_code) VALUES (?, ?, ?, ?, ?)");
            if($stmt->execute([$name, $email, $hashedPassword, $role, $verification_code])){
                // For demonstration, automatically mark as verified.
                $update = $pdo->prepare("UPDATE users SET verified = 1 WHERE email = ?");
                $update->execute([$email]);
                $_SESSION['message'] = "Registration successful! You can now login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed, try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Boxicons CSS for icons -->
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/register.css">
  <style>
    .input-group .form-control {
        border-right: none;
    }
    .input-group .input-group-text {
        background: transparent;
    }
  </style>
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
    <div class="register-container">
        <h2 class="text-center mb-4">Register</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" action="register.php" class="register-form">
            <!-- Full Name Input with Icon on the Right -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    <span class="input-group-text"><i class='bx bx-user'></i></span>
                </div>
            </div>
            <!-- Email Input with Icon on the Right -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <span class="input-group-text"><i class='bx bx-envelope'></i></span>
                </div>
            </div>
            <!-- Password Input with Icon on the Right -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="password" name="password" class="form-control" placeholder="Password (6 characters required)" required>
                    <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                </div>
            </div>
            <!-- Role Selection -->
            <div class="mb-3">
                <select name="role" class="form-control">
                    <option value="voter">Voter</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
            <p class="mt-2 text-center">Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>
