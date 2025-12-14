<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLogin();

$user = getUser();

// Get statistics
try {
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    
    $today = date('Y-m-d');
    $today_attendance = $pdo->prepare("SELECT COUNT(*) FROM attendance_records WHERE date = :date");
    $today_attendance->execute([':date' => $today]);
    $attendance_today = $today_attendance->fetchColumn();
    
    $present_today = $pdo->prepare("SELECT COUNT(*) FROM attendance_records WHERE date = :date AND status = 'present'");
    $present_today->execute([':date' => $today]);
    $present_count = $present_today->fetchColumn();
    
} catch (PDOException $e) {
    $total_students = 0;
    $attendance_today = 0;
    $present_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Attendance System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <h1>Student Attendance System</h1>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="students.php">Students</a>
                <a href="attendance.php">Mark Attendance</a>
                <a href="view_attendance.php">View Attendance</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
        
        <div class="content">
            <h2>Dashboard</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Attendance Today</h3>
                    <div class="stat-number"><?php echo $attendance_today; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Present Today</h3>
                    <div class="stat-number"><?php echo $present_count; ?></div>
                </div>
            </div>
            
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <a href="students.php" class="btn btn-primary">Manage Students</a>
                <a href="attendance.php" class="btn btn-success">Mark Attendance</a>
                <a href="view_attendance.php" class="btn btn-info">View Records</a>
            </div>
        </div>
    </div>
</body>
</html>
