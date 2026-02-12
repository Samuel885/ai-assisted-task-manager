<?php
require "config.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $full_name = trim($_POST["full_name"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $password = $_POST["password"] ?? "";
  $confirm = $_POST["confirm"] ?? "";

  if ($full_name === "" || $email === "" || $password === "") {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Please enter a valid email.";
  } elseif ($password !== $confirm) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters.";
  } else {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $error = "Email is already registered.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt2 = $conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?,?,?)");
      $stmt2->bind_param("sss", $full_name, $email, $hash);

      if ($stmt2->execute()) {
        $success = "Registration successful. You can now log in.";
      } else {
        $error = "Registration failed. Please try again.";
      }
      $stmt2->close();
    }
    $stmt->close();
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register - TaskBalance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="nav">
    <div class="brand">TaskBalance</div>
    <a class="btn btn-outline" href="login.php">Login</a>
  </div>

  <div class="auth-wrap">
    <div class="auth-card">
      <h2>Create account</h2>
      <p class="sub">Register to start managing your tasks.</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Confirm Password</label>
        <input type="password" name="confirm" required>

        <div class="auth-actions">
          <button class="btn btn-primary" type="submit">Register</button>
          <a class="small-link" href="login.php">Already have an account?</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
