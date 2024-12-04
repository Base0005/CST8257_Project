<?php
include "header.php";

$host = 'localhost';
$db = 'cst8257project';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['userId'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate user ID format
    if (!preg_match('/^[a-zA-Z0-9]+$/', $userId)) {
        $message = "User ID can only contain letters and numbers.";
    }
    // Check if user ID already exists
    else {
        $stmt = $conn->prepare("SELECT UserId FROM user WHERE UserId = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "This User ID is already taken.";
        }
        // Validate phone number format (nnn-nnn-nnnn)
        elseif (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) {
            $message = "Phone number must be in format: nnn-nnn-nnnn";
        }
        // Check if passwords match
        elseif ($password !== $confirmPassword) {
            $message = "Passwords do not match.";
        } else {
            // Hash password and insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (UserId, Name, Phone, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $userId, $name, $phone, $hashedPassword);

            if ($stmt->execute()) {
                header("Location: Login.php");
                exit();
            } else {
                $message = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<main style="display: flex; justify-content: center; align-items: center;">
    <div>
        <h1>Sign Up</h1>
        <?php if ($message): ?>
            <div class="error-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" style="display: flex; flex-direction: column; width: 300px;">
            <div>
                <label for="userId">User ID:</label>
                <input type="text" id="userId" name="userId" required>
            </div>

            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div>
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" placeholder="nnn-nnn-nnnn" required>
            </div>

            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div>
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <input type="submit" class="button" value="Submit">
                <input type="reset" class="button" value="Clear">
            </div>
        </form>
    </div>
</main>

<style>
    .error-message {
        color: red;
        margin: 10px 0;
        padding: 10px;
        border: 1px solid red;
        background-color: #ffe6e6;
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

    form div {
        margin: 10px 0;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input[type="text"],
    input[type="tel"],
    input[type="password"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
    }
</style>

<?php
$conn->close();
include "footer.php";
?>