<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php
    session_start();
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'advisingdb';


    $conn = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);


    if (!isset($_POST['username'], $_POST['password'])) {
        exit("Please enter both your username and password");
    }


    if ($_POST['username'] === '' || $_POST['password'] === '') {
        exit("Please enter both your username and password");
    }


    echo ("Authorizing...<br>");


    if ($stmt = $conn->prepare('SELECT id, password FROM advisors WHERE username = ?')) {
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($_POST['password'], $row['password'])) {
                echo ("User successfully authorized<br>");
                $_SESSION['userID'] = $row['id'];
                echo ("<a href='login.html'>Go to main page</a>");
            } else {
                echo ("Invalid username or password");
            }
        } else {
            echo ("Invalid username or password");
        }
    } else {
        echo ("Invalid username or password");
    }
    ?>
</body>

</html>