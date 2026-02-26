<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$full_name = $_SESSION["full_name"];
$user_id = $_SESSION["user_id"];

// Get top recommended task (highest workload_score, not completed)
$stmt = $conn->prepare("
  SELECT title, due_date, priority, difficulty, workload_score
  FROM tasks
  WHERE user_id = ? AND status != 'Completed'
  ORDER BY workload_score DESC, due_date ASC
  LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$topTask = $stmt->get_result()->fetch_assoc();
$stmt->close();
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
          <p class="muted" style="margin-bottom:8px;">Workload Recommendation</p>

          <?php if ($topTask): ?>
            <span class="badge">
              <?php echo htmlspecialchars(strtoupper($topTask["priority"])); ?> PRIORITY
            </span>

            <p style="margin-top:10px; margin-bottom:0;">
              Suggested Focus:
              <strong><?php echo htmlspecialchars($topTask["title"]); ?></strong>
            </p>

            <p class="muted" style="margin-top:6px;">
              Due: <?php echo htmlspecialchars($topTask["due_date"]); ?> |
              Difficulty: <?php echo htmlspecialchars($topTask["difficulty"]); ?> |
              Score: <?php echo (int)$topTask["workload_score"]; ?>
            </p>
          <?php else: ?>
            <span class="badge">NO PENDING TASKS</span>
            <p style="margin-top:10px; margin-bottom:0;">
              You have no pending tasks. Add one to get recommendations.
            </p>
          <?php endif; ?>
        </div>

        <div class="card" style="box-shadow:none;">
          <p class="muted" style="margin-bottom:8px;">Quick Actions</p>
          <div class="actions">
            <a class="btn btn-primary" href="add_task.php">+ Add Task</a>
            <a class="btn btn-outline" href="view_tasks.php">View Tasks</a>
            <a class="btn btn-outline" href="recommendations.php">Recommendations</a>
          </div>
          <p class="muted" style="margin-top:12px;">
            Workload analysis can be added after CRUD is complete.
          </p>
        </div>

      </div>
    </div>
  </div>

</body>
</html>