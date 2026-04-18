<?php
session_start();
require_once("include/connect.php");

if (!isset($_SESSION['userid'])) {
    echo "<script>
        alert('Please login first');
        window.location='login.php';
    </script>";
    exit();
}

if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 3) {
    echo "<script>
        alert('Access denied! Only Marketing Coordinator allowed.');
        window.location='login.php';
    </script>";
    exit();
}
function sendEmail($to, $subject, $message){
    // TEMP disable error
    return true;
}

$search = $_GET['search'] ?? '';
$faculty_filter = $_GET['faculty_filter'] ?? '';

// Total Students (role = student)
$student_count = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM tbluser 
                                                                WHERE roleid = 2
                                                            "))['total'];

// Total Faculty
$faculty_count = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM tblfaculty
                                                            "))['total'];

// Total Submissions
$submission_count = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM tblcontribution
                                                                "))['total'];

// Total Reports (example: selected contributions)
$report_count = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM tblcontribution 
                                                                WHERE status = 'selected'
                                                            "))['total'];

$userid = $_SESSION['userid'];
$roleid = $_SESSION['roleid'];

// 🔔 Count unread notifications for this coordinator
$unread_count = mysqli_fetch_assoc(mysqli_query($connection, "
    SELECT COUNT(*) as total 
    FROM tblnotification_log 
    WHERE sent_to = '$userid' AND status = 'unread'
"))['total'];

// Get faculty first
$user_data = mysqli_fetch_assoc(mysqli_query($connection, "SELECT facultyid FROM tbluser 
                                                        WHERE userid = '$userid'
                                                        "));

$facultyid = $user_data['facultyid']; // ✅ define BEFORE query

$where = "WHERE 1";

// Search
if (!empty($search)) {
    $where .= " AND s.username LIKE '%$search%'";
}

// Faculty filter
if (!empty($faculty_filter)) {
    $where .= " AND s.facultyid = '$faculty_filter'";
}

// Coordinator restriction
if ($roleid == 3) {
    $where .= " AND s.facultyid = '$facultyid'";
}

// SET LIMIT + PAGE
$limit = 5; // records per page

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// GET TOTAL RECORDS
$count_query = mysqli_query($connection, "
SELECT COUNT(*) as total
FROM tblcontribution c
LEFT JOIN tbluser s ON c.studentid = s.userid
$where
");

$total_rows = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_rows / $limit);

$query = mysqli_query($connection, "
SELECT c.*, 
       cat.categoryname, 
       cat.categoryclosuredate,
       u.username as selector,
       s.username as studentname,
       f.facultyname
FROM tblcontribution c
LEFT JOIN tblcategory cat ON c.categoryid = cat.categoryid
LEFT JOIN tbluser u ON c.selected_by = u.userid
LEFT JOIN tbluser s ON c.studentid = s.userid
LEFT JOIN tblfaculty f ON s.facultyid = f.facultyid
$where
ORDER BY c.contributionid DESC
LIMIT $offset, $limit
");

// $facultyid = $user_data['facultyid'];
if (isset($_POST['btnselect'])) {

    $id = $_POST['select_id'];
    $coordinator = $_SESSION['userid'];

    mysqli_query($connection, "
        UPDATE tblcontribution 
        SET status='selected',
            is_selected_for_publication = 1,
            selected_by = '$coordinator',
            selecteddate = NOW()
        WHERE contributionid = '$id'
    ");

    // ✅ Get student info
    $res = mysqli_query($connection, "
        SELECT u.userid, u.email, u.username, c.title
        FROM tblcontribution c
        JOIN tbluser u ON c.studentid = u.userid
        WHERE c.contributionid = '$id'
    ");

    $data = mysqli_fetch_assoc($res);

    // ✅ INSERT NOTIFICATION (FIXED)
    mysqli_query($connection, "
        INSERT INTO tblnotification_log(contributionid, sent_to, sent_date, status)
        VALUES('$id', '{$data['userid']}', NOW(), 'unread')
    ");

    // ✅ EMAIL
    sendEmail(
        $data['email'],
        "🎉 Contribution Selected",
        "Hello {$data['username']},<br>
        Your contribution <b>{$data['title']}</b> has been <b>SELECTED</b>."
    );
}

if (isset($_POST['btnreject'])) {

    $id = $_POST['reject_id'];

    mysqli_query($connection, "
        UPDATE tblcontribution 
        SET status='rejected'
        WHERE contributionid = '$id'
    ");

    $res = mysqli_query($connection, "
        SELECT u.userid, u.email, u.username, c.title
        FROM tblcontribution c
        JOIN tbluser u ON c.studentid = u.userid
        WHERE c.contributionid = '$id'
    ");

    $data = mysqli_fetch_assoc($res);

    // ✅ INSERT NOTIFICATION (FIXED)
    mysqli_query($connection, "
        INSERT INTO tblnotification_log(contributionid, sent_to, sent_date, status)
        VALUES('$id', '{$data['userid']}', NOW(), 'unread')
    ");

    sendEmail(
        $data['email'],
        "❌ Contribution Rejected",
        "Hello {$data['username']},<br>
        Your contribution <b>{$data['title']}</b> has been <b>REJECTED</b>."
    );
}

// AUTO REMINDER AFTER 14 DAYS, SEND EMAIL
$reminder = mysqli_query($connection, "
SELECT c.contributionid, c.title, u.email
FROM tblcontribution c
JOIN tbluser u ON c.studentid = u.userid
LEFT JOIN tblcomment cm ON c.contributionid = cm.contributionid
WHERE cm.commentid IS NULL
AND DATEDIFF(CURDATE(), c.submission_date) > 14
");
while($row = mysqli_fetch_array($reminder)){

    sendEmail(
        $row['email'],
        "⏰ Reminder: No Feedback Yet",
        "Your contribution <b>{$row['title']}</b> has not received feedback after 14 days."
    );
}

if (isset($_POST['btncomment'])) {
    $cid = $_POST['cid'];
    $comment = $_POST['comment'];
    $coordinator = $_SESSION['userid'];

    // ✅ Insert comment
    mysqli_query($connection, "
        INSERT INTO tblcomment(contributionid, coordinatorid, comment_text, comment_date)
        VALUES('$cid','$coordinator','$comment', NOW())
    ");

    // ✅ Get student
    $res = mysqli_query($connection, "
        SELECT studentid 
        FROM tblcontribution 
        WHERE contributionid = '$cid'
    ");

    $data = mysqli_fetch_assoc($res);
    $studentid = $data['studentid'];

    // ✅ Prevent duplicate notification
    $check = mysqli_query($connection, "
        SELECT * FROM tblnotification_log 
        WHERE contributionid = '$cid' AND sent_to = '$studentid'
    ");

    if (mysqli_num_rows($check) == 0) {
        mysqli_query($connection, "
            INSERT INTO tblnotification_log (contributionid, sent_to, sent_date, status)
            VALUES ('$cid', '$studentid', NOW(), 'unread')
        ");
    }
}

if (isset($_POST['btnedit'])) {

    $id = $_POST['edit_id'];
    $title = $_POST['edit_title'];
    $description = $_POST['edit_description'];

    // 🔒 SECURITY: Ensure coordinator edits ONLY own faculty
    $check = mysqli_query($connection, "
        SELECT s.facultyid
        FROM tblcontribution c
        JOIN tbluser s ON c.studentid = s.userid
        WHERE c.contributionid = '$id'
    ");

    $data = mysqli_fetch_assoc($check);

    if ($roleid == 3 && $data['facultyid'] != $facultyid) {
        echo "<script>alert('Access denied!');</script>";
        exit();
    }

    // 🔥 OPTIONAL: Prevent editing after closure date
    $date_check = mysqli_query($connection, "
        SELECT cat.categoryclosuredate
        FROM tblcontribution c
        JOIN tblcategory cat ON c.categoryid = cat.categoryid
        WHERE c.contributionid = '$id'
    ");

    $date_data = mysqli_fetch_assoc($date_check);
    $today = date("Y-m-d");
    $closure = date("Y-m-d", strtotime($date_data['categoryclosuredate']));

    if ($today > $closure) {
        echo "<script>alert('Cannot edit after deadline');</script>";
    } else {

        mysqli_query($connection, "
            UPDATE tblcontribution
            SET title='$title',
                description='$description'
            WHERE contributionid='$id'
        ");

        echo "<script>alert('Updated Successfully');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Coordinator Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- <link rel="stylesheet" href="assets/css/student.css"> -->
    <link rel="stylesheet" href="assets/css/coordinator.css">
</head>

<body>

    <div class="container-fluid">

        <div class="row">

            <!-- SIDEBAR -->

            <div class="col-lg-2 col-md-3 sidebar p-3">

                <h4 class="text-center mb-4">Coordinator Panel</h4>

                <a href="contribution_list.php"><i class="fa fa-home"></i> Dashboard</a>

                <a href="student_list.php"><i class="fa fa-users"></i>Students</a>

                <a href="coordinator_guest_view.php"><i class="fa fa-user-friends"></i> View Guests</a>

                <a href="exception_report.php"><i class="fa fa-exclamation-triangle"></i> Exception Report</a>

                <!-- <a href="report_faculty.php"><i class="fa fa-cog"></i> Stastic Report</a> -->

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

                        <a href="notifications.php" class="position-relative">
    🔔
    <?php
    $noti = mysqli_fetch_assoc(mysqli_query($connection, "
        SELECT COUNT(*) as total 
        FROM tblnotification_log 
        WHERE sent_to = '$userid' AND status = 'unread'
    "));
    $count = $noti['total'];
    ?>

    <?php if ($count > 0) { ?>
        <span class="badge bg-danger">
            <?php echo $count; ?>
        </span>
    <?php } ?>
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

                        <h5>
                        <?php 
                        if ($roleid == 3) echo "Faculty Contributions";
                        else echo "All Contributions";
                        ?>
                        </h5>

                    </div>

                    <div class="container mt-4" style="max-width:700px;">
                        <?php if($search || $faculty_filter){ ?>
                            <div class="alert alert-info">
                                Filter applied. Click "Show All" to reset.
                            </div>
                        <?php } ?>
                        <!-- Add Search Box and Add Dropdown for faculties -->
                        <form method="GET" class="row mb-3">

                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by student name"
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="col-md-4">
                        <select name="faculty_filter" class="form-control">
                            <option value="">All Faculties</option>

                            <?php
                            $f = mysqli_query($connection, "SELECT * FROM tblfaculty");
                            while($fac = mysqli_fetch_array($f)){
                            ?>
                                <option value="<?php echo $fac['facultyid']; ?>"
                                <?php if($faculty_filter == $fac['facultyid']) echo "selected"; ?>>
                                    <?php echo $fac['facultyname']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            🔍 Filter
                        </button>
                    </div>

                    <!-- ✅ SHOW ALL BUTTON -->
                    <div class="col-md-2">
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary w-100">
                            🔄 Show All
                        </a>
                    </div>

                </form>
                        
                    <div class="mt-4 d-flex justify-content-center">

                        <?php for($i = 1; $i <= $total_pages; $i++){ ?>

                            <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&faculty_filter=<?php echo $faculty_filter; ?>"
                            class="btn btn-sm <?php if($i == $page) echo 'btn-primary'; else echo 'btn-outline-primary'; ?> mx-1">
                                <?php echo $i; ?>
                            </a>

                        <?php } ?>

                    </div>
                        <?php

                        while ($row = mysqli_fetch_array($query)) {
                        ?>

                            <!-- <div class="feed-card"> -->
                                <div class="card mt-5 p-4">

                                <div class="d-flex justify-content-between mt-4">

                                    <strong><?php echo $row['title']; ?></strong>

                                    <span class="badge 
                                    <?php
                                    if ($row['status'] == "selected") echo "bg-success";
                                    elseif ($row['status'] == "rejected") echo "bg-danger";
                                    elseif ($row['status'] == "submitted") echo "bg-warning text-dark";
                                    else echo "bg-secondary";
                                    ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                    </span>

                                </div>

                                <small class="text-muted">
                                    👤 <?php echo $row['studentname'] ?? 'N/A'; ?> |
                                    🏫 <?php echo $row['facultyname'] ?? 'N/A'; ?>
                                    📂 <?php echo $row['categoryname']; ?>
                                </small>

                                
                                <p>
                                    <?php echo $row['description']; ?>
                                </p>

                                <!-- <hr> -->
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
                                <!-- DOWNLOAD FILE BUTTON -->
                                <?php if ($row['filepath1']) { ?>
                                    <a href="upload/<?php echo $row['filepath1']; ?>" download class="btn btn-sm btn-outline-primary">
                                        ⬇ Download File 1
                                    </a><br>
                                <?php } ?>

                                <?php if ($row['filepath2']) { ?>
                                    <a href="upload/<?php echo $row['filepath2']; ?>" download class="btn btn-sm btn-outline-primary">
                                        ⬇ Download File 2
                                    </a><br>
                                <?php } ?>

                                <?php if ($row['filepath3']) { ?>
                                    <a href="upload/<?php echo $row['filepath3']; ?>" download class="btn btn-sm btn-outline-primary">
                                        ⬇ Download File 3
                                    </a><br>
                                <?php } ?>

                                <!-- <hr> -->
                                
                                <small class="text-muted">Comment: </small>
                                <br>
                                <?php
                                    $comments = mysqli_query($connection, "
                                    SELECT c.*, u.username 
                                    FROM tblcomment c
                                    JOIN tbluser u ON c.coordinatorid = u.userid
                                    WHERE c.contributionid = '".$row['contributionid']."'
                                    ");

                                    while($c = mysqli_fetch_array($comments)){
                                        echo "<small><b>{$c['username']}:</b> {$c['comment_text']}</small><br>";
                                    }
                                ?>
                                <form method="POST">
                                    <input type="hidden" name="cid" value="<?php echo $row['contributionid']; ?>">

                                    <textarea name="comment" class="form-control mb-2" placeholder="Write comment..." required></textarea>

                                    <button class="btn btn-primary btn-sm" name="btncomment">
                                        Submit Comment
                                    </button>
                                </form>

                                <hr>

                                <div class="d-flex justify-content-between small text-muted">

                                    <span>Submitted: <?php echo $row['submission_date']; ?></span>

                                    <span>Final Closure Date: <?php echo $row['categoryclosuredate']; ?></span>

                                    <span>
                                        Selected By: <?php echo $row['selector'] ?? "-"; ?>
                                    </span>

                                </div>

                                <div class="text-end small text-muted">
                                    Selected Date: <?php echo $row['selecteddate'] ?? "-"; ?>
                                </div>

                                <!-- Contribution edit -->
                                <?php if ($roleid == 3) { ?>
                                    <button class="btn btn-warning btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal<?php echo $row['contributionid']; ?>">
                                        ✏ Edit
                                    </button>
                                <?php } ?>
                                <div class="modal fade" id="editModal<?php echo $row['contributionid']; ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">

                                            <div class="modal-header bg-warning">
                                                <h5>Edit Contribution</h5>
                                                <button class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">

                                                <form method="POST">

                                                    <input type="hidden" name="edit_id" 
                                                        value="<?php echo $row['contributionid']; ?>">

                                                    <div class="mb-3">
                                                        <label>Title</label>
                                                        <input type="text" name="edit_title" 
                                                            class="form-control"
                                                            value="<?php echo $row['title']; ?>" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label>Description</label>
                                                        <textarea name="edit_description" 
                                                            class="form-control"><?php echo $row['description']; ?></textarea>
                                                    </div>

                                                    <button type="submit" name="btnedit" 
                                                        class="btn btn-warning w-100">
                                                        Update Contribution
                                                    </button>

                                                </form>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="select_id" value="<?php echo $row['contributionid']; ?>">
                                    <button class="btn btn-success btn-sm" name="btnselect">
                                        Select
                                    </button>
                                
                                    <input type="hidden" name="reject_id" value="<?php echo $row['contributionid']; ?>">
                                    <button class="btn btn-danger btn-sm" name="btnreject">Reject</button>
                                </form>

                            </div>

                            <?php
                            $today = date("Y-m-d");

                            if ($today <= $row['categoryclosuredate']) {
                            ?>
                                
                            <?php
                            } else {
                                echo '<span class="text-danger small">Closed</span>';
                            }
                            ?>

                            

                        <?php
                        }
                        ?>

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

                                            <label>File 1</label>

                                            <input type="file" name="file1" class="form-control">

                                        </div>

                                        <div class="col-md-4 mb-3">

                                            <label>File 2</label>

                                            <input type="file" name="file2" class="form-control">

                                        </div>

                                        <div class="col-md-4 mb-3">

                                            <label>File 3</label>

                                            <input type="file" name="file3" class="form-control">

                                        </div>

                                    </div>

                                    <button type="submit" name="btnsubmit" class="btn btn-primary w-100">

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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>