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
                $class[grade] = *****
                $row1[$key] = $class
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

    




    ?>
</body>
</html>