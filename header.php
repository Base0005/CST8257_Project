<?php
session_start(); // Ensure session is started
    $LoginStatus = $_SESSION['LoginStatus'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Header</title>
        <!-- Add your stylesheets here -->
        <link rel="stylesheet" href="./style.css">
    </head>
    <body>
        <header>
            <nav style="display: flex; justify-content: space-between;">
                <a href="index.php"><img src="AC_Logo.svg" alt="Website Logo" width="40px" height="30px"></a>
                <a href="index.php">Home</a>
                <?php if ($LoginStatus == "True"):?>
                    <a href="AddAlbum.php">Add Album</a>
                    <a href="MyAlbums.php">My Albums</a>
                    <a href="AddPic.php">Upload Picture</a>
                    <a href="MyPics.php">My Pictures</a>
                    <a href="AddFriend.php">Add Friend</a>
                    <a href="MyFriends.php">My Friends</a>
                    <a href="Logout.php">Logout</a>
                <?php else:?>
                    <a href="Login.php">Log In</a>  
                    <a href="NewUser.php">Sign Up</a>
                <?php endif;?>
            </nav>
        </header>
    </body>
</html>



