<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLogin();

$user = getUser();
$error = '';
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_student = $_GET['student'] ?? '';

// Get all students for filter
try {
    $students_list = $pdo->query("SELECT id, student_id, name FROM students ORDER BY name ASC")->fetchAll();
} catch (PDOException $e) {
    $students_list = [];
}

// Build query based on filters
$query = "SELECT ar.*, s.student_id, s.name, s.course, u.username as marked_by_name 
          FROM attendance_records ar 
          JOIN students s ON ar.student_id = s.id 
          JOIN users u ON ar.marked_by = u.id 
          WHERE 1=1";

$params = [];

if (!empty($filter_date)) {
    $query .= " AND ar.date = :date";
    $params[':date'] = $filter_date;
}

if (!empty($filter_student)) {
    $query .= " AND ar.student_id = :student_id";
    $params[':student_id'] = $filter_student;
}

$query .= " ORDER BY ar.date DESC, s.name ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $attendance_records = $stmt->fetchAll();
} catch (PDOException $e) {
    $attendance_records = [];
    $error = 'Error loading attendance records.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - Student Attendance System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <h1>Student Attendance System</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="students.php">Students</a>
                <a href="attendance.php">Mark Attendance</a>
                <a href="view_attendance.php" class="active">View Attendance</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
        
        <div class="content">
            <h2>View Attendance Records</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="filter-card">
                <h3>Filters</h3>
                <form method="GET" action="">
                    <div class="filter-row">
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Student:</label>
                            <select name="student">
                                <option value="">All Students</option>
                                <?php foreach ($students_list as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" 
                                        <?php echo ($filter_student == $s['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['student_id'] . ' - ' . $s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="view_attendance.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance_records)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No attendance records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                <td><?php echo htmlspecialchars($record['course']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $record['status']; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record['remarks'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($record['marked_by_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>