<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$error = "";

$title = "";
$description = "";
$due_date = "";
$priority = "Medium";
$difficulty = "Medium";
$estimated_hours = 1;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user_id = $_SESSION["user_id"];

  $title = trim($_POST["title"] ?? "");
  $description = trim($_POST["description"] ?? "");
  $due_date = $_POST["due_date"] ?? "";
  $priority = $_POST["priority"] ?? "Medium";
  $difficulty = $_POST["difficulty"] ?? "Medium";
  $estimated_hours = (int)($_POST["estimated_hours"] ?? 1);
  $status = "Pending";

  if ($title === "" || $due_date === "") {
    $error = "Title and due date are required.";
  } elseif ($estimated_hours < 1) {
    $error = "Estimated hours must be at least 1.";
  } elseif ($due_date < date("Y-m-d")) {
    $error = "Due date cannot be in the past.";
  } else {
    $priority_weight = ($priority === "High") ? 3 : (($priority === "Medium") ? 2 : 1);
    $difficulty_weight = ($difficulty === "Hard") ? 3 : (($difficulty === "Medium") ? 2 : 1);

    $workload_score = (max(1, $estimated_hours) * $priority_weight) + $difficulty_weight;

    $stmt = $conn->prepare("
      INSERT INTO tasks (user_id, title, description, due_date, priority, difficulty, estimated_hours, status, workload_score)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
      die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
      "isssssisi",
      $user_id,
      $title,
      $description,
      $due_date,
      $priority,
      $difficulty,
      $estimated_hours,
      $status,
      $workload_score
    );

    if (!$stmt->execute()) {
      die("Insert failed: " . $stmt->error);
    }

    $stmt->close();
    header("Location: view_tasks.php");
    exit;
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Task - TaskBalance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="nav">
  <div class="brand">TaskBalance</div>
  <a class="btn btn-outline" href="view_tasks.php">Back</a>
</div>

<div class="container">
  <div class="card form-card">
    <h1>Add Task</h1>
    <p class="muted">Create a task so TaskBalance can recommend what to do first.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Task Title</label>
      <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

      <label>Description</label>
      <textarea name="description"><?php echo htmlspecialchars($description); ?></textarea>

      <label>Due Date</label>
      <input type="date" name="due_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($due_date); ?>" required>

      <label>Priority</label>
      <select name="priority">
        <option value="Low" <?php if ($priority === "Low") echo "selected"; ?>>Low</option>
        <option value="Medium" <?php if ($priority === "Medium") echo "selected"; ?>>Medium</option>
        <option value="High" <?php if ($priority === "High") echo "selected"; ?>>High</option>
      </select>

      <label>Difficulty</label>
      <select name="difficulty">
        <option value="Easy" <?php if ($difficulty === "Easy") echo "selected"; ?>>Easy</option>
        <option value="Medium" <?php if ($difficulty === "Medium") echo "selected"; ?>>Medium</option>
        <option value="Hard" <?php if ($difficulty === "Hard") echo "selected"; ?>>Hard</option>
      </select>

      <label>Estimated Hours</label>
      <input type="number" name="estimated_hours" min="1" value="<?php echo (int)$estimated_hours; ?>" required>

      <div class="actions">
        <button class="btn btn-primary" type="submit">Save Task</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>