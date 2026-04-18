<?php
require_once("include/connect.php");
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // CHECK if categories exist
    $check = mysqli_query($connection, "
    SELECT * FROM tblcategory WHERE academicyearid = '$id'
");

    if (mysqli_num_rows($check) > 0) {

        echo "<script>
        alert('Cannot delete: This academic year has categories');
        window.location='academic_year_register.php';
    </script>";

    } else {

        mysqli_query($connection, "
        DELETE FROM tblacademicyear WHERE academicyearid = '$id'
    ");

        echo "<script>
        alert('Deleted successfully');
        window.location='academic_year_register.php';
    </script>";
    }
}
?>