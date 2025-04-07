<?php
session_start(); // Start the session hjgyuhoi
//lamar is here
// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.html"); // Redirect to login page if not logged in
    exit();
}

// Check if the session has expired (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_unset(); // Clear the session
    session_destroy(); // Destroy the session
    header("Location: login.html"); // Redirect to login page
    exit();
}

// Update the login time
$_SESSION['login_time'] = time(); // Update the time on each page visit
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Profile</title>
    <meta charset="utf-8">
    <link rel="icon" href="images/logoicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>

<body>
    <div class="profile-page">
        <header onmouseover="animateLogo()" onmouseout="resetLogoAnimation()">
            <img src="images/weblogo.png" alt="logo">
        </header>
        <hr>
        <nav>
            <ul>
            <li><a href="Home.php">Home</a></li>
            <li><a href="Topics1.php">Topics</a></li>
            <li><a href="Profile.php">Profile</a></li>
            <li><a href="Registration.html">Registration</a></li>
            <li><a href="LogIn.html">Log In</a></li>
            <li><a href="resetPassword.php">Reset Password</a></li>
            </ul>
        </nav>
        <hr>
        <main>
            <div class="profile-container">
                <?php
                // Database connection
                $servername = "localhost"; // Change if necessary
                $db_username = "root";
                $db_password = "";
                $dbname = "logindatabase"; // Your database name

                // Create connection
                $conn = new mysqli($servername, $db_username, $db_password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Initialize variables
                $oldemail=$_SESSION['username']; 

                $username = $_SESSION['username']; // Use the username from the session
                $email = '';
                $bio = '';
                $status = 'Offline'; // Default status
                $profile_photo = 'images/logoicon.png'; // Default photo

                // Handle profile form submission
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $email = $_POST['email']; // Get email from form
                    $oldemail=$_POST['oldemail']; 
                    $bio = $_POST['bio'];
                    $username=$_POST['username'];
                    $status = $_POST['status'] ?? 'Offline'; // Get status safely

                    // Handle file upload
                    if (isset($_FILES['upload-photo']) && $_FILES['upload-photo']['error'] == 0) {
                        $fileTmpPath = $_FILES['upload-photo']['tmp_name'];
                        $fileName = $_FILES['upload-photo']['name'];

                        // Specify the directory where the file will be uploaded
                        $uploadFileDir = './uploaded_files/';
                        if (!is_dir($uploadFileDir)) {
                            mkdir($uploadFileDir, 0755, true); // Create directory if not exists
                        }

                        $dest_path = $uploadFileDir . uniqid() . '-' . $fileName; // Use unique name to prevent overwriting

                        // Move the file to the specified directory
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $profile_photo = $dest_path; // Update profile photo path
                        } else {
                            echo "<p>Error moving the uploaded file.</p>";
                        }
                    }

                    // SQL to update data in user_profiles
                    $updateQuery = "UPDATE profile SET email='$email', username='$username', bio='$bio', status='$status', profile_photo='$profile_photo', updated_at=NOW() WHERE email='$oldemail'";
                    //echo  $updateQuery ;
                    $_SESSION['username']=$email;
                    if ($conn->query($updateQuery) === TRUE) {
                        $updateQuery = "UPDATE users SET email='$email'  WHERE email='$oldemail'";
                        if ($conn->query($updateQuery) === TRUE) {     
                                               echo "<p>Profile updated successfully</p>";
                        }

                    } else {
                        echo "<p>Error: " . $updateQuery . "<br>" . $conn->error . "</p>";
                    }
                }

                // Fetch existing user data
                $result = $conn->query("SELECT * FROM profile WHERE email='$oldemail' ORDER BY id DESC LIMIT 1");
               // echo "SELECT * FROM profile WHERE email='$oldemail' ORDER BY id DESC LIMIT 1"; 
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $username = $row['username']; // Retrieve username
                    $email = $row['email'];       // Retrieve email
                 
                    $bio = $row['bio'];
                    $status = $row['status'];
                    $profile_photo = $row['profile_photo'];
                }

                $conn->close();
                ?>

<form class="profile-form" action="profile.php" method="POST" enctype="multipart/form-data" style="width:100%">
<!-- Profile Photo Section -->
                <div class="profile-photo-section" style=" width: 30%;float: left;">
                    <div class="status-circle" style="top: 300px;     border: 2px solid #09886f">    &nbsp&nbsp status
                        <ul class="status-list">
                            <select name="status">
                                <option class="online" <?php if ($status == 'Online') echo 'selected="selected" style="color: green;"'; ?>value="Online">Online</option>
                                <option class="Busy" <?php if ($status == 'Busy') echo 'selected="selected" style="color: orange;"'; ?> value="Busy">Busy</option>
                                <option class="Offline"  <?php if ($status == 'Offline') echo 'selected="selected" style="color: blue;"'; ?>value="Offline">Offline</option>

                            </select>
                            
                        </ul>
                    </div>
                                        <form class="profile-form" action="profile.php" method="POST" enctype="multipart/form-data">

                    <img class="profile-photo" src="<?php echo $profile_photo; ?>" alt="Profile Picture">
                    <label class="upload-photo">
                        <input type="file" id="upload-photo" name="upload-upload" class="upload-photo-input">
                        <span class="upload-photo-button">Upload new photo</span>
                    </label> 
                    <div class="social-media-icons">
                    <a href="https://x.com/" target="_blank"><i class="fa-brands fa-x"></i></a> 
                    <a href="https://www.instagram.com/" target="_blank"><i class="fa-brands fa-instagram"></i></a> 
                </div>
                </div>
               

                <!-- Profile Information Section -->
                <div class="profile-info" style=" width: 65%;float: right;">
                        <input type="hidden" id="user-status" name="status" value="Offline">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly required>
                        <input type="hidden" id="oldemail" name="oldemail" value="<?php echo htmlspecialchars($email); ?>" >

                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" required><?php echo htmlspecialchars($bio); ?></textarea>
                        <div class="form-buttons">
                            <button type="submit" class="save-button">Save Changes</button>
                            <button type="button" class="cancel-button">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
        <hr>
        <footer>Copyright © 2023 Beauty & Glow<br><a href="mailto:me@gmail.com">beautyandglow@gmail.com</a><br>
            <a href="polices.html">Here You Can See Our Privacy Policies!</a>
        </footer>
        <hr>
    </div>
</body>

</html>
