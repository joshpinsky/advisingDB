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

    require_once __DIR__ . '/viewStudents.php'; #should allow use of functions from other files
    require_once __DIR__ . '/academicStanding.php';

    $conn = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
    if ($conn->connect_error) {
        exit('Error Connecting to the DATABASE form' . $conn->connect_error);
    }



    $action = trim($_POST['action']);
    $queryType = trim($_POST['queryType']);
    $id = trim($_POST['id']);


    // TODO
    // Completion % (class, major, department, student?)
    
    //View Students
    if ($action === 'View Students') {
        if ($queryType === 'Student')
            viewByStudent($id);
        if ($queryType === 'Course')
            viewByCourse($id);
        if ($queryType === 'Instructor')
            viewByInstructor($id);
    }


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
            $query = variableOrQuery($query, 'class', $result, $conn);

            //Step 3: Execute generated query and print results
            $result = executeQuery($query, $conn);
            if ($row = $result->fetch_assoc()) {
                echo 'Average grade for courses instructed by ID ' . $_POST['id'] . ': ' . $row['AVG(grade)'];
            } else {
                echo 'No Record Found!';
            }

        } elseif ($queryType === 'Department') {
            //Step 1: Select all instructors in a given department
            $result = createQuery('SELECT id FROM teachers WHERE department = ?', $_POST['id'], $conn);

            //Step 2: Create query searching instructors for all classes they teach
            $query = 'SELECT id FROM classes WHERE ';
            $query = variableOrQuery($query, 'teacher', $result, $conn);
            $result = executeQuery($query, $conn);

            //Step 4: Create query searching enrollments for each valid id
            $query = 'SELECT AVG(grade) FROM enrollments WHERE ';
            $query = variableOrQuery($query, 'class', $result, $conn);

            //Step 5: Execute generated query and print results
            $result = executeQuery($query, $conn);
            if ($row = $result->fetch_assoc()) {
                echo 'Average grade for courses in department ID ' . $_POST['id'] . ': ' . $row['AVG(grade)'];
            } else {
                echo 'No Record Found!';
            }

        } elseif ($queryType === 'Major') {
            //Step 1: Select all students with a given major
            $result = createQuery('SELECT id FROM students WHERE major = ?', $_POST['id'], $conn);

            //Step 2: Create query searching enrollments for all students found above
            $query = 'SELECT AVG(grade) FROM enrollments WHERE ';
            $query = variableOrQuery($query, 'student', $result, $conn);

            //Step 3: Execute generated query and print results
            $result = executeQuery($query, $conn);
            if ($row = $result->fetch_assoc()) {
                echo 'Average of all grades for students with major ID ' . $_POST['id'] . ': ' . $row['AVG(grade)'];
            } else {
                echo 'No Record Found!';
            }
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
    if ($action === 'Major Completion') {

        if ($queryType === 'Student') {
            //Step 1: Find student's major
            $result = createQuery('SELECT major FROM students WHERE id = ?', $id, $conn);

            //Step 2: Find all classes the major requires
            $row = $result->fetch_assoc();
            $result = createQuery('SELECT class FROM requirements WHERE major = ?', $row['major'], $conn);

            //Step 3: Count number of required courses
            $totalCourses = 0;
            $completedCourses = 0;
            while ($row = $result->fetch_assoc()) {

                if ($stmt = $conn->prepare('SELECT grade FROM enrollments WHERE class = ? AND student = ?')) {
                    $stmt->bind_param('ii', $row['class'], $id);
                } else {
                    $conn->close();
                    exit('Error Occurred!');
                }
                $stmt->execute();
                $result2 = $stmt->get_result();
                $stmt->close();

                if ($row2 = $result2->fetch_assoc()) {
                    if ($row2['grade'] >= 70) {
                        $completedCourses++;
                    }
                }
                $totalCourses++;
            }

            //Step 4: Print results
            printf("Major completion percentage for student ID %d: %d%%", $id, 100 * ($completedCourses / $totalCourses));

        } else {
            exit('Invalid query type.');
        }

    }

    //Functions
    
    //Submits the given query to the database, binding the parameters
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
    //Submits the given query with no extra paremeters
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

    //Appends a variable number of conditions to a WHERE clause in the given query
    function variableOrQuery($query, $column, $result, $conn)
    {
        $total = 0;
        while ($row = $result->fetch_assoc()) {
            if ($total > 0) {
                $query = $query . 'OR ';
            }
            $query = $query . $column . ' = ' . $row['id'] . ' ';
            $total++;
        }
        return $query;
    }

    ?>
</body>

</html>