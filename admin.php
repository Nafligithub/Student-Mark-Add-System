<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') header("Location: index.php");
include('db.php');

// Action 1: Delete Student
if (isset($_GET['del'])) { 
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM students WHERE id=$id"); 
    header("Location: admin.php?msg=Student Deleted"); 
}

// Action 2: Reset Password
if (isset($_GET['reset'])) { 
    $id = (int)$_GET['reset'];
    $conn->query("UPDATE students SET password='12345', password_changed=0 WHERE id=$id"); 
    header("Location: admin.php?msg=Password Reset to 12345"); 
}

// Action 3: Save Dynamic Marks
if (isset($_POST['save_marks'])) {
    $sid = (int)$_POST['student_id'];
    $sub = $conn->real_escape_string($_POST['subject']);
    $marks = $_POST['marks'] ?? []; 

    $conn->query("DELETE FROM student_marks WHERE student_id=$sid AND subject='$sub'");
    foreach ($marks as $index => $val) {
        $term = $index + 1;
        $m_val = (int)$val;
        $conn->query("INSERT INTO student_marks (student_id, subject, term_number, mark_value) VALUES ($sid, '$sub', $term, $m_val)");
    }
    header("Location: admin.php?msg=Marks Updated Successfully");
}

// Data Fetching
$sub_f = isset($_GET['sub']) ? $conn->real_escape_string($_GET['sub']) : '';
$sql = "SELECT s.*, GROUP_CONCAT(m.mark_value ORDER BY m.term_number ASC) as all_marks 
        FROM students s 
        LEFT JOIN student_marks m ON s.id = m.student_id AND s.subject = m.subject";
