<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

if (isset($_GET['id'])) {
    $taskId = $_GET['id'];
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("UPDATE tasks SET is_completed = 1, date_completed = NOW() WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $taskId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header('Location: index.php');
    exit;
}
?>