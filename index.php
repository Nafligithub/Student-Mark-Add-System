<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Admin Check
    $admin_res = $conn->query("SELECT * FROM admin WHERE username='$user' AND password='$pass'");
    if ($admin_res->num_rows > 0) {
        $_SESSION['role'] = 'admin';
        header("Location: admin.php");
        exit();
    }

    // Student Check
    $student_res = $conn->query("SELECT * FROM students WHERE username='$user' AND password='$pass'");
    if ($student_res->num_rows > 0) {
        $row = $student_res->fetch_assoc();
        $_SESSION['role'] = 'student';
        $_SESSION['student_id'] = $row['id'];
        header("Location: student_dashboard.php");
        exit();
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>NextGen IT Academy - Login</title>
    <style>
        body { font-family: sans-serif; display: flex; height: 100vh; margin: 0; background: #f4f4f4; }
        .wrapper { display: flex; width: 1000px; margin: auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .img-side { width: 55%; background: url('kids-enjoying-reading-stockcake_2.webp') no-repeat center center; background-size: cover; }
        .form-side { width: 45%; padding: 50px; display: flex; flex-direction: column; justify-content: center; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo img { width: 100px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;}
        button { width: 100%; padding: 12px; background: #0056b3; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .error { color: red; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="img-side"></div>
        <div class="form-side">
            <div class="logo">
                <img src="ChatGPT Image May 2, 2026, 09_11_28 AM_2.jpg" alt="Logo">
                <h2 style="color: #0056b3;">NextGen IT Academy</h2>
            </div>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <p style="text-align:center; font-size: 14px; margin-top: 20px;">New Student? <a href="register.php">Register Here</a></p>
        </div>
    </div>
</body>
</html>