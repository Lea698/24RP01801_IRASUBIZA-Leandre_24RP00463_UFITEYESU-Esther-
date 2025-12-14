<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLogin();

$user = getUser();
$error = '';
$success = '';
$selected_date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d');

// Get all students
try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY name ASC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
    $error = 'Error loading students: ' . $e->getMessage();
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $attendance_date = $_POST['attendance_date'];
    $attendance_data = isset($_POST['attendance']) ? $_POST['attendance'] : [];
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : [];
    
    // Count how many students have been marked
    $marked_count = 0;
    foreach ($attendance_data as $sid => $status) {
        if (!empty($status)) {
            $marked_count++;
        }
    }
    
    if ($marked_count === 0) {
        $error = 'Please mark attendance for at least one student.';
    } else {
        try {
            $pdo->beginTransaction();
            
            foreach ($attendance_data as $student_id => $status) {
                if (!empty($status)) {
                    // Check if attendance already exists
                    $check_stmt = $pdo->prepare("SELECT id FROM attendance_records WHERE student_id = :student_id AND date = :date");
                    $check_stmt->execute([
                        ':student_id' => $student_id,
                        ':date' => $attendance_date
                    ]);
                    
                    if ($check_stmt->rowCount() > 0) {
                        // Update existing record
                        $update_stmt = $pdo->prepare("UPDATE attendance_records 
                                                      SET status = :status, remarks = :remarks, marked_by = :marked_by 
                                                      WHERE student_id = :student_id AND date = :date");
                        $update_stmt->execute([
                            ':status' => $status,
                            ':remarks' => isset($remarks[$student_id]) ? $remarks[$student_id] : '',
                            ':marked_by' => $user['id'],
                            ':student_id' => $student_id,
                            ':date' => $attendance_date
                        ]);
                    } else {
                        // Insert new record
                        $insert_stmt = $pdo->prepare("INSERT INTO attendance_records (student_id, date, status, marked_by, remarks) 
                                                      VALUES (:student_id, :date, :status, :marked_by, :remarks)");
                        $insert_stmt->execute([
                            ':student_id' => $student_id,
                            ':date' => $attendance_date,
                            ':status' => $status,
                            ':marked_by' => $user['id'],
                            ':remarks' => isset($remarks[$student_id]) ? $remarks[$student_id] : ''
                        ]);
                    }
                }
            }
            
            $pdo->commit();
            $success = 'Attendance marked successfully for ' . $marked_count . ' student(s)!';
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error marking attendance: ' . $e->getMessage();
        }
    }
}

// Load existing attendance for selected date
$existing_attendance = [];
try {
    $stmt = $pdo->prepare("SELECT student_id, status, remarks FROM attendance_records WHERE date = :date");
    $stmt->execute([':date' => $selected_date]);
    
    while ($row = $stmt->fetch()) {
        $existing_attendance[$row['student_id']] = [
            'status' => $row['status'],
            'remarks' => $row['remarks']
        ];
    }
} catch (PDOException $e) {
    // Ignore error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Student Attendance System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-select {
            width: 150px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .remarks-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <h1>Student Attendance System</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="students.php">Students</a>
                <a href="attendance.php" class="active">Mark Attendance</a>
                <a href="view_attendance.php">View Attendance</a>
                <span class="user-info">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </nav>
        
        <div class="content">
            <h2>Mark Attendance</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Date Selection Form -->
            <form method="POST" action="attendance.php" class="form-card">
                <div class="form-group">
                    <label>Select Date: <span class="required">*</span></label>
                    <input type="date" name="attendance_date" 
                           value="<?php echo htmlspecialchars($selected_date); ?>" 
                           max="<?php echo date('Y-m-d'); ?>"
                           onchange="this.form.submit()">
                </div>
            </form>
            
            <?php if (empty($students)): ?>
                <div class="alert alert-info">No students found. Please add students first by clicking "Students" menu and then "Add New Student".</div>
            <?php else: ?>
                <!-- Attendance Marking Form -->
                <form method="POST" action="attendance.php">
                    <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($selected_date); ?>">
                    <input type="hidden" name="submit_attendance" value="1">
                    
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <h3 style="margin-bottom: 20px;">Mark Attendance for <?php echo date('F d, Y', strtotime($selected_date)); ?></h3>
                        
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $current_status = isset($existing_attendance[$student['id']]) ? $existing_attendance[$student['id']]['status'] : '';
                                    $current_remarks = isset($existing_attendance[$student['id']]) ? $existing_attendance[$student['id']]['remarks'] : '';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['course']); ?></td>
                                        <td>
                                            <select name="attendance[<?php echo $student['id']; ?>]" class="status-select">
                                                <option value="">-- Select --</option>
                                                <option value="present" <?php echo ($current_status === 'present') ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo ($current_status === 'absent') ? 'selected' : ''; ?>>Absent</option>
                                                <option value="late" <?php echo ($current_status === 'late') ? 'selected' : ''; ?>>Late</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="remarks[<?php echo $student['id']; ?>]" 
                                                   class="remarks-input"
                                                   value="<?php echo htmlspecialchars($current_remarks); ?>" 
                                                   placeholder="Optional remarks">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="form-actions" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-success" style="font-size: 16px; padding: 12px 30px;">
                                💾 Save Attendance
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>