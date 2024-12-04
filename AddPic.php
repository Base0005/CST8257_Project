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

// Get user's albums for dropdown
$userId = $_SESSION['LoginUserID'];
$albumQuery = "SELECT Album_Id, Title FROM album WHERE Owner_Id = ?";
$stmt = $conn->prepare($albumQuery);
$stmt->bind_param("s", $userId);
$stmt->execute();
$albums = $stmt->get_result();

// Get user's name
$nameQuery = "SELECT Name FROM user WHERE UserId = ?";
$stmt = $conn->prepare($nameQuery);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$userName = $row ? $row['Name'] : 'Guest';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $albumId = $_POST['album'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $uploadSuccess = true;
    $message = '';

    // Handle multiple file uploads
    foreach ($_FILES['pictures']['tmp_name'] as $key => $tmp_name) {
        if (!empty($tmp_name)) {
            $fileName = $_FILES['pictures']['name'][$key];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Validate file type
            if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $uploadDir = "pictures/";
                $uniqueName = uniqid() . '.' . $fileType;

                if (move_uploaded_file($tmp_name, $uploadDir . $uniqueName)) {
                    $query = "INSERT INTO picture (Album_Id, File_Name, Title, Description) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("isss", $albumId, $uniqueName, $title, $description);
                    $stmt->execute();
                } else {
                    $uploadSuccess = false;
                }
            } else {
                $uploadSuccess = false;
            }
        }
    }

    if ($uploadSuccess) {
        header("Location: MyPics.php?albumId=" . $albumId);
        exit();
    } else {
        $message = "Some files could not be uploaded. Please try again with valid image files (JPG, PNG, GIF).";
    }
}
?>

<main style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
    <div style="text-align: right; margin: 10px; align-self: flex-end;">
        Welcome, <?= htmlspecialchars($userName) ?>!
        (<a href="Login.php">Not You? Change User Here</a>)
    </div>

    <h1>Upload Pictures</h1>

    <?php if (isset($message)): ?>
        <div class="alert"
            style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" style="width: 100%; max-width: 600px;">
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="album" style="display: block; margin-bottom: 5px;">Select Album:</label>
            <select name="album" id="album" required
                style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                <option value="">Choose an album</option>
                <?php while ($album = $albums->fetch_assoc()): ?>
                    <option value="<?= $album['Album_Id'] ?>"><?= htmlspecialchars($album['Title']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="pictures" style="display: block; margin-bottom: 5px;">Select Pictures (JPG, GIF, PNG):</label>
            <input type="file" name="pictures[]" id="pictures" accept=".jpg,.jpeg,.gif,.png" multiple required
                style="width: 100%; padding: 8px;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="title" style="display: block; margin-bottom: 5px;">Title (optional):</label>
            <input type="text" name="title" id="title"
                style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="description" style="display: block; margin-bottom: 5px;">Description (optional):</label>
            <textarea name="description" id="description" rows="4"
                style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;"></textarea>
        </div>

        <div class="form-actions" style="display: flex; gap: 10px; justify-content: center;">
            <button type="submit" class="button">Upload Pictures</button>
            <button type="reset" class="button secondary">Clear</button>
        </div>
    </form>
</main>

<style>
    .button {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .button:hover {
        background-color: #0056b3;
    }

    .button.secondary {
        background-color: #6c757d;
    }

    .button.secondary:hover {
        background-color: #545b62;
    }

    .form-group label {
        font-weight: bold;
    }

    .alert {
        width: 100%;
        max-width: 600px;
        margin-bottom: 20px;
    }
</style>

<?php include "footer.php"; ?>