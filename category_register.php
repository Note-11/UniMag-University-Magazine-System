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

    $categoryname = mysqli_real_escape_string($connection,$_POST['txtcategoryname']);
    $submission = mysqli_real_escape_string($connection,$_POST['txtsubmission']); // start
    $final = mysqli_real_escape_string($connection,$_POST['txtfinal']); // end
    $academicyearid = mysqli_real_escape_string($connection,$_POST['cboacademicyearid']);

    // ✅ VALIDATION
    if ($final < $submission) {
        echo "<script>alert('End date cannot be earlier than Start date');
        window.location='category_register.php';
        </script>";
        return;
    }

    // 🔥 ALSO check within academic year
    $checkYear = mysqli_query($connection, "
        SELECT submission_closure_date, final_closure_date 
        FROM tblacademicyear 
        WHERE academicyearid = '$academicyearid'
    ");
    $year = mysqli_fetch_assoc($checkYear);

    if ($submission < $year['submission_closure_date'] || $final > $year['final_closure_date']) {
        echo "<script>alert('Category dates must be within Academic Year range');
            window.location='category_register.php';
        </script>";
        return;
    }
    $check = mysqli_query($connection, "
    SELECT * FROM tblcategory 
    WHERE categoryname='$categoryname' 
    AND academicyearid='$academicyearid'");

    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Category already exists in this Academic Year');
            window.location='category_register.php';
        </script>";
        return;
    }

    // ✅ INSERT
    $qry = mysqli_query($connection, "
        INSERT INTO tblcategory
        (academicyearid,categoryname,categorystartdate,categoryclosuredate)
        VALUES
        ('$academicyearid','$categoryname','$submission','$final')
    ");

    if ($qry) {
        echo "<script>alert('Category Added Successfully');</script>";
    } else {
        echo "<script>alert('Error adding category');</script>";
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

                <h3 class="mb-4">Category Management</h3>

                <div class="row">

                    <!-- ADD ACADEMIC YEAR FORM -->

                    <div class="col-lg-4 col-md-12 mb-4">

                        <div class="card p-3">

                            <h5 class="mb-3">Add Category</h5>

                            <form method="POST" action="#">

                                <div class="mb-3">

                                    <label for="txtcategoryname">Category</label>

                                    <input type="text" id="txtcategoryname" name="txtcategoryname" class="form-control"
                                        placeholder="Enter Category (Spring 2026)" required>

                                </div>

                                <div class="mb-3">

                                    <label for="cboacademicyearid">Choose Academic Year</label>

                                    <select class="form-control" id="cboacademicyearid" name="cboacademicyearid" required>

                                        <!-- <option value="">Choose Academic Year</option> -->
                                        <option value="">Choose Academic Year</option>
                                        <?php
                                        $query = "SELECT * FROM tblacademicyear";
                                        $ret = mysqli_query($connection, $query);

                                        while ($arr = mysqli_fetch_array($ret)) {
                                            $academicyearid = $arr['academicyearid'];
                                            echo "<option value='$academicyearid' 
                                                    data-start='{$arr['submission_closure_date']}' 
                                                    data-end='{$arr['final_closure_date']}'>
                                                    {$arr['yearname']} ({$arr['submission_closure_date']} to {$arr['final_closure_date']})
                                                </option>";
                                        }
                                        ?>

                                    </select>

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

                                    <i class="fa fa-save"></i> Save Category

                                </button>

                            </form>

                        </div>

                    </div>


                    <!-- ACADEMIC YEAR TABLE -->

                    <div class="col-lg-8 col-md-12">

                        <div class="card p-3">

                            <h5 class="mb-3">Category List</h5>

                            <div class="table-responsive">

                                <table class="table table-striped">

                                    <thead>

                                        <tr>

                                            <th>ID</th>
                                            <th>Category</th>
                                            <th>Academic Year</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Action</th>

                                        </tr>

                                    </thead>

                                    <tbody>
    <?php
    $qry = mysqli_query($connection, "
        SELECT c.*, a.yearname 
        FROM tblcategory c
        INNER JOIN tblacademicyear a ON c.academicyearid = a.academicyearid
    ");
    while ($row = mysqli_fetch_array($qry)) {
        ?>
        <tr>
            <td><?php echo $row["categoryid"]; ?></td>
            <td><?php echo $row["categoryname"]; ?></td>
            <td><?php echo $row["yearname"]; ?></td>
            <td><?php echo $row["categorystartdate"]; ?></td>
            <td><?php echo $row["categoryclosuredate"]; ?></td>
            <td>
                <?php
                $today = date("Y-m-d");

                if ($today <= $row['categoryclosuredate']) {
                    echo '<a href="category_update.php?id=' . $row['categoryid'] . '" class="btn btn-sm btn-warning">
                            <i class="fa fa-edit"></i>
                          </a>';
                } else {
                    echo '<button class="btn btn-sm btn-secondary" disabled>
                            <i class="fa fa-edit"></i> Closed
                          </button>';
                }

                $check = mysqli_query($connection, "
                    SELECT * FROM tblcontribution WHERE categoryid = '".$row['categoryid']."'
                ");

                if (mysqli_num_rows($check) > 0) {
                    echo '<button class="btn btn-danger btn-sm" disabled>In Use</button>';
                } else {
                    echo '<a href="category_delete.php?id='.$row['categoryid'].'" class="btn btn-danger btn-sm">Delete</a>';
                }
                ?>
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
    <script>
        document.querySelector('[name="cboacademicyearid"]').addEventListener('change', function () {

            let selected = this.options[this.selectedIndex];

            let start = selected.getAttribute('data-start');
            let end = selected.getAttribute('data-end');

            document.querySelector('[name="txtsubmission"]').value = start;
            document.querySelector('[name="txtfinal"]').value = end;
        });
    </script>

</body>

</html>