<?php
require_once("include/connect.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // CHECK dependencies
    $check1 = mysqli_query($connection, "
        SELECT * FROM tblcontribution WHERE studentid='$id'
    ");

    $check2 = mysqli_query($connection, "
        SELECT * FROM tblcomment WHERE coordinatorid='$id'
    ");

    $check3 = mysqli_query($connection, "
        SELECT * FROM tblnotification_log WHERE sent_to='$id'
    ");

    if (
        mysqli_num_rows($check1) > 0 ||
        mysqli_num_rows($check2) > 0 ||
        mysqli_num_rows($check3) > 0
    ) {

        echo "<script>
            alert('Cannot delete user: related data exists');
            window.location='register.php';
        </script>";

    } else {

        mysqli_query($connection, "
            DELETE FROM tbluser WHERE userid='$id'
        ");

        echo "<script>
            alert('User deleted successfully');
            window.location='register.php';
        </script>";
    }
}
?>