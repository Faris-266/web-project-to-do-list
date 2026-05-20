<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'taskifydb';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection Error: " . $e->getMessage());
}

// Determine type: 'task' or 'note'
$type = $_GET['type'] ?? 'task';



// Check user login
$userId = $_SESSION['user_id'] ?? 0;
if ($userId == 0) {
    die(json_encode([])); // Return empty array if not logged in
}

if ($type === 'task') {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE type = 'task' AND user_id = :uid ORDER BY id DESC");
    $stmt->execute(['uid' => $userId]);
} else if ($type === 'note') {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = :uid ORDER BY id DESC");
    $stmt->execute(['uid' => $userId]);
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert numeric values to booleans for JS
foreach ($rows as &$row) {
    if (isset($row['isdone'])) $row['isDone'] = (bool)$row['isdone'];
    // Map 'star' column to 'isImportant' prop
    if (isset($row['star'])) $row['isImportant'] = (bool)$row['star'];
    elseif (isset($row['isimportant'])) $row['isImportant'] = (bool)$row['isimportant']; // Fallback
}
unset($row);

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
echo json_encode($rows);
