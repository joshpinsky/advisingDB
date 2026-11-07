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
    if ($conn->connect_error) {
        exit('Error Connecting to the DATABASE form' . $conn->connect_error);
    }

    

    $action = trim($_POST['action']);
    $queryType = strtolower(trim($_POST['queryType']));
    $id = trim($_POST['id']);

    // TODO
    // Average Grade (class, major, department, student)
    // Completion % (class, major, department, student?)

    // Average Grade
    if ($action === 'Average Grade') {
    // Accept class id, student id, or neither
        if ($queryType === 'Student') {
            if ($stmt = $conn->prepare('SELECT AVG(grade) FROM enrollments WHERE student = ?')) {
                $stmt->bind_param('?', $_POST['id']);
            } else { $conn->close(); exit('Error Occurred!'); }
        } elseif ($queryType === 'Course') {
            if ($stmt = $conn->prepare('SELECT AVG(grade) FROM enrollments WHERE class = ?')) {
                $stmt->bind_param('?', $_POST['id']);
            } else { $conn->close(); exit('Error Occurred!'); }
        } elseif ($queryType === 'Instructor') {
        } elseif ($queryType === 'Department') {
        } elseif ($queryType === 'Major') {
        } else {
            if ($stmt = $conn->prepare('SELECT AVG(grade) FROM enrollments')) {
            } else { $conn->close(); exit('Error Occurred!'); }
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            echo $row['AVG(grade)'];
        } else {
            echo 'No Record Found!';
        }
        $stmt->close();
        $conn->close();
        exit();
    }

    // Completion %
    if ($action === 'completionPercentage') {

    }

    ?>
</body>
</html>