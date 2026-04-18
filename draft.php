<?php
session_start();
require_once("include/connect.php");

$studentid = $_SESSION['userid'];
$query = mysqli_query($connection, "
SELECT c.*, cat.categoryname, cat.categoryclosuredate, u.username as selector
FROM tblcontribution c
LEFT JOIN tblcategory cat ON c.categoryid = cat.categoryid
LEFT JOIN tbluser u ON c.selected_by = u.userid
WHERE c.studentid = '$studentid'
ORDER BY c.contributionid DESC
");
$row = mysqli_fetch_array($query);

if (isset($_POST['btnsubmit'])) {

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

    // File Upload
    $file1 = $_FILES['file1']['name'];
    $file2 = $_FILES['file2']['name'];
    $file3 = $_FILES['file3']['name'];

    $folder = "upload/";

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
        echo "<script>alert('Contribution Submitted Successfully');</script>";
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

                <h4 class="text-center mb-4">Admin Panel</h4>

                <a href="#"><i class="fa fa-home"></i> Dashboard</a>

                <a href="#"><i class="fa fa-users"></i> Manage Students</a>

                <a href="academic_year_register.php"><i class="fa fa-user-tie"></i> Manage Academic Year</a>

                <a href="category_register.php"><i class="fa fa-user-tie"></i> Manage Category</a>

                <a href="#"><i class="fa fa-folder"></i> Submissions</a>

                <a href="#"><i class="fa fa-chart-bar"></i> Reports</a>

                <a href="#"><i class="fa fa-cog"></i> Settings</a>

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

                            <h3>120</h3>

                        </div>

                    </div>


                    <div class="col-lg-3 col-md-6 col-sm-12">

                        <div class="card dashboard-card text-center p-3">

                            <h5>Total Faculty</h5>

                            <h3>15</h3>

                        </div>

                    </div>


                    <div class="col-lg-3 col-md-6 col-sm-12">

                        <div class="card dashboard-card text-center p-3">

                            <h5>Submissions</h5>

                            <h3>230</h3>

                        </div>

                    </div>


                    <div class="col-lg-3 col-md-6 col-sm-12">

                        <div class="card dashboard-card text-center p-3">

                            <h5>Reports</h5>

                            <h3>8</h3>

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

                    <div class="container mt-4" style="max-width:700px;">

                        <?php

                        $query = mysqli_query($connection, "
                        SELECT c.*, cat.categoryclosuredate, cat.categoryname, u.username as selector
                        FROM tblcontribution c
                        LEFT JOIN tblcategory cat ON c.categoryid = cat.categoryid
                        LEFT JOIN tbluser u ON c.selected_by = u.userid
                        WHERE c.studentid = '$studentid'
                        ORDER BY c.contributionid DESC
                        ");

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

                            </div>

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