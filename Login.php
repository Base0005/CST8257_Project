<?php
include 'header.php';

$errorList = [];

// Check if user is already logged in
if (isset($_SESSION['LoginStatus'])) {
    $LoginStatus = $_SESSION['LoginStatus'];
    if ($LoginStatus == "True") {
        header("Location: index.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $LoginUserID = $_POST['LoginUserID'] ?? '';
    $LoginPassword = $_POST['LoginPassword'] ?? '';

    if (empty($LoginUserID)) {
        $errorList[] = "User ID is required.";
    }
    if (empty($LoginPassword)) {
        $errorList[] = "Password is required.";
    }

    if (empty($errorList)) {
        $host = 'localhost';
        $db = 'cst8257project';
        $user = 'root';
        $pass = '';

        $conn = new mysqli($host, $user, $pass, $db);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            $stmt = $conn->prepare("SELECT * FROM user WHERE UserID = ?");
            $stmt->bind_param("s", $LoginUserID);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && (password_verify($LoginPassword, $user['Password']) || $LoginPassword === $user['Password'])) {
                $_SESSION['LoginStatus'] = "True";
                $_SESSION['LoginUserID'] = $LoginUserID;
                header("Location: index.php");
                exit();
            } else {
                $errorList[] = "Invalid User ID or Password.";
            }
        }
    }
}
?>

<main style="display: flex; justify-content: center; flex-direction: column; align-items: center; min-height: 80vh;">
    <div style="width: 300px;">
        <h1>Login Page</h1>
        <form action="" method="post" style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label for="LoginUserID">User ID</label>
                <input type="text" id="LoginUserID" name="LoginUserID" required
                    style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 5px;">
                <label for="LoginPassword">Password:</label>
                <input type="password" id="LoginPassword" name="LoginPassword" required
                    style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <input type="submit" class="button" value="Log In"
                style="padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">

            <p style="text-align: center;">New User? <a href="./NewUser.php">Click Here</a> to Sign Up</p>
        </form>

        <?php if (!empty($errorList)): ?>
            <div style="color: red; margin-top: 15px;">
                <?php foreach ($errorList as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>