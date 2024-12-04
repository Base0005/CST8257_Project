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

// Get user's albums for dropdown
$albumQuery = "SELECT a.Album_Id, a.Title, a.Accessibility_Code 
              FROM album a 
              WHERE a.Owner_Id = ?";
$stmt = $conn->prepare($albumQuery);
$stmt->bind_param("s", $userId);
$stmt->execute();
$albums = $stmt->get_result();

// Get selected album's pictures
$selectedAlbumId = $_GET['albumId'] ?? null;
if ($selectedAlbumId) {
    $picturesQuery = "SELECT p.Picture_Id, p.File_Name, p.Title, p.Description 
                     FROM picture p 
                     JOIN album a ON p.Album_Id = a.Album_Id 
                     WHERE p.Album_Id = ? AND a.Owner_Id = ?";
    $stmt = $conn->prepare($picturesQuery);
    $stmt->bind_param("is", $selectedAlbumId, $userId);
    $stmt->execute();
    $pictures = $stmt->get_result();
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $commentText = trim($_POST['comment']);
    $pictureId = $_POST['picture_id'];

    if (!empty($commentText)) {
        $insertComment = "INSERT INTO comment (Author_Id, Picture_Id, Comment_Text) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertComment);
        $stmt->bind_param("sis", $userId, $pictureId, $commentText);
        $stmt->execute();

        header("Location: MyPics.php?albumId=$selectedAlbumId&pictureId=$pictureId");
        exit();
    }
}

// Get selected picture's comments
$selectedPictureId = $_GET['pictureId'] ?? null;
if ($selectedPictureId) {
    $commentsQuery = "SELECT c.Comment_Text, c.Author_Id, u.Name, c.Comment_Id 
                     FROM comment c 
                     JOIN user u ON c.Author_Id = u.UserId 
                     WHERE c.Picture_Id = ? 
                     ORDER BY c.Comment_Id DESC";
    $stmt = $conn->prepare($commentsQuery);
    $stmt->bind_param("i", $selectedPictureId);
    $stmt->execute();
    $comments = $stmt->get_result();
}
?>

<main style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
    <div style="text-align: right; margin: 10px; align-self: flex-end;">
        Welcome, <?= htmlspecialchars($userName) ?>!
        (<a href="Login.php">Not You? Change User Here</a>)
    </div>

    <h1>My Pictures</h1>

    <div class="container" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
        <!-- Album Selection -->
        <select id="albumSelect" onchange="window.location.href='MyPics.php?albumId=' + this.value"
            style="margin: 20px 0; padding: 8px; border-radius: 4px; width: 300px;">
            <option value="">Select an Album</option>
            <?php while ($album = $albums->fetch_assoc()): ?>
                <option value="<?= $album['Album_Id'] ?>" <?= $selectedAlbumId == $album['Album_Id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($album['Title']) ?>
                    (<?= $album['Accessibility_Code'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <?php if ($selectedAlbumId): ?>
            <!-- Picture Display -->
            <div class="picture-gallery" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                <?php if ($selectedPictureId && $pictures->num_rows > 0):
                    $currentPic = $pictures->fetch_assoc(); ?>
                    <div class="main-picture-container">
                        <img src="pictures/<?= htmlspecialchars($currentPic['File_Name']) ?>"
                            alt="<?= htmlspecialchars($currentPic['Title']) ?>" class="main-picture">
                        <h3><?= htmlspecialchars($currentPic['Title']) ?></h3>
                        <p><?= htmlspecialchars($currentPic['Description'] ?? '') ?></p>
                    </div>
                <?php endif; ?>

                <!-- Thumbnails -->
                <div class="thumbnail-bar">
                    <?php while ($picture = $pictures->fetch_assoc()): ?>
                        <a href="MyPics.php?albumId=<?= $selectedAlbumId ?>&pictureId=<?= $picture['Picture_Id'] ?>"
                            class="thumbnail-link">
                            <img src="pictures/<?= htmlspecialchars($picture['File_Name']) ?>"
                                class="thumbnail <?= $selectedPictureId == $picture['Picture_Id'] ? 'selected' : '' ?>"
                                alt="<?= htmlspecialchars($picture['Title']) ?>">
                        </a>
                    <?php endwhile; ?>
                </div>

                <?php if ($selectedPictureId): ?>
                    <!-- Comments Section -->
                    <div class="comments-section" style="width: 100%; max-width: 800px;">
                        <h3>Comments</h3>
                        <form method="post" class="comment-form">
                            <textarea name="comment" required placeholder="Add your comment here..."
                                style="width: 100%; padding: 8px; margin: 10px 0; min-height: 100px;"></textarea>
                            <input type="hidden" name="picture_id" value="<?= $selectedPictureId ?>">
                            <input type="submit" value="Add Comment" class="button">
                        </form>

                        <div class="comments-list">
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                                <div class="comment">
                                    <p class="comment-author"><?= htmlspecialchars($comment['Name']) ?></p>
                                    <p class="comment-text"><?= htmlspecialchars($comment['Comment_Text']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    .container {
        padding: 20px;
    }

    .picture-gallery {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .main-picture-container {
        text-align: center;
        width: 100%;
        max-width: 800px;
    }

    .main-picture {
        max-width: 100%;
        height: auto;
        object-fit: contain;
    }

    .thumbnail-bar {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 4px;
        width: 100%;
        justify-content: center;
    }

    .thumbnail {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 4px;
        transition: transform 0.2s;
    }

    .thumbnail:hover {
        transform: scale(1.05);
    }

    .thumbnail.selected {
        border: 3px solid #007bff;
    }

    .comments-section {
        margin-top: 20px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 4px;
    }

    .comment {
        border-bottom: 1px solid #ddd;
        padding: 10px 0;
    }

    .comment-author {
        font-weight: bold;
        color: #007bff;
    }

    .button {
        background-color: #007bff;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .button:hover {
        background-color: #0056b3;
    }
</style>

<?php
$conn->close();
include 'footer.php';
?>