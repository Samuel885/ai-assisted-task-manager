<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$full_name = $_SESSION["full_name"];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - TaskBalance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="nav">
    <div class="brand">TaskBalance</div>
    <a class="btn btn-outline" href="logout.php">Logout</a>
  </div>

  <div class="container">

    <div class="card">
      <h1>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>
      <p class="muted">You are logged in. This is your dashboard.</p>

      <div class="row">
        <div class="card" style="box-shadow:none;">
          <p class="muted" style="margin-bottom:8px;">Workload Level</p>
          <span class="badge">MODERATE</span>
          <p style="margin-top:10px; margin-bottom:0;">
            Suggested Focus: <strong>Finish Assignment</strong>
          </p>
          <p class="muted" style="margin-top:6px;">Due: Tomorrow</p>
        </div>

        <div class="card" style="box-shadow:none;">
          <p class="muted" style="margin-bottom:8px;">Quick Actions</p>
          <div class="actions">
            <a class="btn btn-primary" href="#">+ Add Task</a>
            <a class="btn btn-outline" href="#">Tasks Due</a>
            <a class="btn btn-outline" href="#">Workload Analysis</a>
          </div>
          <p class="muted" style="margin-top:12px;">
            (Buttons can be linked later when CRUD pages are ready.)
          </p>
        </div>
      </div>

    </div>

  </div>
</body>
</html>
