<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 

    require_once __DIR__ . '/main.php'; 

    function academicStanding(String $id) {
        global $accessLevel;
        global $conn;
        $accessLevel = $_SESSION['accessLevel'];
        
        echo("starting");
        $result = createQuery('SELECT enrollments.grade as grade, students.name as name FROM enrollments JOIN students on enrollments.student = students.id WHERE enrollments.student=?', $id, $conn);
        $row = $result->fetch_all(MYSQLI_ASSOC);
        if (empty($row)) {
            echo "No student records found.";
            return;
        }
        $semGPA = 0;
        $semClasses = 0;
        $totalGPA = 0;
        $totalClasses = 0;
        foreach ($row as $grade) {
            $g = $grade['grade'];
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
        if ($semClasses > 0)
            $semGPA = $semGPA / $semClasses;
        $result1 = createQuery('SELECT grade FROM credits WHERE student=?', $id, $conn);
        $row1 = $result1->fetch_all(MYSQLI_ASSOC);
        foreach ($row1 as $grade) {
            $g = $grade['grade'];
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
        if ($totalClasses > 0)
            $totalGPA = $totalGPA / $totalClasses;


        
        
        
        $result1 = createQuery('SELECT classes.name AS name, enrollments.grade as grade FROM enrollments JOIN classes ON enrollments.class = classes.id WHERE enrollments.student = ?', $id, $conn);
        $row2 = $result1->fetch_all(MYSQLI_ASSOC); #reused list of classes code here
        echo("Found student classes <br>");
        

        if ($accessLevel == 0) { #masks grade info from lower level advisors
            foreach ($row2 as $key => $class) {
                $class['grade'] = '*****';
                $row2[$key] = $class;
            }
        }

        echo("Name: {$row[0]['name']} <br>");

        echo("Semester GPA: $semGPA <br>
            Total GPA: $totalGPA <br>"); 
        
        if ($totalGPA < 2.0)
            echo("Student is on academic probation <br>");
        else
            echo("Student is in good academic standing <br>");

        foreach ($row2 as $class) {
            echo("{$class['name']}, {$class['grade']} <br>");
        }
        

        
    }
    ?>
</body>
</html>