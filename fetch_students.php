<?php
require 'dbconfig.php';

if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];

    $query = "
        SELECT students.id, students.name 
        FROM student_classes 
        JOIN students ON student_classes.student_id = students.id 
        WHERE student_classes.class_id = $class_id";
    $result = $conn->query($query);

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    echo json_encode($students);
}
?>
