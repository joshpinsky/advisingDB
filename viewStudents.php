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

    function viewByStudent(String $id) {
        echo('Starting!');
        global $accessLevel;
        global $conn;
        $stmt = $conn->prepare('SELECT majors.name AS major, students.name AS student FROM students JOIN majors ON students.major = majors.id WHERE students.id = ?'); #pulls student major
        $stmt->bind_param('s', $id);
        $stmt->execute();
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        if (!$stmt->execute()) {
            die("Execute failed: " . $conn->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) {
            echo('No Record Found!');
            return;
        }
        echo('Found student name and major');

        $stmt1 = $conn->prepare('SELECT classes.name AS name, enrollments.grade as grade FROM enrollments JOIN classes ON enrollments.class = classes.id WHERE enrollments.student = ?'); #pulls all of the students classes
        $stmt1->bind_param('s', $id);
        $stmt1->execute();
        if (!$stmt1) {
            die("Prepare failed: " . $conn->error);
        }
        if (!$stmt1->execute()) {
            die("Execute failed: " . $conn->error);
        }
        $result1 = $stmt1->get_result();
        $row1 = $result1->fetch_all(MYSQLI_ASSOC);
        echo('Found student classes');
        

        if ($accessLevel == 'FIGURE OUT LATER') { #masks grade info from lower level advisors
            foreach ($row1 as $key => $class) {
                $class['grade'] = '*****';
                $row1[$key] = $class;
            }
        }

        echo("Name: {$row['student']} \n" .
            "Major: {$row['major']} \n" .
            "Classes: ");
        foreach ($row1 as $class) {
            echo("{$class['name']}, {$class['grade']}");
        }
    }

    function viewByCourse(String $id) {
        global $accessLevel;
        global $conn;
        $stmt = $conn->prepare('SELECT * FROM classes WHERE id = ?');
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt1 = $conn->prepare('SELECT student, grade FROM enrollments WHERE class = ?');
        $stmt1->bind_param('s', $id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $row1 = $result1->fetch_all(MYSQLI_ASSOC);

        foreach ($row1 as $key => $enrollment) {
            $stmt2 = $conn->prepare('SELECT major from students where id=?');
            $stmt2->bind_param('s', $enrollment['student']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();

            $stmt3 = $conn->prepare('SELECT department from majors where id=?');
            $stmt3->bind_param('s', $row2['major']);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $row3 = $result3->fetch_assoc();

            if ($row3['department'] != 'ADVISOR DEPARTMENT' || $accessLevel < 'FIGURE OUT LATER') { #applies context to by course queries
                $enrollment['grade'] = '*****'; #advisor from one department should not be able to view grades of a student not in his department
                $row1[$key] = $enrollment;
            }
        }


        print("Teacher: {$row['teacher']} \n" .
                "Department: {$row['department']} \n" .
                "Students: ");
        foreach ($row1 as $enrollment) {
            echo("{$enrollment['name']}, {$enrollment['grade']}");
        }
    }

    function viewByInstructor(String $id) {
        global $accessLevel;
        global $conn;
        $stmt = $conn->prepare('SELECT * FROM teachers WHERE id=?');
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt1 = $conn->prepare('SELECT id FROM classes WHERE teacher = ?');
        $stmt1->bind_param('s', $id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $row1 = $result1->fetch_assoc();

        print("Name: {$row['name']} \n" .
            "Department: {$row['department']}");
        
        foreach ($row1 as $course) { #lists each course the instructor teaches
            viewByCourse($course);
        }
    }

    




    ?>
</body>
</html>