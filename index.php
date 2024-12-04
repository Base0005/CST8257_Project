<?php 
include 'header.php';

// Get user's name if logged in
if (isset($_SESSION['LoginStatus']) && $_SESSION['LoginStatus'] == 'True') {
    $host = 'localhost';
    $db = 'cst8257project';
    $user = 'root';
    $pass = '';

    $conn = new mysqli($host, $user, $pass, $db);
    
    $userId = $_SESSION['LoginUserID'];
    $nameQuery = "SELECT Name FROM user WHERE UserId = ?";
    $stmt = $conn->prepare($nameQuery);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $userName = $row ? $row['Name'] : 'Guest';
    $conn->close();
}
?>

<main style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
    <?php if (isset($_SESSION['LoginStatus']) && $_SESSION['LoginStatus'] == 'True'): ?>
        <div style="text-align: right; margin: 10px; align-self: flex-end;">
            Welcome, <?= htmlspecialchars($userName) ?>! 
            (<a href="Login.php">Not You? Change User Here</a>)
        </div>
        <img src="AC_Logo.svg" alt="Algonquin Social Media Logo" style="width: 300px;"><br>
        <h2>Welcome Back to Your Social Media Hub!</h2>
        <div class="quick-actions">
            <h3>Quick Actions:</h3>
            <ul style="list-style: none; padding: 0;">
                <li><a href="MyAlbums.php">View My Albums</a></li>
                <li><a href="AddAlbum.php">Create New Album</a></li>
                <li><a href="MyFriends.php">Manage Friends</a></li>
                <li><a href="AddPic.php">Upload New Pictures</a></li>
            </ul>
        </div>
    <?php else: ?>
        <img src="AC_Logo.svg" alt="Algonquin Social Media Logo" style="width: 300px;"><br>
        <h2>Welcome To The Algonquin Social Media Website</h2>
        <p>If You Never Used This Before, You Have To <a href="./NewUser.php">Sign Up</a> First.</p>
        <p>If You Have Already Signed Up, You Can <a href="./Login.php">Log In</a> Now.</p>
    <?php endif; ?>
</main>

<style>
.quick-actions {
    margin-top: 20px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.quick-actions ul li {
    margin: 10px 0;
}
.quick-actions ul li a {
    text-decoration: none;
    color: #007bff;
    font-size: 1.1em;
}
.quick-actions ul li a:hover {
    text-decoration: underline;
}
</style>

<?php include 'footer.php'; ?>
