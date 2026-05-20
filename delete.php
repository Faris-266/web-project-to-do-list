<?php
session_start();

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'taskifydb';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Connection Error: ' . $e->getMessage()]));
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $type = $_POST['type'] ?? 'task'; // 'task' or 'note'

    if ($id <= 0 && $type !== 'clear_finished') {
        echo json_encode(['status' => 'error', 'message' => 'Valid ID is required']);
        exit;
    }

    try {
        if ($type === 'task') {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
        } else if ($type === 'note') {
            $stmt = $pdo->prepare("DELETE FROM notes WHERE id = :id");
        } else if ($type === 'clear_finished') {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE isdone = 1");
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'All finished tasks deleted']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid type']);
            exit;
        }

        $stmt->execute(['id' => $id]);
        echo json_encode(['status' => 'success', 'message' => ucfirst($type) . ' deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
