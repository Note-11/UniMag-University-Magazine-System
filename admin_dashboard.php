<?php
session_start();
// echo $role = $_SESSION['role'];
require_once("include/connect.php");

if (!isset($_SESSION['userid'])) {
    echo "<script>
        alert('Please login first');
        window.location='login.php';
    </script>";
    exit();
}

if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 1) {
    echo "<script>
        alert('Access denied! Only Admin allowed.');
        window.location='login.php';
    </script>";
    exit();
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

<title>Admin Dashboard</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">


</head>

<body>

<div class="container-fluid">

<div class="row">

<!-- SIDEBAR -->

<div class="col-lg-2 col-md-3 sidebar p-3">

<h4 class="text-center mb-4">Admin Panel</h4>

<a href="admin_dashboard.php"><i class="fa fa-home"></i> Dashboard</a>

<a href="academic_year_register.php"><i class="fa fa-user-tie"></i> Manage Academic Year</a>

<a href="category_register.php"><i class="fa fa-user-tie"></i> Manage Category</a>

<a href="register.php"><i class="fa fa-users"></i> Manage Users</a>

<a href="student_contribution.php"><i class="fa fa-folder"></i> Student Contribution</a>

<a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>

<!-- <a href="#"><i class="fa fa-chart-bar"></i> Reports</a>

<a href="#"><i class="fa fa-cog"></i> Settings</a> -->

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

<span class="me-3">Welcome Admin</span>

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

<div class="card mt-5 p-3">

<h5 class="mb-3">Recent Submissions</h5>

<div class="table-responsive">

<!-- <table class="table table-striped">

<thead>

<tr>

<th>ID</th>

<th>Student</th>

<th>Faculty</th>

<th>Date</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<tr>

<td>1</td>

<td>John</td>

<td>Business</td>

<td>2026-03-01</td>

<td><span class="badge bg-success">Approved</span></td>

</tr>

<tr>

<td>2</td>

<td>Alice</td>

<td>Computing</td>

<td>2026-03-02</td>

<td><span class="badge bg-warning">Pending</span></td>

</tr>

<tr>

<td>3</td>

<td>David</td>

<td>Engineering</td>

<td>2026-03-03</td>

<td><span class="badge bg-danger">Rejected</span></td>

</tr>

</tbody>

</table> -->

</div>

</div>

</div>

</div>

</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>