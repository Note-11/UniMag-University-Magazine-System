<?php
session_start();
require_once("include/connect.php");

// 🔒 ONLY MARKETING MANAGER (roleid = 4)
if (!isset($_SESSION['userid']) || $_SESSION['roleid'] != 4) {
    echo "<script>alert('Access denied'); window.location='login.php';</script>";
    exit();
}

// 🔥 TOTAL CONTRIBUTIONS
$total_res = mysqli_query($connection, "
SELECT COUNT(*) as total FROM tblcontribution
");
$total_data = mysqli_fetch_assoc($total_res);
$total_contributions = $total_data['total'];

// 🔥 CONTRIBUTIONS PER FACULTY
$query = mysqli_query($connection, "
SELECT f.facultyname, COUNT(c.contributionid) as total
FROM tblfaculty f
LEFT JOIN tbluser u ON f.facultyid = u.facultyid
LEFT JOIN tblcontribution c ON u.userid = c.studentid
GROUP BY f.facultyid
");

// STORE DATA FOR CHART
$labels = [];
$data = [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="mb-3">
        <a href="contribution_list_selected.php" class="btn btn-secondary">
            ⬅ Back to Dashboard
        </a>
    </div>

    <h3 class="mb-4">📊 Contributions Report by Faculty</h3>

    <!-- SUMMARY -->
    <div class="alert alert-info">
        Total Contributions: <b><?php echo $total_contributions; ?></b>
    </div>

    <!-- TABLE -->
    <div class="card p-3 mb-4">

        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Faculty</th>
                    <th>Total Contributions</th>
                    <th>Percentage (%)</th>
                </tr>
            </thead>

            <tbody>

            <?php while ($row = mysqli_fetch_assoc($query)) {

                $percentage = $total_contributions > 0 
                    ? round(($row['total'] / $total_contributions) * 100, 2) 
                    : 0;

                // store for chart
                $labels[] = $row['facultyname'];
                $data[] = $row['total'];
            ?>

                <tr>
                    <td><?php echo $row['facultyname']; ?></td>
                    <td><?php echo $row['total']; ?></td>
                    <td><?php echo $percentage; ?>%</td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

    </div>

    <!-- CHART -->
    <div class="card p-4">
        <canvas id="facultyChart"></canvas>
    </div>

</div>

<!-- CHART SCRIPT -->
<script>
const ctx = document.getElementById('facultyChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Number of Contributions',
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