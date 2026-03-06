<?php
require "config.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION["user_id"];

function priorityWeight($p) {
  $p = strtolower(trim($p));
  if ($p === "high") return 5;
  if ($p === "medium") return 3;
  return 1;
}

function difficultyWeight($d) {
  $d = strtolower(trim($d));
  if ($d === "hard") return 4;
  if ($d === "medium") return 2;
  return 1;
}

function statusWeight($status) {
  $status = strtolower(trim($status));
  if ($status === "in progress") return 2;
  return 0;
}

function daysUntil($dateStr) {
  $today = new DateTime("today");
  $due = DateTime::createFromFormat("Y-m-d", $dateStr);
  if (!$due) return 9999;
  return (int)$today->diff($due)->format("%r%a");
}

function urgencyScore($days) {
  if ($days < 0) {
    return min(25, 20 + abs($days));
  } elseif ($days === 0) {
    return 18;
  } elseif ($days === 1) {
    return 15;
  } elseif ($days <= 3) {
    return 12;
  } elseif ($days <= 7) {
    return 8;
  } elseif ($days <= 14) {
    return 4;
  } else {
    return 1;
  }
}

function recommendationCategory($days) {
  if ($days < 0 || $days <= 1) return "Do Now";
  if ($days <= 7) return "Do Soon";
  return "Plan Ahead";
}

function dueLabel($days) {
  if ($days < 0) return "Overdue by " . abs($days) . " day(s)";
  if ($days === 0) return "Due today";
  if ($days === 1) return "Due tomorrow";
  return "Due in " . $days . " day(s)";
}

function recommendationReason($task) {
  $reasons = [];

  if ($task["days_until"] < 0) {
    $reasons[] = "it is overdue";
  } elseif ($task["days_until"] === 0) {
    $reasons[] = "it is due today";
  } elseif ($task["days_until"] === 1) {
    $reasons[] = "it is due tomorrow";
  } elseif ($task["days_until"] <= 3) {
    $reasons[] = "it is due very soon";
  }

  if (strtolower($task["priority"]) === "high") {
    $reasons[] = "it has high priority";
  }

  if (strtolower($task["difficulty"]) === "hard") {
    $reasons[] = "it is difficult";
  }

  if ((int)$task["estimated_hours"] >= 4) {
    $reasons[] = "it needs more time";
  }

  if (strtolower($task["status"]) === "in progress") {
    $reasons[] = "you already started it";
  }

  if (empty($reasons)) {
    return "it has a balanced mix of urgency and workload";
  }

  return implode(", ", $reasons);
}

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

  $priority = priorityWeight($row["priority"]);
  $difficulty = difficultyWeight($row["difficulty"]);
  $statusBonus = statusWeight($row["status"]);
  $urgency = urgencyScore($days);
  $hours = (int)($row["estimated_hours"] ?? 1);

  $score =
      ($urgency * 3) +
      ($priority * 2) +
      ($difficulty * 1.5) +
      min(6, $hours) +
      $statusBonus;

  $row["days_until"] = $days;
  $row["score"] = round($score, 1);
  $row["reason"] = recommendationReason($row);
  $row["category"] = recommendationCategory($days);
  $row["due_label"] = dueLabel($days);

  $tasks[] = $row;
}

usort($tasks, function($a, $b) {
  if ($b["score"] == $a["score"]) {
    if ($a["days_until"] == $b["days_until"]) {
      return $b["estimated_hours"] <=> $a["estimated_hours"];
    }
    return $a["days_until"] <=> $b["days_until"];
  }
  return $b["score"] <=> $a["score"];
});

$stmt->close();
?>