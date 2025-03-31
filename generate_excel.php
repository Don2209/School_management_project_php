<?php
require 'dbconfig.php';
require 'vendor/autoload.php'; // Include PHPExcel library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = $_POST['class_id'];
    $subjectId = $_POST['subject_id'];

    // Fetch students in the selected class
    $query = "
        SELECT students.name 
        FROM student_classes 
        JOIN students ON student_classes.student_id = students.id 
        WHERE student_classes.class_id = $classId";
    $result = $conn->query($query);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Student Name');
    $sheet->setCellValue('B1', 'Marks');
    $sheet->setCellValue('C1', 'Grade');

    $row = 2;
    while ($student = $result->fetch_assoc()) {
        $sheet->setCellValue("A$row", $student['name']);
        $row++;
    }

    $fileName = "class_${classId}_subject_${subjectId}_template.xlsx";
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
