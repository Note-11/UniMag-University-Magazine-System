<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

session_start();
require_once("include/connect.php");
require_once("backend/mailer.php");
require_once("backend/send_mail.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)) {
    echo "<script>
        alert('Upload failed: file too large. Max 40MB allowed.');
        window.location='student_dashboard.php';
    </script>";
    exit();
}

if (!isset($_SESSION['userid'])) {
    echo "<script>
        alert('Please login first');
        window.location='login.php';
    </script>";
    exit();
}

if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 2) {
    echo "<script>
        alert('Access denied! Only Student allowed.');
        window.location='login.php';
    </script>";
    exit();
}

$studentid = $_SESSION['userid'];

if (isset($_POST['btnsubmit'])) {

    // 🔥 CHECK TERMS AGREEMENT
    if (!isset($_POST['agree'])) {
        echo "<script>alert('You must agree to Terms & Conditions');</script>";
        exit();
    }

    // 🔥 SAVE AGREEMENT
    mysqli_query($connection, "
    INSERT INTO tblterms_and_conditions(studentid, academicyearid, agreed_at)
    VALUES('$studentid', 1, NOW())
    ");

    $categoryid = $_POST['categoryid'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $submission_date = date("Y-m-d");

    // 🔥 STEP 1: Get closure date
    $check = mysqli_query($connection, "
        SELECT categoryclosuredate 
        FROM tblcategory 
        WHERE categoryid = '$categoryid'
    ");
    $data = mysqli_fetch_array($check);
    $today = date("Y-m-d");

    // 🔥 STEP 2: Compare date
    if ($today > $data['categoryclosuredate']) {
        echo "<script>alert('Submission closed! Deadline passed.');</script>";
        exit();
    }

    // ✅ Only runs if NOT closed


    // 🚨 Check if POST size exceeded limit
    if ($_SERVER['CONTENT_LENGTH'] > 40 * 1024 * 1024) {
        echo "<script>
            alert('Upload failed: file(s) exceeded 40 MB limit.');
            window.location.href = 'student_dashboard.php';
        </script>";
        exit();
    }

    // File Upload
    $folder = "upload/";
    $file1 = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES['file1']['name']);
    $file2 = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES['file2']['name']);
    $file3 = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $_FILES['file3']['name']);

    // Define max size (40 MB in bytes)
    $maxSize = 40 * 1024 * 1024;
    $allowed = ['jpg','jpeg','png','gif','pdf','docx'];

    // Validate each file
    foreach (['file1','file2','file3'] as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $filename = $_FILES[$field]['name'];

            // Check size
            if ($_FILES[$field]['size'] > $maxSize) {
                echo "<script>
                alert('File \"$filename\" is too large. Max 40 MB allowed.');
                window.location.href = 'student_dashboard.php';
                </script>";
                exit();
            }

            // Check extension
            if (!in_array($ext, $allowed)) {
                echo "<script>
                alert('File type not allowed. Allowed types: JPG, PNG, GIF, PDF, DOCX');
                window.location.href = 'student_dashboard.php';
                </script>";
                exit();
            }
        }
    }

    move_uploaded_file($_FILES['file1']['tmp_name'], $folder . $file1);
    move_uploaded_file($_FILES['file2']['tmp_name'], $folder . $file2);
    move_uploaded_file($_FILES['file3']['tmp_name'], $folder . $file3);

    $query = mysqli_query($connection, "
        INSERT INTO tblcontribution
        (studentid,categoryid,title,description,submission_date,filepath1,filepath2,filepath3)
        VALUES
        ('$studentid','$categoryid','$title','$description','$submission_date','$file1','$file2','$file3')
    ");

    if ($query) {
        $contributionid = mysqli_insert_id($connection);

        // 🔥 STEP A: Get student's details and faculty
        $student_data = mysqli_fetch_assoc(mysqli_query($connection, "
            SELECT u.username, u.email, f.facultyname, u.facultyid
            FROM tbluser u
            JOIN tblfaculty f ON u.facultyid = f.facultyid
            WHERE u.userid = '$studentid'
        "));
        $facultyid   = $student_data['facultyid'];
        $studentName = $student_data['username'];
        $studentEmail= $student_data['email'];
        $facultyName = $student_data['facultyname'];

        // 🔥 STEP B: Build detailed email message
        $date   = date("Y-m-d");
        $status = "Pending Review";

        $message = "
            <h3>New Student Submission</h3>
            <p><b>Title:</b> $title</p>
            <p><b>Student:</b> $studentName ($studentEmail)</p>
            <p><b>Faculty:</b> $facultyName</p>
            <p><b>Date:</b> $date</p>
            <p><b>Status:</b> $status</p>
        ";

        // 🔥 STEP C: Notify coordinator with enriched message (email)
        sendCoordinatorNotification(
            $connection,
            $facultyid,
            "New Contribution Submitted",
            $message
        );

        // 🔔 STEP D: Insert notification into tblnotification_log
        $res = mysqli_query($connection, "
            SELECT userid FROM tbluser 
            WHERE roleid = 3 AND facultyid = '$facultyid'
        ");
        while ($row = mysqli_fetch_assoc($res)) {
            $coordinatorid = $row['userid'];
            mysqli_query($connection, "
                INSERT INTO tblnotification_log (contributionid, sent_to, sent_date, status)
                VALUES ('$contributionid', '$coordinatorid', NOW(), 'unread')
            ");
        }

        echo "<script>alert('Contribution Submitted Successfully + Notifications Created');</script>";
    } else {
        echo "<script>alert('Submission Failed');</script>";        
    }
}

if (isset($_POST['btnupdate'])) {
    $id = $_POST['editid'];
    $title = $_POST['edittitle'];
    $description = $_POST['editdescription'];

    // Check closure date again (SECURITY)
    $check = mysqli_query($connection, "
        SELECT cat.categoryclosuredate
        FROM tblcontribution c
        JOIN tblcategory cat ON c.categoryid = cat.categoryid
        WHERE c.contributionid = '$id'
    ");

    $data = mysqli_fetch_array($check);
    $today = date("Y-m-d");

    if ($today > $data['categoryclosuredate']) {
        echo "<script>alert('Cannot edit after closure date');</script>";
    } else {
        mysqli_query($connection, "
            UPDATE tblcontribution
            SET title='$title', description='$description'
            WHERE contributionid='$id'
        ");

        echo "<script>alert('Updated Successfully');</script>";
    }
}

// Total Students (role = student)
$student_count = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT COUNT(*) as total FROM tbluser 
    WHERE roleid = 2
"))['total'];

// Total Faculty
$faculty_count = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT COUNT(*) as total FROM tblfaculty
"))['total'];

// Total Submissions
$submission_count = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT COUNT(*) as total FROM tblcontribution
"))['total'];

// Total Reports (example: selected contributions)
$report_count = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT COUNT(*) as total FROM tblcontribution 
    WHERE status = 'selected'
"))['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Student Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/student.css">

    <style>
        body {
            background: #f5f6fa;
        }

        /* Sidebar */

        .sidebar {
            height: auto;
            background: #2c3e50;
            color: white;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px;
        }

        .sidebar a:hover {
            background: #34495e;
        }

        /* Dashboard cards */

        .dashboard-card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>

</head>

<body>

    <div class="container-fluid">

        <div class="row">

            <!-- SIDEBAR -->

            <div class="col-lg-2 col-md-3 sidebar p-3">

                <h4 class="text-center mb-4">Student Panel</h4>

                <a href="student_dashboard.php"><i class="fa fa-home"></i> Dashboard</a>

                <a href="student_edit.php"><i class="fa fa-cog"></i> Profile</a>

                <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>

            </div>


            <!-- MAIN CONTENT -->

            <div class="col-lg-10 col-md-9">

                <!-- NAVBAR -->

                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">

                    <div class="container-fluid">

                        <button class="btn btn-outline-primary d-md-none" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                            ☰
                        </button>

                        <span class="navbar-brand">Dashboard</span>

                        <div class="ms-auto">

                            <span class="me-3">Welcome <?php
                                                        // session_start();
                                                        echo $username = $_SESSION['username'];
                                                        ?></span>

                            <img src="https://i.pravatar.cc/40" class="rounded-circle">

                            <a href="notifications_student.php" class="position-relative">
                                🔔
                                <?php
                                $noti = mysqli_fetch_assoc(mysqli_query($connection, "
                                    SELECT COUNT(*) as total 
                                    FROM tblnotification_log 
                                    WHERE sent_to = '$studentid' AND status = 'unread'
                                "));
                                ?>

                                <span class="badge bg-danger">
                                    <?php echo $noti['total']; ?>
                                </span>
                            </a>
                        </div>

                    </div>

                </nav>


                <!-- DASHBOARD CARDS -->

                <div class="row g-4">

                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card dashboard-card text-center p-3">
                            <h5>Total Students</h5>
                            <h3><?php echo $student_count; ?></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card dashboard-card text-center p-3">
                            <h5>Total Faculty</h5>
                            <h3><?php echo $faculty_count; ?></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card dashboard-card text-center p-3">
                            <h5>Submissions</h5>
                            <h3><?php echo $submission_count; ?></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card dashboard-card text-center p-3">
                            <h5>Reports</h5>
                            <h3><?php echo $report_count; ?></h3>
                        </div>
                    </div>

                </div>


                <!-- TABLE -->

                <div class="card mt-5 p-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h5>My Contributions</h5>

                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addContributionModal">
                            <i class="fa fa-plus"></i> New Contribution
                        </button>

                    </div>

                    <div class="container mt-4" style="max-width: 700px;">

                        <?php
                        // Pagination setup
                        $limit = 5;

                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        if ($page < 1) $page = 1;

                        $offset = ($page - 1) * $limit;

                        $query = mysqli_query($connection, "
                        SELECT c.*, cat.categoryclosuredate, cat.categoryname, u.username as selector, c.filepath1,c.filepath2,c.filepath3
                        FROM tblcontribution c
                        LEFT JOIN tblcategory cat ON c.categoryid = cat.categoryid
                        LEFT JOIN tbluser u ON c.selected_by = u.userid
                        WHERE c.studentid = '$studentid'
                        ORDER BY c.contributionid DESC
                        LIMIT $limit OFFSET $offset
                        ");
                        $total_result = mysqli_query($connection, "
                        SELECT COUNT(*) as total 
                        FROM tblcontribution 
                        WHERE studentid = '$studentid'
                        ");

                        $total_row = mysqli_fetch_assoc($total_result);
                        $total_records = $total_row['total'];

                        $total_pages = ceil($total_records / $limit);

                        while ($row = mysqli_fetch_array($query)) {
                        ?>

                            <div class="feed-card">

                                <div class="d-flex justify-content-between">

                                    <strong><?php echo $row['title']; ?></strong>

                                    <span>
                                        <?php
                                        $status = $row['status'];

                                        if ($status == "selected")
                                            echo '<span class="badge bg-success">Selected</span>';

                                        elseif ($status == "rejected")
                                            echo '<span class="badge bg-danger">Rejected</span>';

                                        elseif ($status == "submitted")
                                            echo '<span class="badge bg-warning text-dark">Submitted</span>';

                                        else
                                            echo '<span class="badge bg-secondary">Draft</span>';
                                        ?>
                                    </span>

                                </div>

                                <p class="text-muted mb-1">
                                    Category: <?php echo $row['categoryname']; ?>
                                </p>

                                <p>
                                    <?php echo $row['description']; ?>
                                </p>

                                <b>Files:</b><br>
                                <!-- IMAGE PREVIEW (AUTO DETECT) -->
                                <?php
                                    $files = [$row['filepath1'], $row['filepath2'], $row['filepath3']];

                                    foreach ($files as $file) {
                                        if ($file) {
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    ?>
                                                <img src="upload/<?php echo $file; ?>" width="120" class="mb-2 rounded">
                                    <?php
                                            }
                                        }
                                    }
                                    ?>
                                <br>
                                <hr>

                                <div class="d-flex justify-content-between small text-muted">

                                    <span>Submitted: <?php echo $row['submission_date']; ?></span>
                                    
                                    <span>
                                        Selected By: <?php echo $row['selector'] ?? "-"; ?>
                                    </span>

                                </div>

                                <div class="text-end small text-muted">
                                    Selected Date: <?php echo $row['selecteddate'] ?? "-"; ?>
                                </div>

                                <hr>

                                <?php
                                    $today = date("Y-m-d");

                                    if ($today <= $row['categoryclosuredate']) {
                                    ?>
                                        <button class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal<?php echo $row['contributionid']; ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                    <?php
                                    } else {
                                        echo '<span class="text-danger small">Closed</span>';
                                    }
                                ?>
                                <span>Due Date: <?php echo $row['categoryclosuredate']; ?></span>

<!-- Feedback Section -->
<?php
$comments = mysqli_query($connection, "
    SELECT cm.comment_text, cm.comment_date, u.username AS coordinator
    FROM tblcomment cm
    JOIN tbluser u ON cm.coordinatorid = u.userid
    WHERE cm.contributionid = '".$row['contributionid']."'
    ORDER BY cm.comment_date DESC
");
?>

<div class="mt-3">
    <h6>Feedback from Coordinator:</h6>

    <?php if (mysqli_num_rows($comments) > 0) { ?>
        <ul class="list-group list-group-flush">
            <?php while ($fb = mysqli_fetch_array($comments)) { ?>
                <li class="list-group-item">
                    <p><?php echo $fb['comment_text']; ?></p>
                    <small class="text-muted">
                        By <?php echo $fb['coordinator']; ?> on <?php echo $fb['comment_date']; ?>
                    </small>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p><i>No feedback yet</i></p>
    <?php } ?>
</div>

                            </div>

                            
                            
                            <div class="modal fade" id="editModal<?php echo $row['contributionid']; ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <div class="modal-header bg-warning">
                                            <h5>Edit Contribution</h5>
                                            <button class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <form method="POST" enctype="multipart/form-data">

                                                <input type="hidden" name="editid" value="<?php echo $row['contributionid']; ?>">

                                                <div class="mb-3">
                                                    <label>Title</label>
                                                    <input type="text" name="edittitle" class="form-control"
                                                        value="<?php echo $row['title']; ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label>Description</label>
                                                    <textarea name="editdescription" class="form-control"><?php echo $row['description']; ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <input type="file" name="file1" class="form-control" required onchange="checkSize(this)">
                                                    <input type="file" name="file2" class="form-control" onchange="checkSize(this)">
                                                    <input type="file" name="file3" class="form-control" onchange="checkSize(this)">
                                                </div>

                                                <button type="submit" name="btnupdate" class="btn btn-warning w-100">
                                                    Update
                                                </button>

                                            </form>

                                        </div>

                                    </div>
                                </div>
                            </div>

                        <?php
                        }
                        ?>
                    <div class="d-flex justify-content-center mt-4">

    <nav>
        <ul class="pagination">

            <!-- Previous -->
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
            <?php } ?>

            <!-- Page Numbers -->
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>

            <!-- Next -->
            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            <?php } ?>

        </ul>
    </nav>

</div>
                    </div>

                </div>

                <!-- ADD CONTRIBUTION MODAL -->

                <div class="modal fade" id="addContributionModal">

                    <div class="modal-dialog modal-lg">

                        <div class="modal-content">

                            <div class="modal-header bg-primary text-white">

                                <h5 class="modal-title">
                                    <i class="fa fa-file-alt"></i> Submit New Contribution
                                </h5>

                                <button class="btn-close" data-bs-dismiss="modal"></button>

                            </div>

                            <div class="modal-body">

                                <form method="POST" enctype="multipart/form-data">

                                    <div class="row">

                                        <div class="col-md-6 mb-3">

                                            <label>Category</label>

                                            <select name="categoryid" class="form-control" required>

                                                <option value="">Select Category</option>

                                                <?php
                                                $q = mysqli_query($connection, "
                                                SELECT * FROM tblcategory
                                                WHERE categoryclosuredate >= CURDATE()
                                                ");

                                                while ($row = mysqli_fetch_array($q)) {
                                                ?>

                                                    <option value="<?php echo $row['categoryid']; ?>"
                                                        <?php if (date("Y-m-d") > $row['categoryclosuredate']) echo "disabled"; ?>>
                                                        <?php echo $row['categoryname']; ?>

                                                        <?php
                                                        if (date("Y-m-d") > $row['categoryclosuredate'])
                                                            echo " (Closed)";
                                                        ?>
                                                    </option>

                                                <?php
                                                }
                                                ?>

                                            </select>

                                        </div>

                                        <div class="col-md-6 mb-3">

                                            <label>Title</label>

                                            <input type="text" name="title" class="form-control" required>

                                        </div>

                                    </div>

                                    <div class="mb-3">

                                        <label>Description</label>

                                        <textarea name="description" class="form-control"></textarea>

                                    </div>

                                    <div class="row">

                                        <div class="col-md-4 mb-3">

                                            <label>File 1 (Max 40 MB)</label>

                                            <input type="file" name="file1" class="form-control" required>

                                        </div>

                                        <div class="col-md-4 mb-3">

                                            <label>File 2 (Max 40 MB)</label>

                                            <input type="file" name="file2" class="form-control">

                                        </div>

                                        <div class="col-md-4 mb-3">

                                            <label>File 3 (Max 40 MB)</label>

                                            <input type="file" name="file3" class="form-control">

                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" name="agree" class="form-check-input" id="agreeTerms">
                                            <label class="form-check-label">
                                                I agree to the 
                                                <a href="terms.php" target="_blank">Terms & Conditions</a>
                                            </label>
                                        </div>

                                    </div>

                                    <button type="submit" name="btnsubmit" 
                                            id="submitBtn"
                                            class="btn btn-primary w-100" disabled>
                                        <i class="fa fa-paper-plane"></i> Submit Contribution
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    </div>

    <script>
    document.getElementById("agreeTerms").addEventListener("change", function () {
        document.getElementById("submitBtn").disabled = !this.checked;
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("agreeTerms").addEventListener("change", function () {
        const btn = document.getElementById("submitBtn");

        btn.disabled = !this.checked;

        if(this.checked){
            btn.classList.remove("btn-secondary");
            btn.classList.add("btn-primary");
        } else {
            btn.classList.remove("btn-primary");
            btn.classList.add("btn-secondary");
        }
    });
    </script>
    <script>
function checkSize(input) {
    if (input.files.length > 0 && input.files[0].size > 40 * 1024 * 1024) {
        alert("File is too large. Max 40 MB allowed.");
        input.value = "";
    }
}
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", function(e) {
        const maxSize = 40 * 1024 * 1024;

        const files = form.querySelectorAll('input[type="file"]');

        for (let input of files) {
            if (input.files.length > 0 && input.files[0].size > maxSize) {
                alert("File too large! Max 40MB allowed.");
                e.preventDefault();
                return;
            }
        }
    });
});
    });
</script>

</body>

</html>