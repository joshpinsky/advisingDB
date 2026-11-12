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

    require_once __DIR__ . '/main.php'; 


    $conn = new mysqli($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

    function academicStanding($id) {
        $result = executeQuery(createQuery('SELECT enrollments.grade as grade, students.name as name FROM enrollments JOIN students on enrollments.student = students.id WHERE enrollments.student=?', $id, $conn), $conn);
        $row = $result->fetch_all(MYSQLI_ASSOC);
        $semGPA = 0;
        $semClasses = 0;
        $totalGPA = 0;
        $totalClasses = 0;
        foreach ($row as $grade) {
            $g = $grade['grade']
            $semClasses += 1;
            if ($g >= 90)
                $semGPA += 4;
            elseif ($g >= 80)
                $semGPA += 3;
            elseif ($g >= 70)
                $semGPA += 2;
            elseif ($g >= 60)
                $semGPA += 1;
        }
        $semGPA = $semGPA / $semClasses;
        $result1 = executeQuery(createQuery('SELECT grade FROM credits WHERE student=?', $id, $conn), $conn);
        foreach ($row1 as $grade) {
            $g = $grade['grade']
            $totalClasses += 1;
            if ($g >= 90)
                $totalGPA += 4;
            elseif ($g >= 80)
                $totalGPA += 3;
            elseif ($g >= 70)
                $totalGPA += 2;
            elseif ($g >= 60)
                $totalGPA += 1;
        }
        $totalGPA = $totalGPA / $totalClasses;


        
        
        
        $result1 = executeQuery(createQuery('SELECT classes.name AS name, enrollments.grade as grade FROM enrollments JOIN classes ON enrollments.class = classes.id WHERE enrollments.student = ?', $id, $conn), $conn)
        $row2 = $result1->fetch_all(MYSQLI_ASSOC); #reused list of classes code here
        echo("Found student classes <br>");
        

        if ($accessLevel == 'FIGURE OUT LATER') { #masks grade info from lower level advisors
            foreach ($row2 as $key => $class) {
                $class['grade'] = '*****';
                $row1[$key] = $class;
            }
        }

        echo("Name: {$row['name']} <br>")

        echo("Semester GPA: $semGPA <br>
            Total GPA: $totalGPA <br>"); 

        foreach ($row1 as $class) {
            echo("{$class['name']}, {$class['grade']} <br>");
        }
        

        
    }
    ?>
</body>
</html>