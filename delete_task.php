<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=No task specified');
    exit;
}

require_once 'db.php';

try {
    $taskId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $taskId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    header('Location: index.php?success=Task deleted');
    exit;
} catch (PDOException $e) {

    header('Location: index.php?error=' . urlencode("Database error: " . $e->getMessage()));
    exit;
}
?>