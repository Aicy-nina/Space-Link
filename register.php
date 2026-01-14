<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $national_id = isset($_POST['national_id']) ? trim($_POST['national_id']) : null;

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($role === 'host' && empty($national_id)) {
        $error = "National ID is required for hosts.";
    } else {
        // === PRESENTATION POINT: USER REGISTRATION ===
        // This function call creates the new user in the database.
        // It handles password hashing (security) inside 'includes/auth.php'.
        $result = registerUser($pdo, $first_name, $last_name, $username, $email, $password, $role, $national_id);
        if ($result === true) {
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Space Link</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function toggleNationalId() {
            const role = document.getElementById('role').value;
            const nationalIdGroup = document.getElementById('national-id-group');
            if (role === 'host') {
                nationalIdGroup.style.display = 'block';
                document.getElementById('national_id').required = true;
            } else {
                nationalIdGroup.style.display = 'none';
                document.getElementById('national_id').required = false;
            }
        }
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Space Link</a>
        </div>
    </nav>

    <div class="auth-container">
        <h2>Create an Account</h2>
        <?php if ($error): ?>
            <div style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="color: green; margin-bottom: 15px;"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">I want to:</label>
                <select id="role" name="role" required onchange="toggleNationalId()">
                    <option value="client">Book Venues</option>
                    <option value="host">List Venues</option>
                </select>
            </div>
            <div class="form-group" id="national-id-group" style="display: none;">
                <label for="national_id">National ID</label>
                <input type="text" id="national_id" name="national_id">
            </div>
            <button type="submit" class="btn full-width">Register</button>
        </form>
        <p style="margin-top: 15px; text-align: center;">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</body>
</html>
