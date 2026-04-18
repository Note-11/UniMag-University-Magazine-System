<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Magazine System</title>
    <link rel="stylesheet" href="../../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../assets/css/layout.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
    <header>
        <div class="logosec">
            <div class="logo">UniMag System</div>
            <span class="material-symbols-outlined menuicn" id="menuicn">menu</span>
        </div>

        <div class="searchbar">
            <input type="text" placeholder="Search...">
            <div class="searchbtn">
              <span class="material-symbols-outlined search-icon">search</span>
            </div>
        </div>

        <div class="message">
            <div class="last-login">
                <span class="login-label">Last login:</span> 
                <span class="login-time">21 Feb 2026, 08:00 AM</span>
            </div>
            
            <div class="notification-icon">
                <div class="circle"></div>
                <span class="material-symbols-outlined notif-icon">notifications</span>
            </div>
            <div class="dp">
              <img src="https://media.geeksforgeeks.org/wp-content/uploads/20221210180014/profile-removebg-preview.png"
                    class="dpicn" 
                    alt="profile">
            </div>
        </div>
    </header>
</body>
</html>