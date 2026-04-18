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
        alert('Invalid Academic Year ID');
        window.location='academic_year_register.php';
    </script>";
    exit();
}

$academicyearid = intval($_GET['id']);
$yearname = "";
$submission_closure_date = "";
$final_closure_date = "";

// Get existing academic year data
$qry = mysqli_query($connection, "
    SELECT * FROM tblacademicyear 
    WHERE academicyearid='$academicyearid'
");

if (mysqli_num_rows($qry) > 0) {
    $row = mysqli_fetch_array($qry);
    $yearname = $row['yearname'];
    $submission_closure_date = $row['submission_closure_date'];
    $final_closure_date = $row['final_closure_date'];
} else {
    echo "<script>
        alert('Academic Year not found');
        window.location='academic_year_register.php';
    </script>";
    exit();
}

// Update academic year
if (isset($_POST['btnupdate'])) {
    $yearname = mysqli_real_escape_string($connection, $_POST['txtacademicyear']);
    $submission_closure_date = mysqli_real_escape_string($connection, $_POST['txtsubmission']);
    $final_closure_date = mysqli_real_escape_string($connection, $_POST['txtfinal']);

    // Restrict duplicate academic year name except current record
    $check = mysqli_query($connection, "
        SELECT * FROM tblacademicyear
        WHERE yearname = '$yearname'
        AND academicyearid != '$academicyearid'
    ");

    if (mysqli_num_rows($check) > 0) {
        echo "<script>
            alert('Academic Year already exists!');
            window.location='academic_year_update.php?id=$academicyearid';
        </script>";
        exit();
    }

    // Date validation
    if ($final_closure_date < $submission_closure_date) {
        echo "<script>
            alert('End date cannot be earlier than Start date.');
            window.location='academic_year_update.php?id=$academicyearid';
        </script>";
        exit();
    }

    $query = mysqli_query($connection, "
        UPDATE tblacademicyear
        SET yearname='$yearname',
            submission_closure_date='$submission_closure_date',
            final_closure_date='$final_closure_date'
        WHERE academicyearid='$academicyearid'
    ");

    if ($query) {
        echo "<script>
            alert('Academic Year updated successfully!');
            window.location='academic_year_register.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Update failed!');
            window.location='academic_year_update.php?id=$academicyearid';
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
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-lg-10 col-md-9 p-4">
            <h3 class="mb-4">Academic Year Management</h3>

            <div class="row">
                <div class="col-lg-12 col-md-12 mb-4">
                    <div class="card p-3">
                        <h5 class="mb-3">Update Academic Year</h5>

                        <form method="POST" action="">
                            <input type="hidden" name="id" value="<?php echo $academicyearid; ?>">

                            <div class="mb-3">
                                <label>Academic Year</label>
                                <input type="text" name="txtacademicyear" class="form-control"
                                       value="<?php echo htmlspecialchars($yearname); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Start Date</label>
                                <input type="date" name="txtsubmission" class="form-control"
                                       value="<?php echo htmlspecialchars($submission_closure_date); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>End Date</label>
                                <input type="date" name="txtfinal" class="form-control"
                                       value="<?php echo htmlspecialchars($final_closure_date); ?>" required>
                            </div>

                            <button type="submit" name="btnupdate" class="btn btn-primary w-100">
                                <i class="fa fa-save"></i> Update Academic Year
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
