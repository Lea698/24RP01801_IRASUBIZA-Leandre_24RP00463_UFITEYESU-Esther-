<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLogin();

$user = getUser();
$success = $_GET['success'] ?? '';

try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY name ASC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
    $error = 'Error loading students: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Student Attendance System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <h1>Student Attendance System</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="students.php" class="active">Students</a>
                <a href="attendance.php">Mark Attendance</a>
                <a href="view_attendance.php">View Attendance</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
        
        <div class="content">
            <div class="page-header">
                <h2>Students</h2>
                <a href="add_student.php" class="btn btn-primary">Add New Student</a>
            </div>
            
            <?php if ($success === 'added'): ?>
                <div class="alert alert-success">Student added successfully!</div>
            <?php elseif ($success === 'updated'): ?>
                <div class="alert alert-success">Student updated successfully!</div>
            <?php elseif ($success === 'deleted'): ?>
                <div class="alert alert-success">Student deleted successfully!</div>
            <?php elseif ($success === 'marked_present'): ?>
                <div class="alert alert-success">Attendance marked as PRESENT for today!</div>
            <?php elseif ($success === 'marked_absent'): ?>
                <div class="alert alert-success">Attendance marked as ABSENT for today!</div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No students found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                <td>
                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete_student.php?id=<?php echo $student['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                    <a href="quick_mark.php?id=<?php echo $student['id']; ?>&status=present" 
                                       class="btn btn-sm btn-success" 
                                       onclick="return confirm('Mark as PRESENT for today?')">Present</a>
                                    <a href="quick_mark.php?id=<?php echo $student['id']; ?>&status=absent" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Mark as ABSENT for today?')">Absent</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>