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
        alert('Invalid Category ID');
        window.location='category_register.php';
    </script>";
    exit();
}

$categoryid = intval($_GET['id']);
$academicyearid = "";
$categoryname = "";
$categorystartdate = "";
$categoryclosuredate = "";

$qry = mysqli_query($connection, "
    SELECT * FROM tblcategory
    WHERE categoryid='$categoryid'
");

if (mysqli_num_rows($qry) > 0) {
    $row = mysqli_fetch_array($qry);
    $academicyearid = $row['academicyearid'];
    $categoryname = $row['categoryname'];
    $categorystartdate = $row['categorystartdate'];
    $categoryclosuredate = $row['categoryclosuredate'];
} else {
    echo "<script>
        alert('Category not found');
        window.location='category_register.php';
    </script>";
    exit();
}

// Update category
if (isset($_POST['btnsubmit'])) {
    $academicyearid = mysqli_real_escape_string($connection, $_POST['txtacademicyearid']);
    $categoryname = mysqli_real_escape_string($connection, $_POST['txtcategoryname']);
    $categorystartdate = mysqli_real_escape_string($connection, $_POST['txtstartdate']);
    $categoryclosuredate = mysqli_real_escape_string($connection, $_POST['txtenddate']);

    // Duplicate name check
    $check = mysqli_query($connection, "
        SELECT * FROM tblcategory
        WHERE categoryname = '$categoryname'
        AND categoryid != '$categoryid'
    ");

    if (mysqli_num_rows($check) > 0) {
        echo "<script>
            alert('Category name already exists!');
            window.location='category_update.php?id=$categoryid';
        </script>";
        exit();
    }

    // Date validation
    if ($categoryclosuredate < $categorystartdate) {
        echo "<script>
            alert('End date cannot be earlier than Start date.');
            window.location='category_update.php?id=$categoryid';
        </script>";
        exit();
    }

    // Academic year boundary validation
    $checkYear = mysqli_query($connection, "
        SELECT submission_closure_date, final_closure_date 
        FROM tblacademicyear 
        WHERE academicyearid = '$academicyearid'
    ");
    $year = mysqli_fetch_assoc($checkYear);

    if ($year && ($categorystartdate < $year['submission_closure_date'] || $categoryclosuredate > $year['final_closure_date'])) {
        echo "<script>
            alert('Category dates must be within Academic Year range');
            window.location='category_update.php?id=$categoryid';
        </script>";
        exit();
    }

    // Update query
    $query = mysqli_query($connection, "
        UPDATE tblcategory
        SET academicyearid='$academicyearid',
            categoryname='$categoryname',
            categorystartdate='$categorystartdate',
            categoryclosuredate='$categoryclosuredate'
        WHERE categoryid='$categoryid'
    ");

    if ($query) {
        echo "<script>
            alert('Category updated successfully!');
            window.location='category_register.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Update failed!');
            window.location='category_update.php?id=$categoryid';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3 sidebar p-3">
            <h4 class="text-center mb-4">Admin Panel</h4>
            <a href="student_contribution.php"><i class="fa fa-home"></i> Dashboard</a>
            <a href="academic_year_register.php"><i class="fa fa-user-tie"></i> Manage Academic Year</a>
            <a href="category_register.php"><i class="fa fa-user-tie"></i> Manage Category</a>
            <a href="register.php"><i class="fa fa-users"></i> Manage Users</a>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main content -->
        <div class="col-lg-10 col-md-9 p-4">
            <h3 class="mb-4">Category Management</h3>

            <div class="row">
                <div class="col-lg-12 col-md-12 mb-4">
                    <div class="card p-3">
                        <h5 class="mb-3">Update Category</h5>

                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo $categoryid; ?>">

                            <!-- Category Name first -->
                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" name="txtcategoryname" class="form-control"
                                       value="<?php echo htmlspecialchars($categoryname); ?>" required>
                            </div>

                            <!-- Academic Year next -->
                            <div class="mb-3">
                                <label class="form-label">Academic Year</label>
                                <select name="txtacademicyearid" class="form-control" required>
                                    <option value="">Select Academic Year</option>
                                    <?php
                                    $yearqry = mysqli_query($connection, "SELECT * FROM tblacademicyear ORDER BY yearname ASC");
                                    while ($yearrow = mysqli_fetch_array($yearqry)) {
                                        $selected = ($academicyearid == $yearrow['academicyearid']) ? "selected" : "";
                                        echo "<option value='" . $yearrow['academicyearid'] . "' $selected>" 
                                             . htmlspecialchars($yearrow['yearname']) 
                                             . " (" . $yearrow['submission_closure_date'] 
                                             . " to " . $yearrow['final_closure_date'] . ")</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Start Date -->
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="txtstartdate" class="form-control"
                                       value="<?php echo htmlspecialchars($categorystartdate); ?>" required>
                            </div>

                            <!-- End Date -->
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="txtenddate" class="form-control"
                                       value="<?php echo htmlspecialchars($categoryclosuredate); ?>" required>
                            </div>

                            <button type="submit" name="btnsubmit" class="btn btn-primary w-100">
                                <i class="fa fa-save"></i> Update Category
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
