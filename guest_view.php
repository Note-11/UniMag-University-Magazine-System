<?php
session_start();
require_once("include/connect.php");

// 🔒 ONLY GUEST
if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 5) {
    echo "<script>alert('Access denied'); window.location='login.php';</script>";
    exit();
}

$facultyid = $_SESSION['facultyid'];

// FETCH SELECTED CONTRIBUTIONS FROM SAME FACULTY
$query = mysqli_query($connection, "
SELECT c.*, 
       s.username as studentname,
       f.facultyname,
       cat.categoryname
FROM tblcontribution c
JOIN tbluser s ON c.studentid = s.userid
JOIN tblfaculty f ON s.facultyid = f.facultyid
LEFT JOIN tblcategory cat ON c.categoryid = cat.categoryid
WHERE c.status = 'selected'   -- ✅ ONLY SELECTED
AND s.facultyid = '$facultyid' -- ✅ ONLY SAME FACULTY
ORDER BY c.contributionid DESC
");

// $query = mysqli_query($connection, "
// SELECT c.*, s.username, f.facultyname
// FROM tblcontribution c
// JOIN tbluser s ON c.studentid = s.userid
// JOIN tblfaculty f ON s.facultyid = f.facultyid
// WHERE c.status = 'selected'
// AND s.facultyid = '$facultyid'
// ");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guest View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">
        <?php
            // Get faculty name separately (SAFE)
            $faculty = mysqli_fetch_assoc(mysqli_query($connection, "
                SELECT facultyname FROM tblfaculty WHERE facultyid = '$facultyid'
            "));
            ?>

            <h3>📖 <?php echo $faculty['facultyname']; ?> Faculty Contributions (Guest View)</h3>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        
    </div>

    <?php 
        if (mysqli_num_rows($query) == 0) {
        echo "<div class='alert alert-warning'>No selected contributions available.</div>";
    }
    while($row = mysqli_fetch_array($query)) { ?>

        

        <div class="card mb-3 p-3">
            <span class="badge bg-success">Selected</span>
            

            <h5><?php echo $row['title']; ?></h5>

            <small class="text-muted">
                👤 <?php echo $row['studentname']; ?> |
                📂 <?php echo $row['categoryname']; ?>
            </small>

            <p class="mt-2"><?php echo $row['description']; ?></p>

            <!-- FILE PREVIEW -->
            <?php
            $files = [$row['filepath1'], $row['filepath2'], $row['filepath3']];
            foreach ($files as $file) {
                if ($file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                        echo "<img src='upload/$file' width='120' class='me-2'>";
                    }
                }
            }
            ?>

            <br><br>

            <!-- DOWNLOAD -->
            <?php
            $fileLabels = ["File 1", "File 2", "File 3"];

            foreach ($files as $index => $file) {
                if ($file) {
                    echo "
                    <div class='mb-2'>
                        <strong>{$fileLabels[$index]}:</strong> 
                        <span class='text-muted'>$file</span>
                        <br>
                        <a href='upload/$file' download class='btn btn-sm btn-outline-primary mt-1'>
                            ⬇ Download {$fileLabels[$index]}
                        </a>
                    </div>
                    ";
                }
            }
            ?>

        </div>

    <?php } ?>

</div>

</body>
</html>