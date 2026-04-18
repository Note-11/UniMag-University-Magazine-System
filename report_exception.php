<?php
session_start();
require_once("include/connect.php");

// 🔒 ONLY MARKETING COORDINATOR
if (!isset($_SESSION['userid']) || $_SESSION['roleid'] != 3) {
    echo "<script>alert('Access denied'); window.location='login.php';</script>";
    exit();
}

// 🔥 TOTAL CONTRIBUTIONS
$total_res = mysqli_query($connection, "
SELECT COUNT(*) as total FROM tblcontribution
");
$total_data = mysqli_fetch_assoc($total_res);
$total_contributions = $total_data['total'];

// 🔥 EXCEPTION REPORT (NO COMMENT AFTER 14 DAYS)
$exception_query = mysqli_query($connection, "
SELECT c.contributionid, c.title, c.submission_date,
       s.username, f.facultyname,
       DATEDIFF(CURDATE(), c.submission_date) as days_passed
FROM tblcontribution c
JOIN tbluser s ON c.studentid = s.userid
JOIN tblfaculty f ON s.facultyid = f.facultyid
LEFT JOIN tblcomment cm ON c.contributionid = cm.contributionid
WHERE cm.commentid IS NULL
AND DATEDIFF(CURDATE(), c.submission_date) > 14
");

// 🔥 COUNT EXCEPTIONS
$exception_count = mysqli_num_rows($exception_query);

// 🔥 PERCENTAGE
$percentage = $total_contributions > 0 
    ? round(($exception_count / $total_contributions) * 100, 2)
    : 0;

// 🔥 GROUP BY FACULTY (FOR CHART)
$chart_query = mysqli_query($connection, "
SELECT f.facultyname, COUNT(c.contributionid) as total
FROM tblcontribution c
JOIN tbluser s ON c.studentid = s.userid
JOIN tblfaculty f ON s.facultyid = f.facultyid
LEFT JOIN tblcomment cm ON c.contributionid = cm.contributionid
WHERE cm.commentid IS NULL
AND DATEDIFF(CURDATE(), c.submission_date) > 14
GROUP BY f.facultyid
");

$labels = [];
$data = [];

while ($row = mysqli_fetch_assoc($chart_query)) {
    $labels[] = $row['facultyname'];
    $data[] = $row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exception Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">
<div class="container mt-5">
     <div class="mb-3">
        <a href="contribution_list.php" class="btn btn-secondary">
            ⬅ Back to Dashboard
        </a>
    </div>

    <h3 class="mb-4">⚠ Exception Report (No Comments after 14 Days)</h3>

    <!-- SUMMARY -->
    <div class="alert alert-warning">
        Total Contributions: <b><?php echo $total_contributions; ?></b><br>
        Exceptions: <b><?php echo $exception_count; ?></b><br>
        Percentage: <b><?php echo $percentage; ?>%</b>
    </div>

    <!-- TABLE -->
    <div class="card p-3 mb-4">

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Student</th>
                    <th>Faculty</th>
                    <th>Submitted Date</th>
                    <th>Days Passed</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($exception_count == 0) { ?>
                <tr>
                    <td colspan="5" class="text-center">No exceptions found 🎉</td>
                </tr>
            <?php } ?>

            <?php while ($row = mysqli_fetch_assoc($exception_query)) { ?>
                <tr>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['facultyname']; ?></td>
                    <td><?php echo $row['submission_date']; ?></td>
                    <td><?php echo $row['days_passed']; ?> days</td>
                </tr>
            <?php } ?>

            </tbody>
        </table>

    </div>

    <!-- CHART -->
    <div class="card p-4">
        <h5>📊 Exceptions by Faculty</h5>
        <canvas id="exceptionChart"></canvas>
    </div>

</div>

<!-- CHART SCRIPT -->
<script>
const ctx = document.getElementById('exceptionChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Exception Count',
            data: <?php echo json_encode($data); ?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>