<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
    include main.php;
    session_start();
    $DATABASE_HOST = 'localhost';
    $DATABASE_USER = 'root';
    $DATABASE_PASS = '';
    $DATABASE_NAME = 'advisingDB';


    $conn = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

    byStudent($id) {
        $stmt = $conn->prepare('SELECT major, name FROM students WHERE id = ?')
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc()

        $stmt1 = $conn->prepare('SELECT name, grade FROM enrollments WHERE studentID - ?');
        $stmt1->bindparam('s', $id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $row1 = $result1->fetch_all(MYSQLI_ASSOC);

        if ($accessLevel = 'FIGURE OUT LATER') { #masks grade info from lower level advisors
            foreach ($row1 as $key => $class) {
                $class[grade] = *****;
                $row1[$key] = $class;
            }
        }

        $stmt2 = $conn->prepare('SELECT name FROM majors WHERE id - ?');
        $stmt2->bindparam('s', $row[major]);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $row2 = $result2->fetch_assoc();

        echo("Name: $row[name] \n
            Major: $row2[name] \n
            Classes: ");
        foreach ($row1 as $class) {
            echo("$class[name], $class[grade]");
        }
    }

    byCourse($id) {
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
            $stmt2 = $conn->prepare('SELECT major from students where id=?')
            $stmt2->bind_param('s', $enrollment[student]);
            stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = [$stmt2->fetch_assoc()];

            stmt3 = $conn->prepare('SELECT department from majors where id=?');
            $stmt3->bind_param('s', $row2[major]);
            stmt3->execute();
            $result3 = $stmt3->get_result();
            $row3 = [$stmt3->fetch_assoc()];

            if ($row3[department] != 'ADVISOR DEPARTMENT' || $accessLevel < 'FIGURE OUT LATER') { #applies context to by course queries
                $enrollment[grade] = '*****'; #advisor from one department should not be able to view grades of a student not in his department
                $row1[$key] = $enrollment
            }
        }


        print("Teacher: $row['teacher'] \n
                Department: $row['department'] \n
                Students: ");
        foreach ($row1 as $enrollment) {
            echo("$enrollment[name], $enrollment[grade]");
        }
    }




    ?>
</body>
</html>