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
$message = '';
$messageClass = '';

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
    $requesterId = $_SESSION['LoginUserID'];
    $requesteeId = trim($_POST['friendId']);

    // Check if user exists
    $userCheck = $conn->prepare("SELECT UserId, Name FROM user WHERE UserId = ?");
    $userCheck->bind_param("s", $requesteeId);
    $userCheck->execute();
    $userResult = $userCheck->get_result();
    $requestedUser = $userResult->fetch_assoc();

    if (!$requestedUser) {
        $message = "User ID does not exist.";
        $messageClass = 'error';
    }
    // Check if sending to self
    elseif ($requesterId == $requesteeId) {
        $message = "You cannot send a friend request to yourself.";
        $messageClass = 'error';
    } else {
        // Check existing friendship status
        $friendCheck = $conn->prepare("SELECT Status FROM friendship 
                                    WHERE (Friend_RequesterId = ? AND Friend_RequesteeId = ?)
                                    OR (Friend_RequesterId = ? AND Friend_RequesteeId = ?)");
        $friendCheck->bind_param("ssss", $requesterId, $requesteeId, $requesteeId, $requesterId);
        $friendCheck->execute();
        $friendResult = $friendCheck->get_result();

        if ($friendResult->num_rows > 0) {
            $status = $friendResult->fetch_assoc()['Status'];
            if ($status == 'accepted') {
                $message = "You are already friends with " . htmlspecialchars($requestedUser['Name']) . ".";
                $messageClass = 'info';
            } else {
                // If there's a pending request from the other user
                $reverseCheck = $conn->prepare("SELECT Status FROM friendship 
                                            WHERE Friend_RequesterId = ? AND Friend_RequesteeId = ?");
                $reverseCheck->bind_param("ss", $requesteeId, $requesterId);
                $reverseCheck->execute();
                if ($reverseCheck->get_result()->num_rows > 0) {
                    // Accept the existing request
                    $updateFriend = $conn->prepare("UPDATE friendship SET Status = 'accepted' 
                                                WHERE Friend_RequesterId = ? AND Friend_RequesteeId = ?");
                    $updateFriend->bind_param("ss", $requesteeId, $requesterId);
                    $updateFriend->execute();
                    $message = "Friend request accepted! You are now friends with " . htmlspecialchars($requestedUser['Name']) . ".";
                    $messageClass = 'success';
                }
            }
        } else {
            // Create new friend request
            $addFriend = $conn->prepare("INSERT INTO friendship (Friend_RequesterId, Friend_RequesteeId, Status) 
                                        VALUES (?, ?, 'request')");
            $addFriend->bind_param("ss", $requesterId, $requesteeId);
            $addFriend->execute();
            $message = "Friend request sent successfully to " . htmlspecialchars($requestedUser['Name']) . "!";
            $messageClass = 'success';
        }
    }
}
?>

<main style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
    <div style="text-align: right; margin: 10px; align-self: flex-end;">
        Welcome, <?= htmlspecialchars($userName) ?>!
        (<a href="Login.php">Not You? Change User Here</a>)
    </div>

    <h2>Add Friend</h2>

    <?php if ($message): ?>
        <div class="message <?= $messageClass ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="post"
        style="display: flex; justify-content: center; flex-direction: column; align-items: center; gap: 15px;">
        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label for="friendId">Enter User ID:</label>
            <input type="text" id="friendId" name="friendId" required
                style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
        </div>
        <div>
            <input type="submit" class="button" value="Send Friend Request">
        </div>
    </form>
</main>

<style>
    .button {
        display: inline-block;
        padding: 10px 20px;
        margin: 10px 0;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        border: none;
        cursor: pointer;
    }

    .button:hover {
        background-color: #0056b3;
    }

    .message {
        margin: 20px 0;
        padding: 10px;
        border-radius: 4px;
        width: 300px;
        text-align: center;
    }

    .success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .info {
        background-color: #cce5ff;
        border: 1px solid #b8daff;
        color: #004085;
    }
</style>

<?php
$conn->close();
include "footer.php";
?>