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

    //prevent SQL injection by escaping special characters in the input
    $academicyear = mysqli_real_escape_string($connection,$_POST['txtacademicyear']);
    $submission = mysqli_real_escape_string($connection,$_POST['txtsubmission']); // start date
    $final = mysqli_real_escape_string($connection,$_POST['txtfinal']); // end date

    // ✅ VALIDATION
    if ($final < $submission) {
        echo "<script>alert('End date cannot be earlier than Start date');</script>";
    } else {
        $check = mysqli_query($connection, "
        SELECT * FROM tblacademicyear WHERE yearname='$academicyear'");

        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Academic year already exists');
                window.location='academic_year_register.php';
            </script>";
        } else {
            $qry = mysqli_query($connection, "
            INSERT INTO tblacademicyear 
            (yearname, submission_closure_date, final_closure_date) 
            VALUES ('$academicyear','$submission','$final')
            ");

            if ($qry) {
                echo "<script>alert('Academic Year Added Successfully');</script>";
            } else {
                echo "<script>alert('Error inserting data');</script>";
            }
        }


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

                <h3 class="mb-4">Academic Year Management</h3>

                <div class="row">

                    <!-- ADD ACADEMIC YEAR FORM -->

                    <div class="col-lg-4 col-md-12 mb-4">

                        <div class="card p-3">

                            <h5 class="mb-3">Add Academic Year</h5>

                            <form method="POST" action="#">

                                <div class="mb-3">

                                    <label>Academic Year</label>

                                    <input type="text" name="txtacademicyear" class="form-control"
                                        placeholder="Enter Academic Year (Spring 2026)" required>

                                </div>

                                <div class="mb-3">

                                    <label>Start Date</label>

                                    <input type="date" name="txtsubmission" class="form-control" required>

                                </div>

                                <div class="mb-3">

                                    <label>End Date</label>

                                    <input type="date" name="txtfinal" class="form-control" required>

                                </div>

                                <button type="submit" name="btnsubmit" class="btn btn-primary w-100">

                                    <i class="fa fa-save"></i> Save Academic Year

                                </button>

                            </form>

                        </div>

                    </div>


                    <!-- ACADEMIC YEAR TABLE -->

                    <div class="col-lg-8 col-md-12">

                        <div class="card p-3">

                            <h5 class="mb-3">Academic Year List</h5>

                            <div class="table-responsive">

                                <table class="table table-striped">

                                    <thead>

                                        <tr>

                                            <th>ID</th>
                                            <th>Academic Year</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Action</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                            <?php
                                            $qry = mysqli_query($connection, "SELECT * FROM tblacademicyear");
                                            $count = mysqli_num_rows($qry);


                                            for ($i = 0; $i < $count; $i++) {
                                                $row = mysqli_fetch_array($qry);
                                                ?>
                                            <tr>
                                                <td><?php echo $row["academicyearid"]; ?></td>
                                                <td><?php echo $row["yearname"]; ?></td>
                                                <td><?php echo $row["submission_closure_date"]; ?></td>
                                                <td><?php echo $row["final_closure_date"]; ?></td>
                                                <td>
                                                    <a href="academic_year_update.php?id=<?php echo $row['academicyearid']; ?>"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a href="academic_year_delete.php?id=<?php echo $row['academicyearid']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this record?')">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                            }
                                            ?>

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
