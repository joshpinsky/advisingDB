<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 

    function viewByStudent(String $id) {
        echo("Starting! <br>");
        global $accessLevel;
        global $conn;
        global $department;
        
        $stmt = $conn->prepare('SELECT majors.name AS major, students.name AS student FROM students JOIN majors ON students.major = majors.id WHERE students.id = ?'); #pulls student major
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param('s', $id);
        
        if (!$stmt->execute()) {
            die("Execute failed: " . $conn->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) {
            echo("No Record Found! <br>");
            return;
        }

        $stmt1 = $conn->prepare('SELECT classes.id AS class, classes.name AS name, enrollments.grade as grade FROM enrollments JOIN classes ON enrollments.class = classes.id WHERE enrollments.student = ?'); #pulls all of the students classes
        if (!$stmt1) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt1->bind_param('s', $id);
        
        if (!$stmt1->execute()) {
            die("Execute failed: " . $conn->error);
        }
        $result1 = $stmt1->get_result();
        $row1 = $result1->fetch_all(MYSQLI_ASSOC);
        

        if ($accessLevel == 0) { #masks grade info from lower level advisors
            foreach ($row1 as $key => $class) {
                $class['grade'] = '*****';
                $row1[$key] = $class;
            }
        }
        

        if ($accessLevel == 1) { #hides grades in classes not in advisors department
            foreach ($row1 as $key => $class) {
                $result2 = createQuery('SELECT teachers.department AS department FROM classes JOIN teachers ON classes.teacher = teachers.id WHERE classes.id = ?', $row1[$key]['class'], $conn);
                $row2 = $result2->fetch_assoc();
                if ($row2['department'] != $department) {
                    $class['grade'] = '*****';
                    $row1[$key] = $class;
                }
            }
        }

        echo("Name: {$row['student']} <br>" .
            "Major: {$row['major']} <br>" .
            "Classes: ");
        foreach ($row1 as $class) {
            echo("{$class['name']}, {$class['grade']} <br>");
        }
    }

    function viewByCourse(String $id) {
        global $accessLevel;
        global $conn;
        global $department;
        $stmt = $conn->prepare('SELECT classes.teacher AS teacher, teachers.name AS name, teachers.department AS department, departments.name AS departmentName FROM classes JOIN teachers ON classes.teacher = teachers.id JOIN departments ON teachers.department = departments.id WHERE classes.id = ?');
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param('s', $id); #selects name and department of teacher in $row
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt1 = $conn->prepare('SELECT students.name AS name, students.id AS student, enrollments.grade AS grade FROM enrollments JOIN students ON enrollments.student = students.id WHERE enrollments.class = ?');
        if (!$stmt1) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt1->bind_param('s', $id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $row1 = $result1->fetch_all(MYSQLI_ASSOC);

        foreach ($row1 as $key => $enrollment) {
            $stmt2 = $conn->prepare('SELECT students.major, majors.department AS department FROM students JOIN majors ON students.major = majors.id WHERE students.id = ?');
            if (!$stmt2) {
            die("Prepare failed: " . $conn->error);
        }
            $stmt2->bind_param('s', $enrollment['student']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();

            if (($accessLevel == 1 && $row2['department'] != $department) || $accessLevel == 0) { #applies context to by course queries
                $enrollment['grade'] = '*****'; #advisor from one department should not be able to view grades of a student not in his department
                $row1[$key] = $enrollment;
            }
        }


        print("Teacher: {$row['name']} <br>" .
                "Department: {$row['departmentName']} <br>" .
                "Students:<br> ");
        foreach ($row1 as $enrollment) {
            echo("{$enrollment['name']}, {$enrollment['grade']} <br>");
        }
        echo ("<br>");
    }

    function viewByInstructor(String $id) {
        global $accessLevel;
        global $conn;
        $stmt = $conn->prepare('SELECT * FROM teachers WHERE id=?');
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt1 = $conn->prepare('SELECT id, name FROM classes WHERE teacher = ?');
        $stmt1->bind_param('s', $id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $row1 = $result1->fetch_all(MYSQLI_ASSOC);


        print("Name: {$row['name']} <br>" .
            "Department: {$row['department']} <br>");

        echo("<br>Instructor Courses:<br><br>");

        foreach ($row1 as $course) { #lists each course the instructor teaches

            echo("{$course['name']}<br>");
            viewByCourse($course['id']);
        }
    }
    ?>
</body>
</html>