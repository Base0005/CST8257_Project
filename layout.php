<!-- layout.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Website</title>
    <!-- Add your stylesheets here -->
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="content">
        <?php include $content; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
