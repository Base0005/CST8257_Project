<?php
include 'header.php';

$errorList = [];
$userId = $name = $phone = $password = $confirmPassword = "";

// Database connection details
$host = 'localhost'; // Or your database host
$db = 'cst8257project';
$user = 'root';      // Or your database username
$pass = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['UserId'] ?? '';
    $name = $_POST['Name'] ?? '';
    $phone = $_POST['Phone'] ?? '';
    $password = $_POST['Password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate Student ID
    if (empty($userId)) {
        $errorList[] = "Student ID is required.";
    }

    // Validate Name
    if (empty($name)) {
        $errorList[] = "Name is required.";
    }

    // Validate Phone Number
    if (empty($phone)) {
        $errorList[] = "Phone Number is required.";
    } elseif (!preg_match('/^(?!1)(\(\d{3}\)\s?|\d{3}[-.\s]?)\d{3}[-.\s]?\d{4}$/', $phone)) { // Adjusted to match 10 digits without area code validation
        $errorList[] = "Phone Number must be 10 digits.";
    }

    // Validate Password
    if (empty($password)) {
        $errorList[] = "Password is required.";
    } elseif ($password !== $confirmPassword) {
        $errorList[] = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{6,}$/', $password)) {
        $errorList[] = "Password must be at least 6 characters long, contain at least one uppercase letter, one lowercase letter, and one digit.";
    }

    // If no errors, process the form (e.g., save to database)
    if (empty($errorList)) {
        $conn = new mysqli($host, $user, $pass, $db);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } else {
            try {
                $studentAddition = $conn->query("INSERT INTO user (UserId, Name, Phone, Password) VALUES ('$userId', '$name', '$phone', '$password')");
                if ($studentAddition) {
                    echo "New student added successfully.<br>";
                    // Store the student's information in the session
                    $_SESSION['UserId'] = $userId;
                    $_SESSION['Name'] = $name;
                    $_SESSION['Phone'] = $phone;
                    $_SESSION['Password'] = $password;
                    header("Location: index.php"); // Redirect to the CourseSelection page
                    $conn->close();  // Close the database connection
                } else {
                    echo "Error adding student: " . $conn->error;
                }
            } catch (Exception $e) {
                // Handle the exception and show the error message
                $errorMessage = $e->getMessage();

                // Check if the error is a duplicate entry
                if (strpos($errorMessage, "Duplicate entry") !== false) {
                    $errorList[] = "User ID '$userId' is already being used.";
                } else {
                    $errorList[] = $errorMessage; // Generic error message
                }
            }
        }
    }
}
?>

<main>
    <h1>Registration Page</h1>
    <form action="" method="post">
        <label for="UserId">User ID:</label>
        <input type="text" id="UserId" name="UserId" value="<?php echo htmlspecialchars($userId); ?>"
            required><br>

        <label for="Name">Name:</label>
        <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($name); ?>" required><br>

        <label for="Phone">Phone Number:</label>
        <input type="tel" id="Phone" name="Phone" value="<?php echo htmlspecialchars($phone); ?>" required><br>

        <label for="Password">Password:</label>
        <input type="password" id="Password" name="Password" required><br>

        <label for="confirmPassword">Confirm Password:</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required><br>

        <input type="submit" value="Submit">
    </form>

    <?php if (!empty($errorList)): ?>
        <div style="color: red;">
            <?php foreach ($errorList as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ?>
</main>

<?php
include 'footer.php';
?>