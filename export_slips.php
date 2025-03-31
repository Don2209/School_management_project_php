<?php
require 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['export_type'] === 'slips') {
    $teacherId = $_SESSION['user']['id'];

    // Fetch results for the teacher
    $query = "
        SELECT students.name AS student_name, subjects.name AS subject_name, results.marks, results.grade 
        FROM results
        JOIN students ON results.student_id = students.id
        JOIN subjects ON results.subject_id = subjects.id
        WHERE results.teacher_id = $teacherId";
    $result = $conn->query($query);

    while ($data = $result->fetch_assoc()) {
        $studentName = $data['student_name'];
        $subjectName = $data['subject_name'];
        $marks = $data['marks'];
        $grade = $data['grade'];

        // Generate a PDF for each student
        $html = "
            <h1>Result Slip</h1>
            <p><strong>Student Name:</strong> $studentName</p>
            <p><strong>Subject:</strong> $subjectName</p>
            <p><strong>Marks:</strong> $marks</p>
            <p><strong>Grade:</strong> $grade</p>
        ";

        $pdf = new \Mpdf\Mpdf();
        $pdf->WriteHTML($html);
        $pdf->Output("$studentName-result-slip.pdf", 'D');
    }
    exit();
}
?>
