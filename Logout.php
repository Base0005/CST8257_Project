<?php
    include 'header.php';
    $LoginStatus = $_SESSION['LoginStatus'] ?? '';
    $name = $_SESSION['Name'] ?? '';
?>
<main>
    <h1>Thank You, <?php echo htmlspecialchars($name)?>, for Using Our Online Registration</h1>
    <h2>You Have Successfully Logged Out</h2>
</main>

<?php
    $LoginStatus = "False";
    $_SESSION['LoginStatus'] = $LoginStatus;
    session_unset();
    session_destroy(); 
?>
<?php
include 'footer.php';
?>