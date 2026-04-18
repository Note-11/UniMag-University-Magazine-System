<?php  
require_once ("include/connect.php"); 
if(isset($_GET['id'])) 
{ 
    $id = $_GET['id'];

    // CHECK contributions
    $check = mysqli_query($connection, "
        SELECT * FROM tblcontribution WHERE categoryid = '$id'
    ");

    if (mysqli_num_rows($check) > 0) {

        echo "<script>
            alert('Cannot delete: This category has contributions');
            window.location='category_register.php';
        </script>";

    } else {

        mysqli_query($connection, "
            DELETE FROM tblcategory WHERE categoryid = '$id'
        ");

        echo "<script>
            alert('Deleted successfully');
            window.location='category_register.php';
        </script>";
    }
} 
?>