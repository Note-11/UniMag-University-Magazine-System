<?php
session_start();
require_once("include/connect.php");
require_once("include/pclzip.lib.php");

// 🔒 Only Marketing Manager
if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 4) {
    die("Access denied");
}

$academicyearid = isset($_GET['academicyearid']) ? (int)$_GET['academicyearid'] : 0;

$year_filter = "";
$safe_suffix = "closed_years";

if ($academicyearid > 0) {

    $year_query = mysqli_query($connection, "
        SELECT yearname
        FROM tblacademicyear
        WHERE academicyearid = {$academicyearid}
        AND final_closure_date < CURDATE()
        LIMIT 1
    ");

    if (!$year_query || mysqli_num_rows($year_query) === 0) {
        die("This academic year is not available for download yet");
    }

    $year_data = mysqli_fetch_assoc($year_query);
    $safe_suffix = preg_replace('/[^A-Za-z0-9_-]/', '_', $year_data['yearname']);

    $year_filter = " AND ay.academicyearid = {$academicyearid}";
}

// ✅ GET FILES
$query = mysqli_query($connection, "
    SELECT c.contributionid, c.filepath1, c.filepath2, c.filepath3, ay.yearname
    FROM tblcontribution c
    INNER JOIN tblcategory cat ON c.categoryid = cat.categoryid
    INNER JOIN tblacademicyear ay ON cat.academicyearid = ay.academicyearid
    WHERE c.status = 'selected'
    AND ay.final_closure_date < CURDATE()
    $year_filter
");

if (!$query || mysqli_num_rows($query) === 0) {
    die("No files available");
}

$folder = __DIR__ . "/upload/";
$zip_name = "ALL_SELECTED_" . $safe_suffix . "_" . time() . ".zip";

// 🧠 CHECK WHICH ZIP METHOD AVAILABLE
if (class_exists('ZipArchive')) {

    // ========================
    // ✅ USE ZipArchive
    // ========================
    $zip = new ZipArchive();
    $zip_path = __DIR__ . "/upload/" . $zip_name;

    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Cannot create ZIP");
    }

    while ($row = mysqli_fetch_assoc($query)) {

        $files = [$row['filepath1'], $row['filepath2'], $row['filepath3']];

        foreach ($files as $file) {

            $file_path = $folder . $file;

            if (!empty($file) && is_file($file_path)) {

                $entry = "academic_year_" . preg_replace('/[^A-Za-z0-9_-]/', '_', $row['yearname']) .
                         "/contribution_" . $row['contributionid'] .
                         "/" . basename($file);

                $zip->addFile($file_path, $entry);
            }
        }
    }

    $zip->close();

} else {

    // ========================
    // ✅ USE PclZip (FALLBACK)
    // ========================

    $zip_path = __DIR__ . "/upload/" . $zip_name;

    $archive = new PclZip($zip_path);
    $file_list = [];

    while ($row = mysqli_fetch_assoc($query)) {

        $files = [$row['filepath1'], $row['filepath2'], $row['filepath3']];

        foreach ($files as $file) {

            $file_path = $folder . $file;

            if (!empty($file) && is_file($file_path)) {

                $entry = "academic_year_" . preg_replace('/[^A-Za-z0-9_-]/', '_', $row['yearname']) .
                         "/contribution_" . $row['contributionid'] .
                         "/" . basename($file);

                $file_list[] = [
                    PCLZIP_ATT_FILE_NAME => $file_path,
                    PCLZIP_ATT_FILE_NEW_FULL_NAME => $entry
                ];
            }
        }
    }

    if ($archive->create($file_list) == 0) {
        die("PclZip Error: " . $archive->errorInfo(true));
    }
}

// 🧹 CLEAN OUTPUT
while (ob_get_level()) ob_end_clean();

// ✅ DOWNLOAD
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_name . '"');
header('Content-Length: ' . filesize($zip_path));

readfile($zip_path);

// 🧹 DELETE
unlink($zip_path);
exit();
?>