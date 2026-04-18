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

if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 2) {
    echo "<script>
        alert('Access denied! Only Student allowed.');
        window.location='login.php';
    </script>";
    exit();
}

if (isset($_SESSION['userid'])) {

    $userid = $_SESSION['userid'];
    $qry = mysqli_query($connection, "SELECT * FROM tbluser
                                        where userid = '$userid'");
    $row = mysqli_fetch_array($qry);
    $username = $row["username"];
    $email = $row["email"];
    $password_hash = $row["password_hash"];
}
if (isset($_POST['btnsubmit'])) {

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // 1️⃣ CHECK CURRENT PASSWORD
    if (!password_verify($current, $row['password_hash'])) {
        echo "<script>alert('Current password is incorrect');
        window.location='student_edit.php';
        </script>";
        exit();
    }

    // 2️⃣ CHECK MATCH
    if ($new != $confirm) {
        echo "<script>alert('New passwords do not match');
            window.location='student_edit.php';
        </script>";
        exit();
    }

    // 3️⃣ VALIDATION
    if (strlen($new) < 6) {
        echo "<script>alert('Password must be at least 6 characters');
        window.location='student_edit.php';
        </script>";
        exit();
    }

    // 4️⃣ HASH PASSWORD
    $hashed = password_hash($new, PASSWORD_DEFAULT);

    // 5️⃣ UPDATE
    $query = mysqli_query($connection, "
        UPDATE tbluser 
        SET password_hash = '$hashed'
        WHERE userid = '$userid'
    ");

    if ($query) {
        echo "<script>
            alert('Password updated successfully');
            window.location='student_dashboard.php';
        </script>";
    } else {
        echo "<script>alert('Update failed');</script>";
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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

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

            <div class="col-lg-10 col-md-9 p-4">

                <h3 class="mb-4">User Management</h3>

                <div class="row">

                    <!-- ADD ACADEMIC YEAR FORM -->

                    <div class="col-lg-12 col-md-12 mb-4">

                        <div class="card p-3">

                            <h5 class="mb-3">Update Profile</h5>

                            <form method="POST" action="#">

                                <div class="mb-3">

                                    <label>User</label>

                                    <input type="text" name="txtusername" class="form-control" value="<?php echo $username;?>" readonly>

                                </div>

                                <div class="mb-3">

                                    <label>Email</label>

                                    <input type="email" name="txtemail" class="form-control" value="<?php echo $email;?>" readonly>

                                </div>

                                <div class="mb-3">
                                    <label>Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
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

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>