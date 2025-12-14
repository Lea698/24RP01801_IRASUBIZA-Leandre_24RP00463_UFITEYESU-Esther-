<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        try {
            // Check if username or email already exists
            $check = $pdo->prepare(
                "SELECT id FROM users WHERE username = :username OR email = :email"
            );
            $check->execute([
                'username' => $username,
                'email' => $email
            ]);

            if ($check->rowCount() > 0) {
                $error = 'Username or email already exists.';
            } else {
                // Insert user with plain text password
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, email, password, role)
                     VALUES (:username, :email, :password, 'teacher')"
                );

                $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password
                ]);

                $success = 'Account created successfully. You can now login.';
            }

        } catch (PDOException $e) {
            $error = 'Registration failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Student Attendance System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h2>Create Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <p class="hint">
            Already have an account?
            <a href="login.php">Login</a>
        </p>
    </div>
</div>

</body>
</html>
