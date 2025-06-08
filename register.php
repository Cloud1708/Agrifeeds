<?php
session_start();
require_once 'includes/db.php';

$db = new database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $photo = null;

    if (empty($username) || empty($password) || empty($confirm_password) || empty($name)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($db->checkUsernameExists($username)) {
        $error = "Username already exists";
    } else {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/profile_photos/' . $new_filename;

                if (!is_dir('uploads/profile_photos')) {
                    mkdir('uploads/profile_photos', 0777, true);
                }

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo = $upload_path;
                }
            }
        }

        $userID = $db->registerUser($username, $password, 2, $photo, $name);

        if ($userID) {
            $success = "Registration successful! Redirecting to login...";
            header('refresh:2;url=index.php');
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AgriFeeds | Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f5f7;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 128, 0, 0.1);
            padding: 30px;
            max-width: 460px;
            width: 100%;
            background-color: #fff;
        }

        .card h3 {
            color: #1e4d2b;
            font-weight: bold;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-label {
            color: #333;
            font-weight: 600;
        }

        .form-control {
            border-radius: 10px;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .btn-primary-custom {
            background-color: #28a745;
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .btn-primary-custom:hover {
            background-color: #218838;
        }

        .alert {
            border-radius: 8px;
        }

        .text-muted small {
            display: block;
            margin-top: 15px;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .form-footer a {
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="card">
        <h3><i class="bi bi-person-plus-fill me-2"></i>Register New Account</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" name="name" class="form-control" id="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="photo">Profile Photo (Optional)</label>
                <input type="file" name="photo" class="form-control" id="photo" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary-custom w-100">
                <i class="bi bi-person-check-fill me-1"></i> Register
            </button>
        </form>

        <div class="form-footer">
            Already have an account? <a href="index.php">Login here</a>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
