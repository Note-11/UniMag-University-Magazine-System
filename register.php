<?php
session_start();
require_once("include/autoid.php");
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

if (isset($_POST['btnsubmit'])) {

    $username = $_POST['txtusername'];
    $facultyid = $_POST['cbofacultyid'];
    $roleid = $_POST['cboroleid'];
    $email = $_POST['txtemail'];
    $password = password_hash($_POST['txtpassword'], PASSWORD_DEFAULT); // 🔥 HASHED
    $created_at = date("Y-m-d");

    // 🔥 CHECK DUPLICATE USERNAME OR EMAIL
    $check = mysqli_query($connection, "
    SELECT * FROM tbluser 
    WHERE username='$username' OR email='$email'
");

    if (mysqli_num_rows($check) > 0) {

        echo "<script>
        alert('Username or Email already exists');
        window.location.href='register.php';
        </script>";
        exit();  
    }
    $qry = mysqli_query($connection, "
        INSERT INTO tbluser
        (facultyid,roleid,username,email,password_hash,created_at)
        VALUES
        ('$facultyid','$roleid','$username','$email','$password','$created_at')
    ");
    if ($qry) {
        // If this is a guest registration (adjust roleid value for guest)
        if ($roleid == 5) {
            // Get faculty name
            $facultyRow = mysqli_fetch_assoc(mysqli_query(
                $connection,
                "SELECT facultyname FROM tblfaculty WHERE facultyid = '$facultyid'"
            ));

            // Find coordinator for this faculty
            $coordinator = mysqli_fetch_assoc(mysqli_query(
                $connection,
                "SELECT email FROM tbluser WHERE roleid = 3 AND facultyid = '$facultyid' LIMIT 1"
            ));

            if ($coordinator) {
                require_once("backend/mailer.php");
                $date = date("Y-m-d");
                $message = "
                <h3>New Guest Registration</h3>
                <p><b>Name:</b> $username</p>
                <p><b>Email:</b> $email</p>
                <p><b>Faculty:</b> {$facultyRow['facultyname']}</p>
                <p><b>Role:</b> Guest</p>
                <p><b>Registered At:</b> $date</p>
            ";
                sendEmail($coordinator['email'], "New Guest Registration", $message);
            }
        }

        echo '<script type="text/javascript">
        alert("Submission Success");
        window.location.href="#";
    </script>';
    } else {
        echo '<script type="text/javascript">
        alert("Submission Fail");
    </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin Dashboard</title>

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

                <h4 class="text-center mb-4">Admin Panel</h4>

                <a href="student_contribution.php"><i class="fa fa-home"></i> Dashboard</a>

                <a href="academic_year_register.php"><i class="fa fa-user-tie"></i> Manage Academic Year</a>

                <a href="category_register.php"><i class="fa fa-user-tie"></i> Manage Category</a>

                <a href="register.php"><i class="fa fa-users"></i> Manage Users</a>

                <!-- <a href="student_contribution.php"><i class="fa fa-folder"></i> Student Contribution</a> -->

                <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>

            </div>


            <!-- MAIN CONTENT -->

            <div class="col-lg-10 col-md-9 p-4">

                <h3 class="mb-4">User Management</h3>

                <div class="row">

                    <!-- ADD ACADEMIC YEAR FORM -->

                    <div class="col-lg-4 col-md-12 mb-4">

                        <div class="card p-3">

                            <h5 class="mb-3">Add User</h5>

                            <form method="POST" action="#">

                                <div class="mb-3">

                                    <label>User</label>

                                    <input type="text" name="txtusername" class="form-control"
                                        placeholder="Enter Student Name" required>

                                </div>

                                <div class="mb-3">

                                    <label>Choose Faculty</label>

                                    <select class="form-control" name="cbofacultyid" required>

                                        <option value="">Choose Faculty</option>

                                        <?php
                                        $query = "SELECT * FROM tblfaculty";
                                        $ret = mysqli_query($connection, $query);

                                        while ($arr = mysqli_fetch_array($ret)) {
                                            $facultyid = $arr['facultyid'];

                                            echo "<option value='$facultyid'>" . $arr['facultyid'] . " - " . $arr['facultyname'] . "</option>";
                                        }
                                        ?>

                                    </select>

                                </div>

                                <div class="mb-3">

                                    <label>Choose Role</label>

                                    <select class="form-control" name="cboroleid" required>

                                        <option value="">Choose Role</option>

                                        <?php
                                        $query = "SELECT * FROM tblrole ORDER BY roleid ASC";
                                        $ret = mysqli_query($connection, $query);

                                        while ($arr = mysqli_fetch_array($ret)) {
                                            $roleid = $arr['roleid'];

                                            echo "<option value='$roleid'>" . $arr['roleid'] . " - " . $arr['rolename'] . "</option>";
                                        }
                                        ?>

                                    </select>

                                </div>

                                <div class="mb-3">

                                    <label>Email</label>

                                    <input type="email" name="txtemail" class="form-control"
                                        placeholder="Enter Your Email" required>

                                </div>

                                <div class="mb-3">

                                    <label>Password</label>

                                    <input type="password" class="form-control" name="txtpassword" id="Password"
                                        placeholder="Password" required>

                                </div>

                                <button type="submit" name="btnsubmit" class="btn btn-primary w-100">

                                    <i class="fa fa-save"></i> Register User

                                </button>

                            </form>

                        </div>

                    </div>


                    <!-- ACADEMIC YEAR TABLE -->

                    <div class="col-lg-8 col-md-12">

                        <div class="card p-3">

                            <h5 class="mb-3">User List</h5>

                            <div class="table-responsive">

                                <table class="table table-striped">

                                    <thead>

                                        <tr>

                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Faculty</th>
                                            <th>Role</th>
                                            <th>Email</th>
                                            <th>CreatedAt</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>

                                            <?php
                                            $qry = mysqli_query($connection, "SELECT * FROM tbluser u, tblrole r, tblfaculty f 
                                                                                where r.roleid=u.roleid
                                                                                and f.facultyid=u.facultyid");
                                            $count = mysqli_num_rows($qry);


                                            for ($i = 0; $i < $count; $i++) {
                                                $row = mysqli_fetch_array($qry);
                                                ?>
                                            <tr>
                                                <td><?php echo $row["userid"]; ?></td>
                                                <td><?php echo $row["username"]; ?></td>
                                                <td><?php echo $row["facultyname"]; ?></td>
                                                <td><?php echo $row["rolename"]; ?></td>
                                                <td><?php echo $row["email"]; ?></td>
                                                <td><?php echo $row["created_at"]; ?></td>
                                                <td>
                                                    <?php
                                                    $today = date("Y-m-d");
                                                    // Allow edit/delete
                                                    echo '<a href="user_update.php?id=' . $row['userid'] . '" class="btn btn-sm btn-warning">
            <i class="fa fa-edit"></i>
          </a>';


                                                    // Delete can still be optional; if you want to also prevent delete, wrap it in the same check
    //                                                 echo ' <a href="user_delete.php?id=' . $row['userid'] . '" class="btn btn-sm btn-danger"
    //     onclick="return confirm(\'Are you sure you want to delete this record?\')">
    //     <i class="fa fa-trash"></i>
    //   </a>';
                                                $check = mysqli_query($connection, "
                                                    SELECT * FROM tblcontribution WHERE studentid='".$row['userid']."'
                                                ");

                                                if (mysqli_num_rows($check) > 0) {
                                                    echo '<button class="btn btn-danger btn-sm" disabled>In Use</button>';
                                                } else {
                                                    echo '<a href="user_delete.php?id='.$row['userid'].'" class="btn btn-danger btn-sm">Delete</a>';
                                                }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                            }
                                            ?>

                                        </tr>

                                    </tbody>

                                </table>

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