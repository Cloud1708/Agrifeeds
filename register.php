<?php
session_start();
require_once 'includes/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $firstName = trim($_POST['firstName']);
        $lastName = trim($_POST['lastName']);
        $countryCode = trim($_POST['countryCode']);
        $phoneNumber = trim($_POST['phoneNumber']);
        
        // Remove leading zero if present
        $phoneNumber = ltrim($phoneNumber, '0');
        
        $contactInfo = $countryCode . $phoneNumber; // Combine for database storage
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $photo = null;

        // Enhanced validation
        if (empty($username) || empty($password) || empty($confirm_password) || empty($firstName) || empty($lastName) || empty($phoneNumber)) {
            $error = "All fields are required";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = "Username can only contain letters, numbers, and underscores";
        } elseif (strlen($username) < 3) {
            $error = "Username must be at least 3 characters long";
        } else {
            // Check username availability
            if ($db->checkUsernameExists($username)) {
                $error = "Username already exists";
            } else {
                // Handle photo upload
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

                // Attempt registration
                error_log("Attempting to register user: " . $username);
                $userID = $db->registerUser($username, $password, 2, $photo, $firstName, $lastName, $contactInfo);

                if ($userID) {
                    $success = "Registration successful! Redirecting to login...";
                    error_log("Registration successful for user: " . $username . " with ID: " . $userID);
                    header('refresh:2;url=index.php');
                } else {
                    $error = "Registration failed. Please try again.";
                    error_log("Registration failed for user: " . $username);
                }
            }
        }
    } catch (Exception $e) {
        $error = "An error occurred during registration: " . $e->getMessage();
        error_log("Registration error: " . $e->getMessage());
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
     <link href="./css/background.css" rel="stylesheet">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phoneNumber');
            
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value;
                // Remove leading zero if present
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }
                // Update the input value
                e.target.value = value;
            });
        });
    </script>
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
                <label class="form-label" for="firstName">First Name</label>
                <input type="text" name="firstName" class="form-control" id="firstName" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="lastName">Last Name</label>
                <input type="text" name="lastName" class="form-control" id="lastName" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
                <div class="valid-feedback">Username is available!</div>
                <div class="invalid-feedback">Please enter a valid username.</div>
                <div id="usernameFeedback" class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <div class="input-group">
                    <select class="form-select" name="countryCode" id="countryCode" style="max-width: 120px;">
                        <option value="+63">+63 (PH)</option>
                        <option value="+1">+1 (US)</option>
                        <option value="+44">+44 (UK)</option>
                        <option value="+61">+61 (AU)</option>
                        <option value="+65">+65 (SG)</option>
                        <option value="+60">+60 (MY)</option>
                        <option value="+66">+66 (TH)</option>
                        <option value="+84">+84 (VN)</option>
                        <option value="+62">+62 (ID)</option>
                    </select>
                    <input type="tel" name="phoneNumber" class="form-control" id="phoneNumber" 
                           placeholder="Enter phone number" required
                           pattern="[0-9]{10,11}" 
                           title="Please enter a valid phone number (10-11 digits)">
                </div>
                <small class="text-muted">Enter your mobile number (with or without starting 0)</small>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function(){
        let usernameValid = false;

        function toggleNextButton() {
            $('#nextButton').prop('disabled', !usernameValid);
        }

        $('#username').on('input', function(){
            var username = $(this).val();
            if (username.length > 0) {
                $.ajax({
                    url: 'AJAX/check_username.php',
                    method: 'POST',
                    data: { username: username },
                    dataType: 'json',
                    success: function(response) {
                        if (response.exists) {
                            // Username is already taken
                            $('#username').removeClass('is-valid').addClass('is-invalid');
                            $('#usernameFeedback').text('Username is already taken.').show();
                            $('#username')[0].setCustomValidity('Username is already taken.');
                            $('#username').siblings('.invalid-feedback').not('#usernameFeedback').hide();
                            usernameValid = false;
                        } else {
                            // Username is valid and available
                            $('#username').removeClass('is-invalid').addClass('is-valid');
                            $('#usernameFeedback').text('').hide();
                            $('#username')[0].setCustomValidity('');
                            $('#username').siblings('.valid-feedback').show();
                            usernameValid = true;
                        }
                        toggleNextButton();
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while checking username availability.'
                        });
                        usernameValid = false;
                        toggleNextButton();
                    }
                });
            } else {
                // Empty input reset
                $('#username').removeClass('is-valid is-invalid');
                $('#usernameFeedback').text('').hide();
                $('#username')[0].setCustomValidity('');
                usernameValid = false;
                toggleNextButton();
            }
        });

        // Initial state
        toggleNextButton();
    });
    </script>
</body>
</html>
