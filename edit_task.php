<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION["user_id"];
$task_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$error = "";

/* 1) Fetch the task (must belong to this user) */
$stmt = $conn->prepare("SELECT * FROM tasks WHERE task_id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

if (!$task) {
  header("Location: view_tasks.php");
  exit;
}

/* 2) If form submitted, update the task */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = trim($_POST["title"] ?? "");
  $description = trim($_POST["description"] ?? "");
  $due_date = $_POST["due_date"] ?? null;
  $priority = $_POST["priority"] ?? "Medium";
  $difficulty = $_POST["difficulty"] ?? "Medium";
  $estimated_hours = (int)($_POST["estimated_hours"] ?? 1);
  $status = $_POST["status"] ?? "Pending";

  if ($title === "" || !$due_date) {
    $error = "Title and due date are required.";
  } else {
    $priority_weight = ($priority === "High") ? 3 : (($priority === "Medium") ? 2 : 1);
    $workload_score = max(0, $estimated_hours) * $priority_weight;

    $stmt2 = $conn->prepare("
      UPDATE tasks
      SET title = ?, description = ?, due_date = ?, priority = ?, difficulty = ?, estimated_hours = ?, status = ?, workload_score = ?
      WHERE task_id = ? AND user_id = ?
    ");
    $stmt2->bind_param(
      "sssssisiii",
      $title,
      $description,
      $due_date,
      $priority,
      $difficulty,
      $estimated_hours,
      $status,
      $workload_score,
      $task_id,
      $user_id
    );

    if ($stmt2->execute()) {
      header("Location: view_tasks.php");
      exit;
    } else {
      $error = "Update failed. Please try again.";
    }
    $stmt2->close();
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Task - TaskBalance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="nav">
    <div class="brand">TaskBalance</div>
    <a class="btn btn-outline" href="view_tasks.php">Back</a>
  </div>

  <div class="container">
    <div class="card" style="max-width:720px; margin: 0 auto;">
      <h1>Edit Task</h1>
      <p class="muted">Update task details and save changes.</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <label>Task Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($task["title"]); ?>" required>

        <label>Description</label>
        <input type="text" name="description" value="<?php echo htmlspecialchars($task["description"] ?? ""); ?>">

        <label>Due Date</label>
        <input type="date" name="due_date" value="<?php echo htmlspecialchars($task["due_date"]); ?>" required>

        <label>Priority</label>
        <select name="priority" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px;">
          <option value="Low" <?php if ($task["priority"] === "Low") echo "selected"; ?>>Low</option>
          <option value="Medium" <?php if ($task["priority"] === "Medium") echo "selected"; ?>>Medium</option>
          <option value="High" <?php if ($task["priority"] === "High") echo "selected"; ?>>High</option>
        </select>

        <label>Difficulty</label>
        <select name="difficulty" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px;">
          <option value="Easy" <?php if ($task["difficulty"] === "Easy") echo "selected"; ?>>Easy</option>
          <option value="Medium" <?php if ($task["difficulty"] === "Medium") echo "selected"; ?>>Medium</option>
          <option value="Hard" <?php if ($task["difficulty"] === "Hard") echo "selected"; ?>>Hard</option>
        </select>

        <label>Estimated Hours</label>
        <input type="number" name="estimated_hours" min="1" value="<?php echo (int)$task["estimated_hours"]; ?>" required>

        <label>Status</label>
        <select name="status" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px;">
          <option value="Pending" <?php if ($task["status"] === "Pending") echo "selected"; ?>>Pending</option>
          <option value="In Progress" <?php if ($task["status"] === "In Progress") echo "selected"; ?>>In Progress</option>
          <option value="Completed" <?php if ($task["status"] === "Completed") echo "selected"; ?>>Completed</option>
        </select>

        <div class="actions" style="margin-top:16px;">
          <button class="btn btn-primary" type="submit">Save Changes</button>
          <a class="btn btn-outline" href="view_tasks.php">Cancel</a>
        </div>
      </form>
    </div>
  </div>

</body>
</html>