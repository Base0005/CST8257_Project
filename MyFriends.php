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
$userId = $_SESSION['LoginUserID'];

// Get user's name
$nameQuery = "SELECT Name FROM user WHERE UserId = ?";
$stmt = $conn->prepare($nameQuery);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$userName = $row ? $row['Name'] : 'Guest';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accept_selected']) && !empty($_POST['requests'])) {
        foreach ($_POST['requests'] as $requesterId) {
            $stmt = $conn->prepare("UPDATE friendship SET Status = 'accepted' WHERE Friend_RequesterId = ? AND Friend_RequesteeId = ?");
            $stmt->bind_param("ss", $requesterId, $userId);
            $stmt->execute();
        }
    }

    if (isset($_POST['deny_selected']) && !empty($_POST['requests'])) {
        foreach ($_POST['requests'] as $requesterId) {
            $stmt = $conn->prepare("DELETE FROM friendship WHERE Friend_RequesterId = ? AND Friend_RequesteeId = ?");
            $stmt->bind_param("ss", $requesterId, $userId);
            $stmt->execute();
        }
    }

    if (isset($_POST['defriend_selected']) && !empty($_POST['friends'])) {
        foreach ($_POST['friends'] as $friendId) {
            $stmt = $conn->prepare("DELETE FROM friendship WHERE (Friend_RequesterId = ? AND Friend_RequesteeId = ?) OR (Friend_RequesterId = ? AND Friend_RequesteeId = ?)");
            $stmt->bind_param("ssss", $userId, $friendId, $friendId, $userId);
            $stmt->execute();
        }
    }

    header('Location: MyFriends.php');
    exit();
}

// Get friend requests
$requestsQuery = "SELECT u.UserId, u.Name 
                 FROM friendship f 
                 JOIN user u ON f.Friend_RequesterId = u.UserId 
                 WHERE f.Friend_RequesteeId = ? AND f.Status = 'request'";
$stmt = $conn->prepare($requestsQuery);
$stmt->bind_param("s", $userId);
$stmt->execute();
$requests = $stmt->get_result();

// Get friends and their shared album count
$friendsQuery = "SELECT u.UserId, u.Name, COUNT(DISTINCT a.Album_Id) as shared_albums 
                FROM friendship f 
                JOIN user u ON (f.Friend_RequesterId = u.UserId OR f.Friend_RequesteeId = u.UserId)
                LEFT JOIN album a ON u.UserId = a.Owner_Id AND a.Accessibility_Code = 'shared'
                WHERE (f.Friend_RequesterId = ? OR f.Friend_RequesteeId = ?) 
                AND f.Status = 'accepted'
                AND u.UserId != ?
                GROUP BY u.UserId, u.Name";
$stmt = $conn->prepare($friendsQuery);
$stmt->bind_param("sss", $userId, $userId, $userId);
$stmt->execute();
$friends = $stmt->get_result();
?>

<main style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
    <div style="text-align: right; margin: 10px; align-self: flex-end;">
        Welcome, <?= htmlspecialchars($userName) ?>!
        (<a href="Login.php">Not You? Change User Here</a>)
    </div>

    <h2>My Friends</h2>
    <p><a href="AddFriend.php" class="button">Add Friends</a></p>

    <!-- Friend Requests Section -->
    <?php if ($requests->num_rows > 0): ?>
        <h3>Friend Requests</h3>
        <form method="post" id="requestsForm">
            <table border="1">
                <tr>
                    <th>Select</th>
                    <th>Name</th>
                </tr>
                <?php while ($request = $requests->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="requests[]" value="<?= $request['UserId'] ?>"></td>
                        <td><?= htmlspecialchars($request['Name']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <input type="submit" name="accept_selected" value="Accept Selected"
                onclick="return confirm('Accept selected friend requests?')">
            <input type="submit" name="deny_selected" value="Deny Selected"
                onclick="return confirm('Are you sure you want to deny these friend requests?')">
        </form>
    <?php endif; ?>

    <!-- Friends List Section -->
    <?php if ($friends->num_rows > 0): ?>
        <h3>My Friends</h3>
        <form method="post" id="friendsForm">
            <table border="1">
                <tr>
                    <th>Select</th>
                    <th>Name</th>
                    <th>Shared Albums</th>
                </tr>
                <?php while ($friend = $friends->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="friends[]" value="<?= $friend['UserId'] ?>"></td>
                        <td>
                            <?php if ($friend['shared_albums'] > 0): ?>
                                <a href="MyPic.php?friendId=<?= $friend['UserId'] ?>">
                                    <?= htmlspecialchars($friend['Name']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($friend['Name']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $friend['shared_albums'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <input type="submit" name="defriend_selected" value="Defriend Selected"
                onclick="return confirm('Are you sure you want to remove these friends?')">
        </form>
    <?php endif; ?>
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
$conn->close();
include 'footer.php';
?>