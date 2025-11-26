<?php
require_once 'db.php';

function registerUser($pdo, $first_name, $last_name, $username, $email, $password, $role, $national_id = null) {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        return "Email already registered.";
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    if ($stmt->fetch()) {
        return "Username already taken."; 
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (first_name, last_name, username, email, password, role, national_id) 
            VALUES (:first_name, :last_name, :username, :email, :password, :role, :national_id)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'role' => $role,
            'national_id' => $national_id
        ]);
        return true;
    } catch (PDOException $e) {
        return "Registration failed: " . $e->getMessage();
    }
}

function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}
?>
