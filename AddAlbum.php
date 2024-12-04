<?php
include "header.php";

if ($_SESSION['LoginStatus'] != 'True') {
    header("Location: Login.php");
    exit();
}

$host = 'localhost';
$db = 'cst8257project';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

// Get user's name
$userId = $_SESSION['LoginUserID'];
$nameQuery = "SELECT Name FROM user WHERE UserId = ?";
$stmt = $conn->prepare($nameQuery);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$userName = $row ? $row['Name'] : 'Guest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $accessibility = $_POST['Accessibility'];
    $ownerId = $_SESSION['LoginUserID'];

    $query = "INSERT INTO album (Title, Description, Owner_Id, Accessibility_Code) 
              VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $title, $description, $ownerId, $accessibility);

    if ($stmt->execute()) {
        header("Location: MyAlbums.php");
        exit();
    }
}
?>

<main style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
    <div style="text-align: right; margin: 10px; align-self: flex-end;">
        Welcome, <?= htmlspecialchars($userName) ?>!
        (<a href="Login.php">Not You? Change User Here</a>)
    </div>

    <h1>Create An Album</h1>
    <form action="" method="post" style="display: flex; flex-direction: column; justify-content: center; width: 400px;">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br>

        <label type="text" for="Accessibility">Accessibility:</label>
        <select name="Accessibility" required>
            <option value="" selected disabled>Please Select An Option</option>
            <option value="private">ONLY Accessible To The User</option>
            <option value="shared">Accessible ONLY To The Users & Its Friends</option>
        </select><br>

        <label for="description">Description:</label>
        <textarea id="description" style="font-family:'Helvetica Neue'" name="description" rows="4"
            required></textarea><br>

        <div style="display: flex; justify-content:center; column-gap: 10px;">
            <input type="submit" class="button" value="Create Album" style="width: auto">
            <input type="reset" class="button" id="clear" name="clear" value="Clear" style="text-align:center;">
        </div>
    </form>
</main>

<?php
include "footer.php";
?>

<style>
    .button {
        display: inline-block;
        padding: 10px 20px;
        margin: 10px 0;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
    }
</style>