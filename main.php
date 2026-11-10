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
    $queryType = trim($_POST['queryType']);
    $id = trim($_POST['id']);


    // TODO
    // Average Grade (class, major, department, student)
    // Completion % (class, major, department, student?)
    
    // Average Grade
    if ($action === 'Average Grade') {
        if (!$_POST['id'])
            exit('Error: empty field!');
        // Search by Student, Course, Instructor, Department, or Major
        if ($queryType === 'Student') {
            $result = createQuery('SELECT AVG(grade) FROM enrollments WHERE student = ?', $_POST['id'], $conn);
            if ($row = $result->fetch_assoc()) {
                echo 'Average grade for student ID ' . $_POST['id'] . ': ' . $row['AVG(grade)'];
            } else {
                echo 'No Record Found!';
            }
        } elseif ($queryType === 'Course') {
            $result = createQuery('SELECT AVG(grade) FROM enrollments WHERE class = ?', $_POST['id'], $conn);
            if ($row = $result->fetch_assoc()) {
                echo 'Average grade for course ID ' . $_POST['id'] . ': ' . $row['AVG(grade)'];
            } else {
                echo 'No Record Found!';
            }
        } elseif ($queryType === 'Instructor') {
            //Step 1: Select all classes with the given instructor
            $result = createQuery('SELECT id FROM classes WHERE teacher = ?', $_POST['id'], $conn);

            //Step 2: Create query searching enrollements for each valid id
            $query = 'SELECT AVG(grade) FROM enrollments WHERE ';
            $total = 0;
            while ($row = $result->fetch_assoc()) {
                if ($total > 0) {
                    $query = $query . 'OR ';
                }
                $query = $query . 'class = ' . $row['id'] . ' ';
                $total++;
            }

            //Step 3: Execute generated query and print results
            $result = executeQuery($query, $conn);
            if ($row = $result->fetch_assoc()) {
                echo 'Average grade for courses instructed by ID ' . $_POST['id'] . ': ' . $row['AVG(grade)'];
            } else {
                echo 'No Record Found!';
            }

        } elseif ($queryType === 'Department') {
            //TODO
        } elseif ($queryType === 'Major') {
            //TODO
        } else {
            if ($stmt = $conn->prepare('SELECT AVG(grade) FROM enrollments')) {
            } else {
                $conn->close();
                exit('Error Occurred!');
            }
        }
        exit();
    }

    // Completion %
    if ($action === 'completionPercentage') {
    }

    //Functions
    
    //Submits the given query to the database
    function createQuery($query, $value, $conn)
    {
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('i', $value);
        } else {
            $conn->close();
            exit('Error Occurred!');
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        //$conn->close();
        return $result;
    }

    function executeQuery($query, $conn)
    {
        if ($stmt = $conn->prepare($query)) {
        } else {
            $conn->close();
            exit('Error Occurred!');
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        //$conn->close();
        return $result;
    }

    ?>
</body>

</html>