<?php  
 session_start(); 
 session_destroy(); 
 $_SESSION["userid"]=null; 
 header("Location:login.php"); 
 ?>