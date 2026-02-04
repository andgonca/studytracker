<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle Password Change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();

            if (password_verify($current_password, $hashed_password)) {
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed, $_SESSION['user_id']);
                
                if ($update_stmt->execute()) {
                    $message = "Password updated successfully.";
                } else {
                    $error = "Error updating password.";
                }
                $update_stmt->close();
            } else {
                $error = "Current password is incorrect.";
            }
        }
    }

    // Handle Create User
    if (isset($_POST['create_user'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $error = "Username and password are required.";
        } else {
            // Check if username exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = "Username already exists.";
            } else {
                $check_stmt->close();
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $insert_stmt->bind_param("ss", $username, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $message = "User '$username' created successfully.";
                } else {
                    $error = "Error creating user: " . $conn->error;
                }
                $insert_stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Study Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Study Tracker</a>
            <div class="navbar-nav">
                <a class="nav-link" href="index.php">Tracker</a>
                <a class="nav-link" href="maintenance.php">Maintenance</a>
                <a class="nav-link active" href="users.php">Users</a>
                <a class="nav-link text-danger" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">User Management</h2>
        
        <?php if($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">Change My Password</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="change_password" value="1">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">Create New User</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="create_user" value="1">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-success">Create User</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>