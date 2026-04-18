<?php
session_start();
require_once("include/connect.php");

// 🔒 SECURITY: Only coordinator
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

// GET STUDENT ID
$studentid = $_GET['id'] ?? '';

if (!$studentid) {
    echo "<script>alert('Invalid student'); window.location='student_list.php';</script>";
    exit();
}

// 🔒 CHECK same faculty
$check = mysqli_query($connection, "
    SELECT u.*, f.facultyname 
    FROM tbluser u
    JOIN tblfaculty f ON u.facultyid = f.facultyid
    WHERE u.userid = '$studentid' AND u.facultyid = '$facultyid'
");

$student = mysqli_fetch_assoc($check);

if (!$student) {
    echo "<script>alert('Unauthorized access'); window.location='student_list.php';</script>";
    exit();
}

// FETCH CONTRIBUTIONS
$contributions = mysqli_query($connection, "
SELECT c.*, cat.categoryname
FROM tblcontribution c
LEFT JOIN tblcategory cat ON c.categoryid = cat.categoryid
WHERE c.studentid = '$studentid'
ORDER BY c.contributionid DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Contributions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <!-- BACK BUTTON -->
    <a href="student_list.php" class="btn btn-secondary mb-3">⬅ Back</a>

    <!-- STUDENT INFO -->
    <div class="card p-3 mb-4">
        <h4>👤 <?php echo $student['username']; ?></h4>
        <p class="mb-1">📧 <?php echo $student['email']; ?></p>
        <p class="mb-0">🏫 <?php echo $student['facultyname']; ?></p>
    </div>

    <!-- CONTRIBUTIONS -->
    <h5>📄 Contributions</h5>

    <?php if (mysqli_num_rows($contributions) == 0) { ?>
        <div class="alert alert-info">No contributions found</div>
    <?php } ?>

    <?php while ($row = mysqli_fetch_array($contributions)) { ?>

        <div class="card p-3 mb-4 shadow-sm">

            <!-- TITLE + STATUS -->
            <div class="d-flex justify-content-between">
                <h5><?php echo $row['title']; ?></h5>

                <span class="badge 
                <?php
                if ($row['status'] == "selected") echo "bg-success";
                elseif ($row['status'] == "rejected") echo "bg-danger";
                elseif ($row['status'] == "submitted") echo "bg-warning text-dark";
                else echo "bg-secondary";
                ?>">
                <?php echo ucfirst($row['status']); ?>
                </span>
            </div>

            <small class="text-muted">
                📂 <?php echo $row['categoryname']; ?> |
                📅 <?php echo $row['submission_date']; ?>
            </small>

            <p class="mt-2"><?php echo $row['description']; ?></p>

            <!-- FILES -->
            <b>Files:</b><br>

            <?php
            $files = [$row['filepath1'], $row['filepath2'], $row['filepath3']];

            foreach ($files as $file) {
                if ($file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    // IMAGE PREVIEW
                    if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                        echo "<img src='upload/$file' width='120' class='me-2 mb-2 rounded'>";
                    }

                    // DOWNLOAD BUTTON
                    echo "<a href='upload/$file' download class='btn btn-sm btn-outline-primary me-2 mb-2'>
                            ⬇ Download
                          </a>";
                }
            }
            ?>

            <!-- DOWNLOAD ZIP -->
            <br>
            <a href="download_zip.php?id=<?php echo $row['contributionid']; ?>" 
               class="btn btn-success btn-sm">
               📦 Download All
            </a>

            <!-- COMMENTS -->
            <hr>
            <b>Comments:</b><br>

            <?php
            $comments = mysqli_query($connection, "
                SELECT c.*, u.username 
                FROM tblcomment c
                JOIN tbluser u ON c.coordinatorid = u.userid
                WHERE c.contributionid = '".$row['contributionid']."'
            ");

            if (mysqli_num_rows($comments) == 0) {
                echo "<small class='text-muted'>No comments yet</small>";
            }

            while($c = mysqli_fetch_array($comments)){
                echo "<div><b>{$c['username']}:</b> {$c['comment_text']}</div>";
            }
            ?>

        </div>

    <?php } ?>

</div>

</body>
</html>