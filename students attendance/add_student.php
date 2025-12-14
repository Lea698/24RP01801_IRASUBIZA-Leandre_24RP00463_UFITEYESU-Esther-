<?php
require_once 'config/database.php';
require_once 'includes/session.php';
requireLogin();

$user = getUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year_level = $_POST['year_level'] ?? '';
    
    // Validation
    if (empty($student_id) || empty($name) || empty($email)) {
        $error = 'Student ID, Name, and Email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!is_numeric($year_level) || $year_level < 1 || $year_level > 5) {
        $error = 'Year level must be between 1 and 5.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (student_id, name, email, course, year_level) 
                                   VALUES (:student_id, :name, :email, :course, :year_level)");
            
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':course', $course, PDO::PARAM_STR);
            $stmt->bindParam(':year_level', $year_level, PDO::PARAM_INT);
            
            $stmt->execute();
            
            header('Location: students.php?success=added');
            exit();
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Student ID or Email already exists.';
            } else {
                $error = 'Error adding student. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Student Attendance System</title>
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
            <h2>Add New Student</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="form-card">
                <div class="form-group">
                    <label>Student ID: <span class="required">*</span></label>
                    <input type="text" name="student_id" required 
                           value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Name: <span class="required">*</span></label>
                    <input type="text" name="name" required 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Email: <span class="required">*</span></label>
                    <input type="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Course:</label>
                    <input type="text" name="course" 
                           value="<?php echo htmlspecialchars($_POST['course'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Year Level: <span class="required">*</span></label>
                    <select name="year_level" required>
                        <option value="">Select Year</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" 
                                <?php echo (isset($_POST['year_level']) && $_POST['year_level'] == $i) ? 'selected' : ''; ?>>
                                Year <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Student</button>
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
