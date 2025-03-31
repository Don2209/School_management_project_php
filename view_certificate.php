<?php
session_start();
require 'dbconfig.php';

// Check if the user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'students') {
    header('Location: index.php');
    exit();
}

$student_id = $_SESSION['user']['id'];
$term_id = $_POST['term_id'];
$year = $_POST['year'];

// Fetch the term name and validate the year
$termQuery = "SELECT term_name FROM school_terms WHERE id = $term_id AND YEAR(start_date) = $year";
$termResult = $conn->query($termQuery);
if ($termResult->num_rows === 0) {
    die("Invalid term or year selected.");
}
$term = $termResult->fetch_assoc()['term_name'];

// Fetch student details
$studentQuery = "SELECT name FROM students WHERE id = $student_id";
$studentResult = $conn->query($studentQuery);
$studentName = $studentResult->fetch_assoc()['name'];

// Fetch results for the student for the selected term and year
$query = "
    SELECT subjects.name AS subject_name, results.marks, results.grade 
    FROM results 
    JOIN subjects ON results.subject_id = subjects.id 
    JOIN school_terms ON results.term_id = school_terms.id
    WHERE results.student_id = $student_id AND results.term_id = $term_id AND YEAR(school_terms.start_date) = $year";
$result = $conn->query($query);

$results = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Certificate</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f4f4f4;
            color: #333;
        }

        .certificate {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 2px solid #4ecdc4;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .certificate h1 {
            font-size: 2em;
            margin-bottom: 10px;
            color: #4ecdc4;
        }

        .certificate h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .certificate p {
            font-size: 1em;
            margin-bottom: 20px;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .results-table th, .results-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .results-table th {
            background: #4ecdc4;
            color: #fff;
        }

        .print-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #4ecdc4;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .print-btn:hover {
            background: #3bb0a1;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>Result Certificate</h1>
        <h2><?php echo $term . " (" . $year . ")"; ?></h2>
        <p><strong>Student Name:</strong> <?php echo $studentName; ?></p>
        <?php if (!empty($results)): ?>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Marks</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo $result['subject_name']; ?></td>
                            <td><?php echo $result['marks']; ?></td>
                            <td><?php echo $result['grade']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button class="print-btn" onclick="window.print()">Print Certificate</button>
        <?php else: ?>
            <p>No results available for this term and year.</p>
        <?php endif; ?>
    </div>
</body>
</html>
