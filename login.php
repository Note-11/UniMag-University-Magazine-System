<?php
session_start();
require_once("include/connect.php");

if (isset($_POST['btnlogin'])) {

    $email = $_POST['txtemail'];
    $password = $_POST['txtpassword'];

    // ✅ GET USER BY EMAIL ONLY
    $qry = mysqli_query($connection, "
        SELECT u.*, r.rolename 
        FROM tbluser u
        JOIN tblrole r ON r.roleid = u.roleid
        WHERE u.email = '$email'
    ");

    $user = mysqli_fetch_assoc($qry);

    // ✅ VERIFY HASH PASSWORD
    if ($user && password_verify($password, $user['password_hash'])) {

        $_SESSION['userid'] = $user['userid'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['roleid'] = $user['roleid'];
        $_SESSION['facultyid'] = $user['facultyid'];

        // ✅ ROLE BASED REDIRECT
        if ($user['roleid'] == "1") {
            header("Location: student_contribution.php");
        }
        else if ($user['rolename'] == "Student") {
            header("Location: student_dashboard.php");
        }
        else if ($user['rolename'] == "Marketing Coordinator") {
            header("Location: contribution_list.php");
        }
        else if ($user['rolename'] == "Marketing Manager") {
            header("Location: contribution_list_selected.php");
        }
        else if ($user['rolename'] == "Guest") {
            header("Location: guest_view.php");
        }

        exit();

    } else {
        echo "<script>
            alert('Invalid Email or Password');
            window.location='login.php';
        </script>";
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

<h3 class="text-center mb-4">Login</h3>

<form method="POST">

<div class="mb-3">
<label>Email</label>
<!-- <input type="email" name="txtemail" class="form-control" placeholder="Enter Email" required> -->
 <input type="email" name="txtemail" class="form-control"
       placeholder="Enter Email" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="txtpassword" class="form-control" placeholder="Enter Password" required>
</div>


<button type="submit" name="btnlogin" class="btn btn-primary w-100">
<i class="fa fa-sign-in-alt"></i> Login
</button>

<!-- <hr> -->

<!-- <div class="text-center">
<a href="student_register.php">Create an Account</a>
</div> -->

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>