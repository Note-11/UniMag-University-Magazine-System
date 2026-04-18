<?php
include("include/connect.php");

$id = $_GET['id'];

// $year = $_GET['year'] ?? '';
$res = mysqli_query($connection, "
SELECT filepath1, filepath2, filepath3 
FROM tblcontribution 
WHERE contributionid = '$id'
");

$data = mysqli_fetch_assoc($res);

$zip = new ZipArchive();
$filename = "files_" . time() . ".zip";

if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
    exit("Cannot open zip");
}

$folder = "upload/";

foreach ($data as $file) {
    if ($file && file_exists($folder . $file)) {
        $zip->addFile($folder . $file, $file);
    }
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename=' . $filename);
readfile($filename);
unlink($filename);