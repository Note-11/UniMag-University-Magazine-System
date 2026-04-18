<?php
session_start();
require_once("include/connect.php");

$userid = $_SESSION['userid'];

$res = mysqli_query($connection, "
SELECT n.*, 
       c.title, 
       c.description, 
       c.submission_date, 
       c.filepath1, 
       c.filepath2, 
       c.filepath3,
       u.username AS student_name, 
       u.email AS student_email, 
       f.facultyname
FROM tblnotification_log n
JOIN tblcontribution c ON n.contributionid = c.contributionid
JOIN tbluser u ON c.studentid = u.userid
JOIN tblfaculty f ON u.facultyid = f.facultyid
WHERE n.sent_to = '$userid'
ORDER BY n.notificationid DESC
");
?>

<h3>Notifications</h3>

<?php if (mysqli_num_rows($res) == 0) { ?>
    <div>No notifications yet.</div>
<?php } else { 
    while($row = mysqli_fetch_array($res)){ ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            🔔 <b><?php echo $row['title']; ?></b><br>
            <p><b>Description:</b> <?php echo $row['description']; ?></p>
            <p><b>Student:</b> <?php echo $row['student_name']; ?> (<?php echo $row['student_email']; ?>)</p>
            <p><b>Faculty:</b> <?php echo $row['facultyname']; ?></p>
            <p><b>Submitted on:</b> <?php echo $row['submission_date']; ?></p>
            <p><b>Notification Date:</b> <?php echo $row['sent_date']; ?></p>
            <p><b>Status:</b> <?php echo ucfirst($row['status']); ?></p>

            <!-- 🔗 Links to uploaded files -->
            <p><b>Files:</b><br>
                <?php if (!empty($row['filepath1'])) { ?>
                    <a href="upload/<?php echo $row['filepath1']; ?>" target="_blank">View File 1</a><br>
                <?php } ?>
                <?php if (!empty($row['filepath2'])) { ?>
                    <a href="upload/<?php echo $row['filepath2']; ?>" target="_blank">View File 2</a><br>
                <?php } ?>
                <?php if (!empty($row['filepath3'])) { ?>
                    <a href="upload/<?php echo $row['filepath3']; ?>" target="_blank">View File 3</a><br>
                <?php } ?>
            </p>
        </div>
    <?php } 
} ?>

<?php
// mark as read
mysqli_query($connection, "
UPDATE tblnotification_log 
SET status='read' 
WHERE sent_to = '$userid'
");
?>
