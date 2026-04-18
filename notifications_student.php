<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("include/connect.php");

if (!isset($_SESSION['userid'])) {
    echo "<script>
        alert('Please login first');
        window.location='login.php';
    </script>";
    exit();
}

$userid = $_SESSION['userid'];

// ✅ Get latest notification per contribution (NO DUPLICATES)
$res = mysqli_query($connection, "
SELECT n.*, 
       c.title, 
       c.description, 
       c.submission_date,
       f.facultyname,

       (SELECT cm.comment_text 
        FROM tblcomment cm 
        WHERE cm.contributionid = c.contributionid 
        ORDER BY cm.commentid DESC 
        LIMIT 1) AS latest_comment,

       (SELECT cm.comment_date 
        FROM tblcomment cm 
        WHERE cm.contributionid = c.contributionid 
        ORDER BY cm.commentid DESC 
        LIMIT 1) AS comment_date,

       (SELECT u.username 
        FROM tblcomment cm
        JOIN tbluser u ON cm.coordinatorid = u.userid
        WHERE cm.contributionid = c.contributionid 
        ORDER BY cm.commentid DESC 
        LIMIT 1) AS coordinator_name

FROM tblnotification_log n
JOIN tblcontribution c ON n.contributionid = c.contributionid
JOIN tbluser u ON c.studentid = u.userid
JOIN tblfaculty f ON u.facultyid = f.facultyid

WHERE n.sent_to = '$userid'

ORDER BY n.notificationid DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>🔔 My Notifications</h3>

<?php if (mysqli_num_rows($res) == 0) { ?>
    <div class="alert alert-info">No notifications yet.</div>
<?php } else { 
    while($row = mysqli_fetch_array($res)){ ?>
        
        <div class="card mb-3 shadow-sm">
            <div class="card-body">

                <h5>🔔 <?php echo $row['title']; ?></h5>

                <p><b>Description:</b> <?php echo $row['description']; ?></p>

                <p><b>Faculty:</b> <?php echo $row['facultyname']; ?></p>

                <p><b>Submitted on:</b> <?php echo $row['submission_date']; ?></p>

                <p><b>Notification Date:</b> <?php echo $row['sent_date']; ?></p>

                <p>
                    <b>Status:</b> 
                    <span class="badge bg-secondary">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </p>

                <hr>

                <h6>💬 Latest Feedback</h6>

                <?php if (!empty($row['latest_comment'])) { ?>
                    <p><?php echo $row['latest_comment']; ?></p>

                    <small class="text-muted">
                        By <?php echo $row['coordinator_name']; ?> 
                        on <?php echo $row['comment_date']; ?>
                    </small>
                <?php } else { ?>
                    <p><i>No feedback yet</i></p>
                <?php } ?>

            </div>
        </div>

<?php } } ?>

<?php
// ✅ Mark all as read
mysqli_query($connection, "
UPDATE tblnotification_log 
SET status='read' 
WHERE sent_to = '$userid'
");
?>

</body>
</html>