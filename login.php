<?php
require "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $password = $_POST["password"] ?? "";

  if ($email === "" || $password === "") {
    $error = "Email and password are required.";
  } else {
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password_hash"])) {
      $_SESSION["user_id"] = $user["user_id"];
      $_SESSION["full_name"] = $user["full_name"];
      header("Location: dashboard.php");
      exit;
    } else {
      $error = "Invalid email or password.";
    }
    $stmt->close();
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - TaskBalance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="nav">
    <div class="brand">TaskBalance</div>
    <a class="btn btn-outline" href="register.php">Register</a>
  </div>

  <div class="auth-wrap">
    <div class="auth-card">
      <h2>Welcome back</h2>
      <p class="sub">Log in to manage your tasks and workload.</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <div class="auth-actions">
          <button class="btn btn-primary" type="submit">Login</button>
          <a class="small-link" href="register.php">Create an account</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
