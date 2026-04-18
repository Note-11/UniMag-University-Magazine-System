<?php 
    $current_page = basename($_SERVER['PHP_SELF']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../assets/css/adm_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <title>Magazine System</title>
</head>
<body>
    <div class="navcontainer">
        <nav class="nav">
            <div class="nav-upper-options">
                <a href="../../pages/administrator/admin-dashboard.php" class="nav-option <?php echo ($current_page == 'admin-dashboard.php') ? 'option1' : ''; ?>">
                    <span class="material-symbols-outlined nav-icon">dashboard</span>
                    <h3>Dashboard</h3>
                </a>
                
                <a href="../../pages/administrator/manage-users.php" class="nav-option <?php echo ($current_page == 'manage-users.php' || $current_page == 'edit-user.php') ? 'option1' : ''; ?>">
                    <span class="material-symbols-outlined nav-icon">manage_accounts</span>
                    <h3>Manage Users</h3>
                </a>
                
                <a href="../../pages/administrator/assign-roles.php" class="nav-option <?php echo ($current_page == 'assign-roles.php') ? 'option1' : ''; ?>">
                    <span class="material-symbols-outlined nav-icon">badge</span>
                    <h3>Assign Roles</h3>
                </a>
                
                <a href="../../pages/administrator/manage-academic-years.php" class="nav-option <?php echo ($current_page == 'manage-academic-years.php') ? 'option1' : ''; ?>">
                    <span class="material-symbols-outlined nav-icon">calendar_month</span>
                    <h3>Manage Academic Years</h3>
                </a>
                
                <a href="../../pages/administrator/manage-faculties.php" class="nav-option <?php echo ($current_page == 'manage-faculties.php') ? 'option1' : ''; ?>">
                    <span class="material-symbols-outlined nav-icon">account_balance</span>
                    <h3>Manage Faculties</h3>
                </a>
                
                <a href="../../auth/logout.php" class="nav-option logout">
                    <span class="material-symbols-outlined nav-icon">logout</span>
                    <h3>Logout</h3>
                </a>
            </div>
        </nav>
    </div>
</body>
<script src="../../assets/js/adm-dash.js"></script>
</html>