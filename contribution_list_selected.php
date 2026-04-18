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

if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 4) {
    echo "<script>
        alert('Access denied! Only Marketing Manager allowed.');
        window.location='login.php';
    </script>";
    exit();
}

date_default_timezone_set("Asia/Singapore");

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
       ay.academicyearid,
       ay.yearname,
       ay.final_closure_date,
       u.username as selector,
       s.username as studentname,
       f.facultyname
FROM tblcontribution c
LEFT JOIN tblcategory cat ON c.categoryid = cat.categoryid
LEFT JOIN tblacademicyear ay ON cat.academicyearid = ay.academicyearid
LEFT JOIN tbluser u ON c.selected_by = u.userid
LEFT JOIN tbluser s ON c.studentid = s.userid
LEFT JOIN tblfaculty f ON s.facultyid = f.facultyid
$where
and c.status = 'selected'
ORDER BY c.contributionid DESC
LIMIT $offset, $limit
");


?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Manager Dashboard</title>

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

                <h4 class="text-center mb-4">Manager Panel</h4>

                <a href="contribution_list_selected.php"><i class="fa fa-home"></i> Dashboard</a>

                <!-- <a href="report_exception.php"><i class="fa fa-exclamation-triangle"></i> Exception Report</a> -->

                <a href="report_faculty.php"><i class="fa fa-cog"></i> Statistics Report</a>

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

                            <div class="feed-card">

                                <div class="d-flex justify-content-between">

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
                                    📂 <?php echo $row['categoryname']; ?> |
                                    🎓 <?php echo $row['yearname']; ?>
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

                                <!-- <hr> -->

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
                                <span>Final Closure Date: <?php echo $row['final_closure_date']; ?></span>
                                 <?php
                                    $today = date("Y-m-d");

                                    if ($today <= $row['final_closure_date']) {
                                    ?>
                                        
                                    <?php
                                    } else {
                                        echo '<span class="text-danger small">(Closed)</span>';
                                    }
                                ?>

                                <div class="mt-3">
                                    <?php if ($today > $row['final_closure_date']) { ?>
                                        <a href="download_all_selected.php?academicyearid=<?php echo $row['academicyearid']; ?>" class="btn btn-success btn-sm">
                                            📦 Download ZIP for <?php echo htmlspecialchars($row['yearname']); ?>
                                        </a>
                                    <?php } else { ?>
                                        <span class="text-danger small">ZIP download available after the final closure date.</span>
                                    <?php } ?>
                                </div>

                                <br>

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
                                                SELECT *
                                                FROM tblcategory
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
