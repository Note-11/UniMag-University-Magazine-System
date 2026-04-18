<?php
session_start();
require_once("include/connect.php");

// 🔒 ONLY COORDINATOR
if (!isset($_SESSION['roleid']) || $_SESSION['roleid'] != 3) {
    echo "<script>alert('Access denied'); window.location='login.php';</script>";
    exit();
}

$facultyid = $_SESSION['facultyid'];
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'userid';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Pagination setup
$limit = 20; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Validate sort column
$valid_columns = ['userid','username','email','created_at'];
if (!in_array($sort, $valid_columns)) {
    $sort = 'userid';
}

// Validate order
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Base SQL
$sql = "
    SELECT u.userid, u.username, u.email, u.created_at, f.facultyname
    FROM tbluser u
    JOIN tblfaculty f ON u.facultyid = f.facultyid
    WHERE u.roleid = 5
    AND u.facultyid = '$facultyid'
";

// Search filter
if ($search != '') {
    $sql .= " AND (
        u.userid LIKE '%$search%' 
        OR u.username LIKE '%$search%' 
        OR u.email LIKE '%$search%' 
        OR u.created_at LIKE '%$search%'
    )";
}

// Count total for pagination
$count_sql = str_replace("SELECT u.userid, u.username, u.email, u.created_at, f.facultyname",
                         "SELECT COUNT(*) as total", $sql);
$count_result = mysqli_query($connection, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Add sorting + pagination
$sql .= " ORDER BY $sort $order LIMIT $limit OFFSET $offset";
$query = mysqli_query($connection, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coordinator Guest View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <!-- BACK BUTTON ABOVE TITLE -->
    <div class="mb-3">
        <a href="contribution_list.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
    </div>

    <?php
    $faculty = mysqli_fetch_assoc(mysqli_query($connection, "
        SELECT facultyname FROM tblfaculty WHERE facultyid = '$facultyid'
    "));
    ?>
    <h3>👥 Guests in <?php echo $faculty['facultyname']; ?> Faculty</h3>

    <!-- SEARCH FORM -->
    <div class="mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by ID, Name, Email, or Registered At"
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">🔍 Search</button>
            </div>
            <div class="col-md-2">
                <a href="coordinator_guest_view.php" class="btn btn-secondary w-100">Show All</a>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($query) == 0) {
        echo "<div class='alert alert-warning'>No guests found.</div>";
    } else { ?>

    <!-- RESULTS COUNT -->
    <div class="mb-2">
        <?php
        $start = $offset + 1;
        $end = min($offset + $limit, $total_rows);
        echo "<p class='text-muted'>Showing $start–$end of $total_rows guests</p>";
        ?>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Number</th>
                <?php
                // Helper to render sortable headers with arrows
                function sortLink($column, $label, $search, $sort, $order, $page) {
                    $newOrder = ($sort == $column && $order == 'ASC') ? 'DESC' : 'ASC';
                    $arrow = '';
                    if ($sort == $column) {
                        $arrow = $order == 'ASC' ? ' ↑' : ' ↓';
                    }
                    return "<a href=\"?search=$search&sort=$column&order=$newOrder&page=$page\">$label$arrow</a>";
                }
                ?>
                <th><?php echo sortLink('userid','ID',$search,$sort,$order,$page); ?></th>
                <th><?php echo sortLink('username','Name',$search,$sort,$order,$page); ?></th>
                <th><?php echo sortLink('email','Email',$search,$sort,$order,$page); ?></th>
                <th>Faculty</th>
                <th><?php echo sortLink('created_at','Registered At',$search,$sort,$order,$page); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $row_number = $offset + 1; // start numbering based on pagination
            while($row = mysqli_fetch_assoc($query)) { ?>
            <tr>
                <td><?php echo $row_number++; ?></td>
                <td><?php echo $row['userid']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['facultyname']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- PAGINATION -->
    <nav>
        <ul class="pagination">
            <?php if ($page > 1) { ?>
                <li class="page-item"><a class="page-link" href="?search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page-1; ?>">Previous</a></li>
            <?php } ?>
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php } ?>
            <?php if ($page < $total_pages) { ?>
                <li class="page-item"><a class="page-link" href="?search=<?php echo $search; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&page=<?php echo $page+1; ?>">Next</a></li>
            <?php } ?>
        </ul>
    </nav>

    <?php } ?>
</div>
</body>
</html>
