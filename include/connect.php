<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "dbunimag";

$connection = mysqli_connect($host, $username, $password, $dbname);

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($connection, "utf8mb4");
?>