if($sub_f != '') $sql .= " WHERE s.subject = '$sub_f'";
$sql .= " GROUP BY s.id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - NextGen IT Academy</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f0f2f5; color: #333; }
        header { background: #002140; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .main { padding: 30px; max-width: 1200px; margin: auto; }
        
        /* Official Controls Row */
        .controls-row { 
            background: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; 
            display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
        }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        th { background: #f8f9fa; color: #555; font-weight: 600; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid #f1f1f1; }
        tr:hover { background: #fafafa; }

        /* Status & Badge */
        .mark-badge { background: #e6f7ff; color: #1890ff; border: 1px solid #91d5ff; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-size: 14px; }
        
        /* Buttons */
        .btn { padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; }
        .btn-details { background: #1890ff; color: white; }
        .btn-reset { background: #faad14; color: white; }
        .btn-delete { background: #ff4d4f; color: white; }
        .btn-add-mark { background: #52c41a; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .btn:hover { opacity: 0.85; transform: translateY(-1px); }

        /* Modal & Overlay */
        .overlay { display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index: 90; backdrop-filter: blur(3px); }
        .modal { display:none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border-radius: 15px; z-index: 100; width: 400px; }
        .modal-input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .modal-input-group { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    </style>
</head>
<body>

<header>
    <h2 style="margin:0; font-weight: 500;">NextGen <span style="font-weight: 300;">Admin</span></h2>
    <a href="logout.php" style="color:white; text-decoration:none; font-weight:600; border: 1px solid white; padding: 5px 15px; border-radius: 5px;">Logout</a>
</header>

<div class="main">
    <?php if(isset($_GET['msg'])): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; border-left: 5px solid #28a745;">
            <b>Success:</b> <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="controls-row">
        <div>
            <strong>Filter:</strong>
            <form method="GET" style="display:inline; margin-left:10px;">
                <select name="sub" onchange="this.form.submit()" style="padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                    <option value="">All Subjects</option>
                    <option value="IT" <?= ($sub_f=='IT')?'selected':'' ?>>IT</option>
                    <option value="Second Language Tamil" <?= ($sub_f=='Second Language Tamil')?'selected':'' ?>>Tamil</option>
                </select>
            </form>
        </div>
        <input type="text" id="nameSearch" onkeyup="filterTable()" placeholder="🔍 Search student name..." style="padding:10px; width:300px; border-radius:6px; border:1px solid #ddd; outline:none;">
    </div>

    <table id="studentTable">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Subject</th>
                <th>Marks (Dynamic)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="student-name">
                    <div style="font-weight:600; color:#111;"><?= $row['full_name'] ?></div>
                    <div style="font-size:12px; color:#888;"><?= $row['whatsapp'] ?></div>
                </td>
                <td><span style="background:#f0f2f5; padding:4px 10px; border-radius:15px; font-size:12px;"><?= $row['subject'] ?></span></td>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <button class="btn btn-add-mark" onclick="openMarksModal(<?= $row['id'] ?>, '<?= $row['full_name'] ?>', '<?= $row['subject'] ?>', '<?= $row['all_marks'] ?>')">+</button>
                        <span class="mark-badge">[<?= $row['all_marks'] ?: '0' ?>]</span>
                    </div>
                </td>
                <td>
                    <button class="btn btn-details" onclick="showDetails('<?= $row['full_name'] ?>', '<?= $row['name_with_initials'] ?>', '<?= $row['grade'] ?>', '<?= $row['dob'] ?>', '<?= $row['whatsapp'] ?>')">Details</button>
                    <a href="?reset=<?= $row['id'] ?>" class="btn btn-reset">Reset</a>
                    <a href="?del=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete Student?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Marks -->
<div class="overlay" id="overlay" onclick="closeModals()"></div>
<div class="modal" id="markModal">
    <h3 id="mTitle" style="color:#002140; margin-top:0;">Edit Marks</h3>
    <form method="POST">
        <input type="hidden" name="student_id" id="mSid">
        <input type="hidden" name="subject" id="mSub">
        <div id="marksContainer" style="max-height: 300px; overflow-y: auto; margin-bottom:15px; padding-right:5px;"></div>
        <button type="button" onclick="addMarkField()" style="width:100%; padding:10px; border:1px dashed #1890ff; color:#1890ff; background:none; cursor:pointer; margin-bottom:15px; font-weight:600; border-radius:6px;">+ Add New Term</button>
        <button type="submit" name="save_marks" style="width:100%; padding:12px; background:#52c41a; color:white; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">Save All Marks</button>
    </form>
</div>

<!-- Modal for Details -->
<div class="modal" id="detailsModal">
    <h3 style="color:#1890ff; margin-top:0;">Student Information</h3>
    <div id="detailsBody" style="line-height: 2;"></div>
    <button onclick="closeModals()" style="width:100%; margin-top:20px; padding:10px; background:#f0f2f5; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Close</button>
</div>

<script>
let termCount = 0;

function addMarkField(value = "") {
    termCount++;
    const container = document.getElementById('marksContainer');
    const div = document.createElement('div');
    div.className = 'modal-input-group';
    div.innerHTML = `
        <label style="font-size:12px; width:50px; font-weight:bold;">Term ${termCount}</label>
        <input type="number" name="marks[]" value="${value}" class="modal-input" required>
        <button type="button" onclick="this.parentElement.remove()" style="color:#ff4d4f; border:none; background:none; cursor:pointer; font-size:20px;">&times;</button>
    `;
    container.appendChild(div);
}

function openMarksModal(id, name, sub, existingMarks) {
    document.getElementById('mSid').value = id;
    document.getElementById('mSub').value = sub;
    document.getElementById('mTitle').innerText = name;
    const container = document.getElementById('marksContainer');
    container.innerHTML = "";
    termCount = 0;

    if(existingMarks) {
        existingMarks.split(',').forEach(m => addMarkField(m));
    } else {
        addMarkField();
    }
    document.getElementById('markModal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function showDetails(full_name, initials, grade, dob, whatsapp) {
    const body = document.getElementById('detailsBody');
    body.innerHTML = `
        <b>Full Name:</b> ${full_name}<br>
        <b>With Initials:</b> ${initials}<br>
        <b>Grade:</b> ${grade}<br>
        <b>Date of Birth:</b> ${dob}<br>
        <b>WhatsApp:</b> ${whatsapp}
    `;
    document.getElementById('detailsModal').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}

function closeModals() {
    document.getElementById('markModal').style.display = 'none';
    document.getElementById('detailsModal').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
}

function filterTable() {
    let input = document.getElementById('nameSearch').value.toUpperCase();
    let rows = document.querySelectorAll('#studentTable tbody tr');
    rows.forEach(row => {
        let name = row.querySelector('.student-name').innerText.toUpperCase();
        row.style.display = name.includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>