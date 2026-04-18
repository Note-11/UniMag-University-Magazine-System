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

if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 1) {
    echo "<script>
        alert('Access denied! Only Admin allowed.');
        window.location='login.php';
    </script>";
    exit();
}

if (!isset($_GET['id'])) {
    echo "<script>
        alert('Invalid user ID');
        window.location='register.php';
    </script>";
    exit();
}

$userid = intval($_GET['id']);
$username = "";
$email = "";

// Get user data
$qry = mysqli_query($connection, "SELECT * FROM tbluser WHERE userid='$userid'");
if (mysqli_num_rows($qry) > 0) {
    $row = mysqli_fetch_array($qry);
    $username = $row["username"];
    $email = $row["email"];
} else {
    echo "<script>
        alert('User not found');
        window.location='register.php';
    </script>";
    exit();
}

// Update user
if (isset($_POST['btnsubmit'])) {
    $username = mysqli_real_escape_string($connection, $_POST['txtusername']);
    $email = mysqli_real_escape_string($connection, $_POST['txtemail']);
    $password_input = $_POST['txtpassword'];

    // Check duplicate email for other users
    $checkEmail = mysqli_query($connection, "
        SELECT * FROM tbluser
        WHERE email='$email' AND userid != '$userid'
    ");

    if (mysqli_num_rows($checkEmail) > 0) {
        echo "<script>
            alert('Email already exists!');
            window.location='user_update.php?id=$userid';
        </script>";
        exit();
    }

    // Update with password
    if (!empty($password_input)) {
        $password = password_hash($password_input, PASSWORD_DEFAULT);

        $query = mysqli_query($connection, "
            UPDATE tbluser
            SET username='$username',
                email='$email',
                password_hash='$password'
            WHERE userid='$userid'
        ");
    } else {
        // Update without changing password
        $query = mysqli_query($connection, "
            UPDATE tbluser
            SET username='$username',
                email='$email'
            WHERE userid='$userid'
        ");
    }

    if ($query) {
        echo "<script>
            alert('Update SUCCESS!');
            window.location='register.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Update Failed!');
            window.location='user_update.php?id=$userid';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">
      
        <div class="col-lg-2 col-md-3 sidebar p-3">
            <h4 class="text-center mb-4">Admin Panel</h4>

            <a href="student_contribution.php"><i class="fa fa-home"></i> Dashboard</a>
            <a href="academic_year_register.php"><i class="fa fa-user-tie"></i> Manage User</a>
            <a href="category_register.php"><i class="fa fa-user-tie"></i> Manage Category</a>
            <a href="register.php"><i class="fa fa-users"></i> Manage Users</a>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="col-lg-10 col-md-9 p-4">
            <h3 class="mb-4">User Management</h3>

            <div class="row">
                <div class="col-lg-12 col-md-12 mb-4">
                    <div class="card p-3">
                        <h5 class="mb-3">Update User</h5>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label>User</label>
                                <input type="text" name="txtusername" class="form-control"
                                    value="<?php echo htmlspecialchars($username); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="txtemail" class="form-control"
                                    value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" class="form-control" name="txtpassword" id="Password"
                                    placeholder="Leave blank if you do not want to change password">
                            </div>

                            <button type="submit" name="btnsubmit" class="btn btn-primary w-100">
                                <i class="fa fa-save"></i> Update User
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>