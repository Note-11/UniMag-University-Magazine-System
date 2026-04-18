<?php
session_start();
require_once("include/connect.php");

// 🔒 SECURITY: Only Coordinator
if (!isset($_SESSION['userid']) || $_SESSION['roleid'] != 3) {
    echo "<script>alert('Access denied'); window.location='login.php';</script>";
    exit();
}

$userid = $_SESSION['userid'];


// Get coordinator faculty
$res = mysqli_query($connection, "
    SELECT facultyid FROM tbluser WHERE userid = '$userid'
");
$user = mysqli_fetch_assoc($res);
$facultyid = $user['facultyid'];


// 🔍 SEARCH
$search = $_GET['search'] ?? '';

$where = "WHERE roleid = 2 AND facultyid = '$facultyid'";

if (!empty($search)) {
    $where .= " AND username LIKE '%$search%'";
}

// FETCH STUDENTS
$query = mysqli_query($connection, "
SELECT u.*, f.facultyname,
       COUNT(c.contributionid) as total_contributions
FROM tbluser u
JOIN tblfaculty f ON u.facultyid = f.facultyid
LEFT JOIN tblcontribution c ON u.userid = c.studentid
WHERE u.roleid = 2 
AND u.facultyid = '$facultyid'
" . (!empty($search) ? " AND u.username LIKE '%$search%'" : "") . "
GROUP BY u.userid
");
if (!$query) {
    die("Query Error: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student List</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/coordinator.css">

</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR -->

            <div class="col-lg-2 col-md-3 sidebar p-3">

                <h4 class="text-center mb-4">Coordinator Panel</h4>

                <a href="contribution_list.php"><i class="fa fa-home"></i> Dashboard</a>

                <a href="student_list.php"><i class="fa fa-users"></i>Students</a>

                <a href="coordinator_guest_view.php"><i class="fa fa-user-friends"></i> View Guests</a>

                <a href="exception_report.php"><i class="fa fa-exclamation-triangle"></i> Exception Report</a>

                <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>

            </div>

            <!-- MAIN CONTENT -->

            <div class="col-lg-10 col-md-9">

                <!-- NAVBAR -->

                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">

                    <div class="container-fluid">

                        <button class="btn btn-outline-primary d-md-none" data-bs-toggle="collapse"
                            data-bs-target="#sidebarMenu">
                            ☰
                        </button>

                        <span class="navbar-brand">Dashboard</span>

                        <div class="ms-auto">

                            <span class="me-3">Welcome <?php
                            // session_start();
                            echo $username = $_SESSION['username'];
                            ?></span>

                            <img src="https://i.pravatar.cc/40" class="rounded-circle">

                            <a href="notifications.php" class="position-relative">
                                🔔
                                <?php
                                $noti = mysqli_fetch_assoc(mysqli_query($connection, "
                                        SELECT COUNT(*) as total 
                                        FROM tblnotification_log 
                                        WHERE sent_to = '$userid' AND status = 'unread'
                                    "));
                                ?>

                                <span class="badge bg-danger">
                                    <?php echo $noti['total']; ?>
                                </span>
                            </a>
                        </div>

                    </div>

                </nav>

                <h3 class="mb-4">🎓 Students in Your Faculty</h3>

                <!-- SEARCH -->
                <form method="GET" class="row mb-3">

                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control" placeholder="Search student by name"
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="col-md-4">
                        <button class="btn btn-primary w-100">
                            🔍 Search
                        </button>
                    </div>

                </form>

                <!-- TABLE -->
                <div class="card p-3">

                    <table class="table table-bordered table-hover">

                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Faculty</th>
                                <th>Created Date</th>
                                <th>Total Contributions</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php $i = 1;
                            while ($row = mysqli_fetch_array($query)) { ?>

                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['facultyname']; ?></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                    <td><?php echo $row['total_contributions']; ?></td>
                                    <td><a href="student_contribution_list.php?id=<?php echo $row['userid']; ?>"
                                            class="btn btn-sm btn-info">
                                            View Contributions
                                        </a></td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>

</body>

</html>