<?php
require 'dbconfig.php';
require 'vendor/autoload.php'; // Include PHPExcel library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['export_type'] === 'excel') {
    $teacherId = $_SESSION['user']['id'];
    $termId = $_POST['term_id'];

    // Fetch the term name
    $termQuery = "SELECT term_name FROM school_terms WHERE id = $termId";
    $termResult = $conn->query($termQuery);
    $term = $termResult->fetch_assoc()['term_name'];

    // Fetch results for the teacher for the selected term
    $query = "
        SELECT students.name AS student_name, subjects.name AS subject_name, results.marks, results.grade 
        FROM results
        JOIN students ON results.student_id = students.id
        JOIN subjects ON results.subject_id = subjects.id
        WHERE results.teacher_id = $teacherId AND results.term_id = $termId";
    $result = $conn->query($query);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Term: ' . $term); // Prefill the term name
    $sheet->setCellValue('A2', 'Student Name');
    $sheet->setCellValue('B2', 'Subject');
    $sheet->setCellValue('C2', 'Marks');
    $sheet->setCellValue('D2', 'Grade');

    $row = 3;
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue("A$row", $data['student_name']);
        $sheet->setCellValue("B$row", $data['subject_name']);
        $sheet->setCellValue("C$row", $data['marks']);
        $sheet->setCellValue("D$row", $data['grade']);
        $row++;
    }

    $fileName = "teacher_results_term_${termId}.xlsx";
    $writer = new Xlsx($spreadsheet);
    $writer->save($fileName);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=$fileName");
    readfile($fileName);

    // Delete the file after download
    unlink($fileName);
    exit();
}
?>
