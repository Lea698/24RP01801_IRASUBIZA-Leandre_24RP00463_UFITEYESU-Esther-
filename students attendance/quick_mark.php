<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLogin();

$user = getUser();
$student_id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

// Validate status
if (!in_array($status, ['present', 'absent'])) {
    header('Location: students.php?error=invalid_status');
    exit();
}

// Validate student exists
try {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE id = :id");
    $stmt->bindParam(':id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        header('Location: students.php?error=student_not_found');
        exit();
    }
} catch (PDOException $e) {
    header('Location: students.php?error=database_error');
    exit();
}

// Mark attendance for today
$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare("INSERT INTO attendance_records (student_id, date, status, marked_by, remarks) 
                           VALUES (:student_id, :date, :status, :marked_by, :remarks)
                           ON DUPLICATE KEY UPDATE status = :status, marked_by = :marked_by");
    
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $today, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindValue(':marked_by', $user['id'], PDO::PARAM_INT);
    $stmt->bindValue(':remarks', 'Quick marked from students list', PDO::PARAM_STR);
    
    $stmt->execute();
    
    header('Location: students.php?success=marked_' . $status);
    exit();
    
} catch (PDOException $e) {
    header('Location: students.php?error=mark_failed');
    exit();
}
?>