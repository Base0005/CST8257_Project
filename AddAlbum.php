<?php
include "header.php";
if (isset($_SESSION['LoginStatus'])) {
    $LoginStatus = $_SESSION['LoginStatus'];
    if ($LoginStatus == "False") {
        header("Location: Login.php");
        exit();
    }
}
?>
<main style="display: flex; justify-content: center; align-items: center;">
    <div>
        <h1>Create An Album</h1>
        <form action="" method="post"
            style="display: flex; flex-direction: column; justify-content: center; width: 400px;">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required><br>
            <label type="text" for="Accessibility">Accessibility:</label>
            <select name="Accessibility">
                <option value="" selected disabled>Please Select An Option</option>
                <option value="private"> ONLY Accessible To The User</option>
                <option value="shared">Accessible ONLY To The Users & Its Friends</option>
            </select><br>
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea><br>
            <div style="display: flex; justify-content:center; column-gap: 10px;">
                <input type="submit" value="Create Album" style="width: auto">
                <input type="reset" id="clear" name="clear" value="Clear" style="text-align:center;">
            </div>
        </form>
    </div>
</main>

<?php
include "footer.php";
?>