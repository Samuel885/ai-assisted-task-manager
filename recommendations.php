<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION["user_id"];

function priorityWeight($p) {
  $p = strtolower($p);
  if ($p === "high") return 3;
  if ($p === "medium") return 2;
  return 1;
}

function difficultyWeight($d) {
  $d = strtolower($d);
  if ($d === "hard") return 3;
  if ($d === "medium") return 2;
  return 1;
}

function daysUntil($dateStr) {
  $today = new DateTime("today");
  $due = DateTime::createFromFormat("Y-m-d", $dateStr);
  if (!$due) return 9999;
  $diff = (int)$today->diff($due)->format("%r%a"); // negative if overdue
  return $diff;
}

// Fetch tasks not completed
$stmt = $conn->prepare("
  SELECT task_id, title, due_date, priority, difficulty, estimated_hours, status
  FROM tasks
  WHERE user_id = ? AND status != 'Completed'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$tasks = [];

while ($row = $res->fetch_assoc()) {
  $days = daysUntil($row["due_date"]);

  // urgency factor: overdue or due soon = higher
  // due today => 10, due in 1 day => 9, ... due in 7 days => 3, > 7 => 1
  if ($days <= 0) $urgency = 12; 
  elseif ($days == 1) $urgency = 10;
  elseif ($days <= 3) $urgency = 8;
  elseif ($days <= 7) $urgency = 5;
  else $urgency = 2;

  $pW = priorityWeight($row["priority"]);
  $dW = difficultyWeight($row["difficulty"]);

  // score formula (simple but effective)
  $score = ($pW * 4) + ($dW * 2) + $urgency;

  // small bonus if estimated hours is bigger (optional)
  $hours = (int)($row["estimated_hours"] ?? 1);
  $score += min(3, max(0, $hours - 1));

  $row["days_until"] = $days;
  $row["score"] = $score;
  $tasks[] = $row;
}

// Sort highest score first
usort($tasks, function($a, $b) {
  return $b["score"] <=> $a["score"];
});

$stmt->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Recommendations - TaskBalance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="nav">
  <div class="brand">TaskBalance</div>
  <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
</div>

<div class="container">
  <div class="card">
    <h1 style="margin:0;">Recommendations</h1>
    <p class="muted" style="margin:6px 0 0;">
      Suggested order based on priority, difficulty, and due date.
    </p>

    <?php if (count($tasks) === 0): ?>
      <div style="margin-top:14px;" class="muted">
        No active tasks found. Add tasks to get recommendations.
      </div>
    <?php else: ?>
      <?php $top = $tasks[0]; ?>
      <div class="card" style="box-shadow:none; margin-top:14px;">
        <p class="muted" style="margin:0 0 8px;">Do this first</p>
        <h2 style="margin:0;"><?php echo htmlspecialchars($top["title"]); ?></h2>
        <p class="muted" style="margin:8px 0 0;">
          Due: <strong><?php echo htmlspecialchars($top["due_date"]); ?></strong> |
          Priority: <strong><?php echo htmlspecialchars($top["priority"]); ?></strong> |
          Difficulty: <strong><?php echo htmlspecialchars($top["difficulty"]); ?></strong>
        </p>
      </div>

      <div class="table-wrap" style="margin-top:14px;">
        <table>
          <tr>
            <th style="width:40%;">Task</th>
            <th style="text-align:center;">Due</th>
            <th style="text-align:center;">Priority</th>
            <th style="text-align:center;">Difficulty</th>
            <th style="text-align:center;">Score</th>
          </tr>

          <?php foreach ($tasks as $t): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($t["title"]); ?></strong></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($t["due_date"]); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($t["priority"]); ?></td>
              <td style="text-align:center;"><?php echo htmlspecialchars($t["difficulty"]); ?></td>
              <td style="text-align:center;"><?php echo (int)$t["score"]; ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>