<?php
session_start(); // بدء الجلسة

// تحقق من تسجيل الدخول
if (!isset($_SESSION['username'])) {
    header("Location: login.html"); // توجيه إلى صفحة تسجيل الدخول إذا لم يكن مسجلاً الدخول
    exit();
}

// تحقق من انتهاء الجلسة (30 دقيقة)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_unset(); // إفراغ الجلسة
    session_destroy(); // تدمير الجلسة
    header("Location: login.html"); // إعادة توجيه إلى صفحة تسجيل الدخول
    exit();
}

// تحديث وقت تسجيل الدخول
$_SESSION['login_time'] = time(); // تحديث الوقت عند كل زيارة للصفحة

// المحتوى المحمي
echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!";
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "logindatabase"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if inputs are set
    if (isset($_POST['topicInput']) && isset($_POST['usernameInput'])) {
        $topic = $_POST['topicInput'];
        $username = $_POST['usernameInput'];

        // Prepare and insert the topic
        $stmt = $conn->prepare("INSERT INTO topics (username, topic) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $topic);

        if ($stmt->execute()) {
            // Redirect back to topics page after submission
            header("Location: topics1.php");
            exit();
        } else {
            echo "Error adding topic: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Retrieve topics for the user
$userTopics = [];
if (isset($_POST['usernameInput'])) {
    $user = $_POST['usernameInput'];

    // Prepare and execute the select statement
    $stmt = $conn->prepare("SELECT topic FROM topics WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $userTopics[] = htmlspecialchars($row['topic']);
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Topics</title>
    <meta charset="utf-8">
    <link rel="icon" href="images/logoicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>

<body class="topics">
    <header onmouseover="animateLogo()" onmouseout="resetLogoAnimation()">
        <img src="images/weblogo.png" alt="logo">
    </header>
    
    <hr>

    <!-- Navigation Menu -->
    <nav>
        <ul>
        <li><a href="home.php">Home</a></li>
        <li><a href="home.php">Home</a></li>
            <li><a href="topics1.php">Topics</a></li>
            <li><a href="Profile.php">Profile</a></li>
            <li><a href="Registration.html">Registration</a></li>
            <li><a href="LogIn.html">Log In</a></li>
            <li><a href="resetPassword.php">Reset Password</a></li>
        </ul>
    </nav>
    
    <hr>
    
    <!-- Main Content Section for Topics -->
    <main>
        <!-- Topic Form -->
        <div class="topic-form">
            <h2>Add a New Topic</h2>
            <form id="topicForm" method="POST" action="topics1.php">
                <input type="text" id="usernameInput" name="usernameInput" placeholder="Enter your name..." required>
                <input type="text" id="topicInput" name="topicInput" placeholder="Enter your topic..." required>
                <button type="submit">Upload Topic</button>
            </form>
        </div>

        <!-- Display Uploaded Topics -->
        
    </main>
    <hr>
    <footer>Copyright © 2023 Beauty & Glow<br><a href="mailto:me@gmail.com">beautyandglow@gmail.com</a><br>
        <a href="polices.html">Here You Can See Our Privacy Policies!</a>
    </footer>
    <hr>
</body>

</html>