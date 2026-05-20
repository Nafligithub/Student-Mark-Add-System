<?php
session_start();
// Student login check
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}
include('db.php');

$sid = $_SESSION['student_id'];

// Profile Update Logic (One Time Only)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_u = $conn->real_escape_string($_POST['new_u']); 
    $new_p = $conn->real_escape_string($_POST['new_p']);
    
    $check = $conn->query("SELECT id FROM students WHERE username='$new_u' AND id != $sid");
    if($check->num_rows > 0) {
        $error = "Username already exists!";
    } else {
        $conn->query("UPDATE students SET username='$new_u', password='$new_p', password_changed=1 WHERE id=$sid");
        header("Location: student_dashboard.php?msg=Updated");
        exit();
    }
}

// Fetch Student Data
$res = $conn->query("SELECT * FROM students WHERE id = $sid");
$user = $res->fetch_assoc();

// Fetch Dynamic Marks (New Logic)
// GROUP_CONCAT use panni ellaa term marks-aiyum comma-vaala pirichu edukkirom
$marks_res = $conn->query("SELECT GROUP_CONCAT(mark_value ORDER BY term_number ASC) as all_marks 
                           FROM student_marks 
                           WHERE student_id = $sid AND subject = '{$user['subject']}'");
$marks_data = $marks_res->fetch_assoc();
$marks_array = $marks_data['all_marks'] ? explode(',', $marks_data['all_marks']) : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - NextGen IT</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; padding: 40px; }
        .container { max-width: 700px; margin: auto; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .header { border-bottom: 2px solid #0056b3; padding-bottom: 15px; margin-bottom: 20px; }
        .update-box { background: #fff4e5; border: 1px solid #ffa940; padding: 20px; border-radius: 8px; }
        .input-group { margin-bottom: 10px; }
        input { width: 95%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .btn-update { background: #fa8c16; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .mark-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .total { font-size: 22px; font-weight: bold; color: #0056b3; text-align: center; margin-top: 20px; }
        .btn-logout { display: block; text-align: center; margin-top: 20px; color: #ff4d4f; text-decoration: none; font-weight: bold; }
        .success-chip { background: #f6ffed; border: 1px solid #b7eb8f; color: #52c41a; padding: 5px 15px; border-radius: 20px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    
    <!-- SECTION 1: Profile Update -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin:0;">Welcome, <?= htmlspecialchars($user['full_name']) ?></h2>
            <?php if($user['password_changed'] == 1): ?>
                <span class="success-chip">✓ Profile Updated</span>
            <?php endif; ?>
        </div>

        <?php if($user['password_changed'] == 0): ?>
            <div class="update-box" style="margin-top: 20px;">
                <h3 style="margin-top:0; color: #d46b08;">Update Profile (One-Time Setup)</h3>
                <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
                <form method="POST">
                    <div class="input-group">
                        <input type="text" name="new_u" placeholder="Set New Username" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="new_p" placeholder="Set New Password" required minlength="5">
                    </div>
                    <button type="submit" name="update_profile" class="btn-update">Update & Save</button>
                </form>
            </div>
        <?php else: ?>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">Your login credentials are secured.</p>
        <?php endif; ?>
    </div>

    <!-- SECTION 2: Results Display -->
    <div class="card">
        <div class="header">
            <h3 style="margin:0; color: #0056b3;">Your Exam Results</h3>
            <p style="margin: 5px 0 0 0; color: #777;">Subject: <b><?= htmlspecialchars($user['subject']) ?></b> | Grade: <?= htmlspecialchars($user['grade']) ?></p>
        </div>

        <?php 
        $total = 0;
        if (count($marks_array) > 0): 
            foreach ($marks_array as $index => $mark): 
                $total += (int)$mark;
        ?>
            <div class="mark-row">
                <span>Term <?= $index + 1 ?> Score:</span> 
                <b><?= $mark ?></b>
            </div>
        <?php 
            endforeach; 
        else: 
        ?>
            <p style="text-align:center; color:#999;">Marks not yet entered by Admin.</p>
        <?php endif; ?>

        <div class="total">Total Points: <?= $total ?></div>
        
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

</div>

</body>
</html>