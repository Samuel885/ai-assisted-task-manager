<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION["user_id"];

// --- Sorting + Filtering values ---
$sort = $_GET["sort"] ?? "due";
$priorityFilter = $_GET["priority"] ?? "All";
$statusFilter = $_GET["status"] ?? "All";

// Order by rules
$orderBy = "due_date ASC";
if ($sort === "priority") $orderBy = "FIELD(priority,'High','Medium','Low') ASC, due_date ASC";
if ($sort === "status") $orderBy = "FIELD(status,'Pending','In Progress','Completed') ASC, due_date ASC";

// Build dynamic SQL
$sql = "SELECT task_id, title, due_date, priority, status FROM tasks WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($priorityFilter !== "All") {
  $sql .= " AND priority = ?";
  $types .= "s";
  $params[] = $priorityFilter;
}

if ($statusFilter !== "All") {
  $sql .= " AND status = ?";
  $types .= "s";
  $params[] = $statusFilter;
}

$sql .= " ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

function priorityClass($p) {
  $p = strtolower($p);
  if ($p === "high") return "p-high";
  if ($p === "medium") return "p-medium";
  return "p-low";
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My Tasks - TaskBalance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="nav">
  <div class="brand">TaskBalance</div>
  <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
</div>

<div class="container">
  <div class="card">

    <div class="header-row">
      <div>
        <h1 style="margin:0;">My Tasks</h1>
        <p class="muted" style="margin:6px 0 0;">Manage your tasks, deadlines, and priorities.</p>
      </div>

      <div class="actions">
        <a class="btn btn-primary" href="add_task.php">+ Add Task</a>
      </div>
    </div>

    <!-- Sorting + Filtering Controls -->
    <form method="GET" style="margin-top:14px;">
      <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <select name="sort">
          <option value="due" <?php if($sort==="due") echo "selected"; ?>>Sort: Due Date</option>
          <option value="priority" <?php if($sort==="priority") echo "selected"; ?>>Sort: Priority</option>
          <option value="status" <?php if($sort==="status") echo "selected"; ?>>Sort: Status</option>
        </select>

        <select name="priority">
          <option value="All">Priority: All</option>
          <option value="High" <?php if($priorityFilter==="High") echo "selected"; ?>>High</option>
          <option value="Medium" <?php if($priorityFilter==="Medium") echo "selected"; ?>>Medium</option>
          <option value="Low" <?php if($priorityFilter==="Low") echo "selected"; ?>>Low</option>
        </select>

        <select name="status">
          <option value="All">Status: All</option>
          <option value="Pending" <?php if($statusFilter==="Pending") echo "selected"; ?>>Pending</option>
          <option value="In Progress" <?php if($statusFilter==="In Progress") echo "selected"; ?>>In Progress</option>
          <option value="Completed" <?php if($statusFilter==="Completed") echo "selected"; ?>>Completed</option>
        </select>

        <button class="btn btn-primary" type="submit">Apply</button>
        <a class="btn btn-outline" href="view_tasks.php">Reset</a>
      </div>
    </form>

    <div class="table-wrap">
      <table>
        <tr>
          <th style="width:35%;">Title</th>
          <th style="width:15%; text-align:center;">Due</th>
          <th style="width:15%; text-align:center;">Priority</th>
          <th style="width:15%; text-align:center;">Status</th>
          <th style="width:20%; text-align:center;">Actions</th>
        </tr>

        <?php if ($result->num_rows === 0): ?>
          <tr>
            <td colspan="5" class="muted" style="padding:18px;">
              No tasks match your filters. Try <strong>Reset</strong> or add a new task.
            </td>
          </tr>
        <?php else: ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td>
                <strong><?php echo htmlspecialchars($row["title"]); ?></strong>
              </td>

              <td style="text-align:center;">
                <?php echo htmlspecialchars($row["due_date"]); ?>
              </td>

              <td style="text-align:center;">
                <span class="pill <?php echo priorityClass($row["priority"]); ?>">
                  <?php echo htmlspecialchars($row["priority"]); ?>
                </span>
              </td>

              <td style="text-align:center;">
                <span class="status status-<?php echo str_replace(' ', '\\ ', $row["status"]); ?>">
                  <?php echo htmlspecialchars($row["status"]); ?>
                </span>
              </td>

              <td style="text-align:center;">
                <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap;">
                  <a class="action-btn action-edit"
                     href="edit_task.php?id=<?php echo (int)$row["task_id"]; ?>">
                    Edit
                  </a>

                  <a class="action-btn action-del"
                     href="delete_task.php?id=<?php echo (int)$row["task_id"]; ?>"
                     onclick="return confirm('Delete this task?');">
                    Delete
                  </a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>

      </table>
    </div>

  </div>
</div>

</body>
</html>
<?php $stmt->close(); ?>