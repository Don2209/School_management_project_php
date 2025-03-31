<?php
require 'dbconfig.php';
session_start();

$teacher_id = $_SESSION['user']['id'];

// Fetch the current term
$currentTermQuery = "SELECT id FROM school_terms ORDER BY start_date DESC LIMIT 1";
$currentTermResult = $conn->query($currentTermQuery);
$currentTerm = $currentTermResult->fetch_assoc();
$currentTermId = $currentTerm['id'];

// Fetch subjects assigned to the teacher for the current term
$subjectQuery = "
    SELECT DISTINCT subjects.id, subjects.name 
    FROM teacher_class_subjects 
    JOIN subjects ON teacher_class_subjects.subject_id = subjects.id 
    WHERE teacher_class_subjects.teacher_id = $teacher_id";
$subjectResult = $conn->query($subjectQuery);

$subjects = [];
$malePassRates = [];
$femalePassRates = [];

while ($subject = $subjectResult->fetch_assoc()) {
    $subjectId = $subject['id'];
    $subjects[] = $subject['name'];

    // Calculate male pass rate for the current term
    $maleQuery = "
        SELECT COUNT(*) AS total, 
               SUM(CASE WHEN marks >= 50 THEN 1 ELSE 0 END) AS passed 
        FROM results 
        JOIN students ON results.student_id = students.id 
        WHERE results.subject_id = $subjectId 
          AND students.Gender = 'Male'
          AND results.term_id = $currentTermId";
    $maleResult = $conn->query($maleQuery)->fetch_assoc();
    $malePassRate = $maleResult['total'] > 0 ? ($maleResult['passed'] / $maleResult['total']) * 100 : 0;
    $malePassRates[] = round($malePassRate, 2);

    // Calculate female pass rate for the current term
    $femaleQuery = "
        SELECT COUNT(*) AS total, 
               SUM(CASE WHEN marks >= 50 THEN 1 ELSE 0 END) AS passed 
        FROM results 
        JOIN students ON results.student_id = students.id 
        WHERE results.subject_id = $subjectId 
          AND students.Gender = 'Female'
          AND results.term_id = $currentTermId";
    $femaleResult = $conn->query($femaleQuery)->fetch_assoc();
    $femalePassRate = $femaleResult['total'] > 0 ? ($femaleResult['passed'] / $femaleResult['total']) * 100 : 0;
    $femalePassRates[] = round($femalePassRate, 2);
}

echo json_encode([
    'subjects' => $subjects,
    'malePassRates' => $malePassRates,
    'femalePassRates' => $femalePassRates
]);
?>
