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

// 🔥 EXCEPTION QUERY
$query = mysqli_query($connection, "
SELECT c.*, 
       s.username as studentname,
       f.facultyname,
       cat.categoryname,
       DATEDIFF(CURDATE(), c.submission_date) as days_passed
FROM tblcontribution c
JOIN tbluser s ON c.studentid = s.userid
JOIN tblfaculty f ON s.facultyid = f.facultyid
JOIN tblcategory cat ON c.categoryid = cat.categoryid
LEFT JOIN tblcomment cm ON c.contributionid = cm.contributionid
WHERE cm.commentid IS NULL
AND DATEDIFF(CURDATE(), c.submission_date) > 14
AND s.facultyid = '$facultyid'
ORDER BY days_passed DESC
");

if (isset($_POST['btncomment'])) {
    $cid = $_POST['cid'];
    $comment = $_POST['comment'];
    $coordinator = $_SESSION['userid'];

    mysqli_query($connection, "
        INSERT INTO tblcomment(contributionid, coordinatorid, comment_text, comment_date)
        VALUES('$cid','$coordinator','$comment', NOW())
    ");

    echo "<script>alert('Comment added'); window.location='exception_report.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exception Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <!-- Back button -->
    <div class="mb-3">
        <a href="contribution_list.php" class="btn btn-secondary">
            ⬅ Back to Dashboard
        </a>
    </div>

    <h3 class="mb-4 text-danger">⚠ Exception Report (No Comments after 14 Days)</h3>

    <table class="table table-bordered table-hover bg-white">

        <thead class="table-danger">
            <tr>
                <th>Student</th>
                <th>Faculty</th>
                <th>Category</th>
                <th>Title</th>
                <th>Submitted Date</th>
                <th>Days Passed</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php if (mysqli_num_rows($query) == 0) { ?>
            <tr>
                <td colspan="8" class="text-center text-success">
                    🎉 No contributions with no comments older than 14 days were found.
                </td>
            </tr>
        <?php } else { 
            while($row = mysqli_fetch_array($query)){ ?>
                <tr>
                    <td><?php echo $row['studentname']; ?></td>
                    <td><?php echo $row['facultyname']; ?></td>
                    <td><?php echo $row['categoryname']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['submission_date']; ?></td>
                    <td><span class="badge bg-danger"><?php echo $row['days_passed']; ?> days</span></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#commentModal<?php echo $row['contributionid']; ?>">
                            💬 Comment
                        </button>
                    </td>
                </tr>

                <!-- COMMENT MODAL -->
                <div class="modal fade" id="commentModal<?php echo $row['contributionid']; ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5>Add Comment</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST">
                                    <input type="hidden" name="cid" value="<?php echo $row['contributionid']; ?>">
                                    <textarea name="comment" class="form-control mb-3" required></textarea>
                                    <button name="btncomment" class="btn btn-primary w-100">Submit Comment</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } 
        } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
