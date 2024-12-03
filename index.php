<?php 
    include 'header.php';
?>

<main>
    <div>
    <h2>Welcome To The Algonquin Social Media Website</h2>
    <p>If You Never Used This Before, You Have To <a href="./NewUser.php">Sign Up</a> First.</p>
    <p>If You Have Already Signed Up, You Can <a href="./Login.php">Log In</a> Now.</p>
    </div>
    <?php echo "<pre>";
    print_r($_SESSION);
    echo "</pre>"; ?>
</main>
<?php
include 'footer.php';
?>