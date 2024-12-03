<?php
//To Verify that the User Is Logged In
include "header.php";
if (isset($_SESSION['LoginStatus'])) {
    $LoginStatus = $_SESSION['LoginStatus'];
    if ($LoginStatus == "False") {
        header("Location: Login.php");
        exit();
    }
}
?>
<main>
    <h1>Add Friend</h1>
</main>
<?php
include "footer.php"
    ?>