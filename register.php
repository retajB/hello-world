<?php
// edit by: ELAF SULTAN 
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root"; 
$passwordDB = ""; 
$dbname = "logindatabase"; 

$conn = mysqli_connect($servername, $username, $passwordDB, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mySubmit"])) {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = htmlspecialchars(trim($_POST["password"]));
    $passwordhash = password_hash($password, PASSWORD_DEFAULT);

    $errors = [];

    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // Check for strong password requirements
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must contain at least one special character (e.g., !@#$%^&*).";
    }

    if (count($errors) > 0) {
        echo "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR name = ?");
        $stmt->bind_param("ss", $email, $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div class='alert alert-danger'>Username or email already exists.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, created_at, login_attempts) VALUES (?, ?, ?, NOW(), 0)");
            $stmt->bind_param("sss", $name, $email, $passwordhash);
            if ($stmt->execute()) {
                echo "New record created successfully.";
            } else {
                echo "SQL error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>