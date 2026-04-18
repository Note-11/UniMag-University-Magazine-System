<?php
session_start();
require_once("include/connect.php");
if (isset($_POST['btnlogin'])) {
    $email = $_POST['txtemail'];
    $password = $_POST['txtpassword'];

    $qry = mysqli_query($connection, "SELECT * FROM tbluser WHERE email='$email' AND password_hash='$password'");
    $count = mysqli_num_rows($qry);
    $row = mysqli_fetch_array($qry);
    if ($count > 0) {
        $_SESSION['adminid']=$row['adminid'];
        $_SESSION['username']=$row['username'];
        echo '<script type = "text/javascript">
        alert("LOGIN SUCCESS!");
        window.location.href = "admin_dashboard.php";
                </script>';
    } else {
        echo '<script type = "text/javascript">
        alert("FAIL !!!");
        window.location.href = "adminlogin.php";
                </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>UniMag - Secure Login</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/login_style.css">

</head>

<body>

<div class="container d-flex align-items-center justify-content-center vh-100">

<div class="row shadow-lg login-card overflow-hidden">

<!-- LEFT PANEL -->
<div class="col-md-6 system-panel p-5 d-flex flex-column justify-content-center">

<h2 class="mb-4"><i class="fa-solid fa-shield-halved"></i> UniMag System</h2>

<p>
This system provides a <strong>Secure Role-Based Access Control</strong> for university magazine management.
</p>

<ul>
<li>Administrator Management</li>
<li>Faculty Access</li>
<li>Student Submissions</li>
<li>Guest Report Viewing</li>
</ul>

<p class="mt-4">
Only authorized users can access the system dashboard.
</p>

</div>


<!-- RIGHT PANEL -->
<div class="col-md-6 bg-white p-5">

<h3 class="text-center mb-4">Student Login</h3>

<form method="POST">

<div class="mb-3">
<label>Email</label>
<input type="email" name="txtemail" class="form-control" placeholder="Enter Email" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="txtpassword" class="form-control" placeholder="Enter Password" required>
</div>

<div class="form-check mb-3">
<input class="form-check-input" type="checkbox">
<label class="form-check-label">Remember Me</label>
</div>

<button type="submit" name="btnlogin" class="btn btn-primary w-100">
<i class="fa fa-sign-in-alt"></i> Login
</button>

<hr>

<div class="text-center">
<a href="adminreg.php">Create an Account</a>
</div>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>