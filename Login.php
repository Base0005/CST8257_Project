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
        // Database connection details
        $host = 'localhost'; // Or your database host
        $db = 'cst8257project';
        $user = 'root';      // Or your database username
        $pass = '';

        // Create connection
        $conn = new mysqli($host, $user, $pass, $db);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            echo "Connected successfully to the database.<br>";
            $UserLogin = $conn->query("SELECT * FROM user WHERE UserID = '$LoginUserID' AND Password = '$LoginPassword'");
            if ($UserLogin) {
                // Login successful
                $LoginStatus = "True";
                $_SESSION['LoginStatus'] = $LoginStatus;
                $_SESSION['UserID'] = $LoginUserID;
                header("Location: index.php");
                exit();
            } else {
                // Login failed
                $errorList[] = "Invalid Student ID or Password.";
            }
        }
    }
}
?>
<main>
    <h1>Login Page</h1>
    <form action="" method="post">
        <label for="LoginUserID">User ID</label>
        <input type="text" id="LoginUserID" name="LoginUserID" required><br>
        <label for="Password">Password:</label>
        <input type="password" id="LoginPassword" name="LoginPassword" required><br>
        <input type="submit" value="Log In">
        <p>New User? <a href="./NewUser.php">Click Here</a> to Sign Up</p>
    </form>
    <?php if (!empty($errorList)): ?>
        <div style="color: red;">
            <?php foreach ($errorList as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php echo "<pre>";
    print_r($_SESSION);
    echo "</pre>"; ?>
</main>

<?php
include 'footer.php';
?>