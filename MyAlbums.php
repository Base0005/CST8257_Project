<?php
include 'header.php';

if ($_SESSION['LoginStatus'] != 'True') {
    header('Location: Login.php');
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

// Handle form submission for saving changes
if (isset($_POST['save_changes'])) {
    foreach ($_POST['accessibility'] as $albumId => $accessibility) {
        $stmt = $conn->prepare("UPDATE album SET Accessibility_Code = ? WHERE Album_Id = ?");
        $stmt->bind_param("si", $accessibility, $albumId);
        $stmt->execute();
    }
    header('Location: MyAlbums.php');
    exit();
}

// Handle album deletion
if (isset($_POST['delete_album'])) {
    $albumId = $_POST['album_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete pictures first
        $stmt = $conn->prepare("DELETE FROM picture WHERE Album_Id = ?");
        $stmt->bind_param("i", $albumId);
        $stmt->execute();

        // Then delete the album
        $stmt = $conn->prepare("DELETE FROM album WHERE Album_Id = ?");
        $stmt->bind_param("i", $albumId);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        header('Location: MyAlbums.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}

// Get current user's albums
$query = "SELECT a.Album_Id, a.Title, a.Accessibility_Code, COUNT(p.Picture_Id) as picture_count 
          FROM album a 
          LEFT JOIN picture p ON a.Album_Id = p.Album_Id
          WHERE a.Owner_Id = ?
          GROUP BY a.Album_Id, a.Title, a.Accessibility_Code";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<main style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
    <div style="text-align: right; margin: 10px; align-self: flex-end;">
        Welcome, <?= htmlspecialchars($userName) ?>!
        (<a href="Login.php">Not You? Change User Here</a>)
    </div>

    <h2>My Albums</h2>
    <p><a href="AddAlbum.php" class="button">Create a New Album</a></p>

    <form method="post">
        <table border="1" style="width: 100%">
            <thead>
                <tr style="text-align: center">
                    <th>Title</th>
                    <th>Number of Pictures</th>
                    <th>Accessibility</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($album = $result->fetch_assoc()): ?>
                    <tr style="text-align: center">
                        <td>
                            <a href="MyPictures.php?albumId=<?= $album['Album_Id'] ?>">
                                <?= htmlspecialchars($album['Title']) ?>
                            </a>
                        </td>
                        <td><?= $album['picture_count'] ?></td>
                        <td>
                            <select name="accessibility[<?= $album['Album_Id'] ?>]">
                                <option value="private" <?= $album['Accessibility_Code'] == 'private' ? 'selected' : '' ?>>
                                    Private</option>
                                <option value="shared" <?= $album['Accessibility_Code'] == 'shared' ? 'selected' : '' ?>>Shared
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="album_id" value="<?= $album['Album_Id'] ?>">
                            <input type="submit" name="delete_album" value="DELETE" class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this album? All pictures in the album will be deleted.');">
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div style="margin-top: 20px; text-align: center;">
            <input type="submit" name="save_changes" value="Save Changes" class="button">
        </div>
    </form>
</main>

<style>
    table {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        text-align: center;
    }

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

<?php
$stmt->close();
$conn->close();
include 'footer.php';
?>