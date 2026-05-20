<?php
include('db.php');
$success_msg = "";
$error_msg = ""; // Pudhu error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $initials = $_POST['initials'];
    $username = $_POST['username'];
    $dob = $_POST['dob'];
    $grade = $_POST['grade'];
    $subject = $_POST['subject'];
    $whatsapp = $_POST['whatsapp'];

    // 1. Username munnadiye irukkiraatha endru check pannuvom
    $check_user = "SELECT username FROM students WHERE username = '$username'";
    $result = $conn->query($check_user);

    if ($result->num_rows > 0) {
        // Username munnadiye irunthaal intha message varum
        $error_msg = "Error: Username '$username' Allready use this username please give different username.";
    } else {
        // 2. Username illaiyendraal mattum data-vai insert pannuvom
        $sql = "INSERT INTO students (full_name, name_with_initials, username, dob, grade, subject, whatsapp) 
                VALUES ('$full_name', '$initials', '$username', '$dob', '$grade', '$subject', '$whatsapp')";

        if ($conn->query($sql) === TRUE) {
            $success_msg = "Registration Successful! Official Default Password: <b>12345</b>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - NextGen IT Academy</title>
    <style>
        body { font-family: sans-serif; background: #e9ecef; padding: 40px; }
        .reg-card { max-width: 500px; margin: auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        
        /* Message Styles */
        .alert-box { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb; }
        .error-box { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center; border: 1px solid #f5c6cb; }
        
        .btn-submit { width: 100%; padding: 14px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .btn-back { display: block; width: 100%; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; text-align: center; text-decoration: none; margin-top: 10px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="reg-card">
        <h2 style="text-align:center; color: #0056b3;">Student Registration</h2>
        
        <!-- Success Message -->
        <?php if($success_msg != ""): ?>
            <div class="alert-box">
                <?= $success_msg ?> <br><br>
                <a href="index.php" style="color: #155724; font-weight: bold;">Go to Login</a>
            </div>
        <?php endif; ?>

        <!-- Duplicate Username Error Message -->
        <?php if($error_msg != ""): ?>
            <div class="error-box">
                <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="initials" placeholder="Name with Initials" required>
            <input type="text" name="username" placeholder="Choose Username" required>
            <label style="font-size: 12px; color: #666;">Date of Birth</label>
            <input type="date" name="dob" required>
            <select name="grade" required>
                <option value="">Select Grade</option>
                <?php for($i=1; $i<=13; $i++) echo "<option value='$i'>Grade $i</option>"; ?>
            </select>
            <select name="subject" required>
                <option value="IT">IT</option>
                <option value="Second Language Tamil">Second Language Tamil</option>
            </select>
            <input type="text" name="whatsapp" placeholder="WhatsApp Number" required>
            
            <button type="submit" class="btn-submit">Register Student</button>
            <a href="index.php" class="btn-back">Back to Login</a>
        </form>
    </div>
</body>
</html>