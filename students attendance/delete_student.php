<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLogin();

$student_id = $_GET['id'] ?? 0;

if ($student_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $stmt->bindParam(':id', $student_id, PDO::PARAM_INT);
        $stmt->execute();
        
        header('Location: students.php?success=deleted');
        exit();
    } catch (PDOException $e) {
        header('Location: students.php?error=delete_failed');
        exit();
    }
} else {
    header('Location: students.php');
    exit();
}
?>
