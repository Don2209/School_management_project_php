<?php
session_start();
include 'dbconfig.php';

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'teachers') {
    header('Location: index.php');
    exit();
}

$teacher_id = $_SESSION['user']['id'];

// Fetch classes assigned to the teacher
$classQuery = "
    SELECT DISTINCT classes.id, classes.name 
    FROM teacher_class_subjects 
    JOIN classes ON teacher_class_subjects.class_id = classes.id 
    WHERE teacher_class_subjects.teacher_id = $teacher_id";
$classResult = $conn->query($classQuery);

// Fetch subjects assigned to the teacher
$subjectQuery = "
    SELECT DISTINCT subjects.id, subjects.name 
    FROM teacher_class_subjects 
    JOIN subjects ON teacher_class_subjects.subject_id = subjects.id 
    WHERE teacher_class_subjects.teacher_id = $teacher_id";
$subjectResult = $conn->query($subjectQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $marks = $_POST['marks'];

    foreach ($marks as $student_id => $mark) {
        $grade = calculateGrade($mark);
        $query = "
            INSERT INTO results (student_id, subject_id, teacher_id, marks, grade, term_id) 
            VALUES ($student_id, $subject_id, $teacher_id, $mark, '$grade', 1)
            ON DUPLICATE KEY UPDATE marks = $mark, grade = '$grade'";
        $conn->query($query);
    }

    $successMessage = "Results uploaded successfully!";
}

// Function to calculate grade based on marks
function calculateGrade($marks) {
    if ($marks >= 90) return 'A+';
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';
    return 'F';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Individual Results</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background: linear-gradient(135deg, #4ecdc4, #556270);
            color: #333;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        .sidebar nav ul li {
            margin: 15px 0;
        }

        .sidebar nav ul li a {
            text-decoration: none;
            color: #ecf0f1;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .sidebar nav ul li a:hover {
            background: #34495e;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .navbar h3 {
            color: #fff;
        }

        .profile {
            color: #fff;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .student-list {
            margin-top: 20px;
        }

        .student-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-list th, .student-list td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        .student-list th {
            background: #f4f4f4;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #4ecdc4;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            font-size: 1em;
        }

        .btn:hover {
            background: #3bb0a1;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="dashboard-title">Teacher Portal</h2>
        <nav>
            <ul>
                <li class="menu-item">
                    <a href="teacher_dashboard.php" class="menu-link">
                        <i class="fas fa-chalkboard"></i>Dashboard
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-clipboard-list"></i>Attendance
                    </a>
                </li>
                <li class="menu-item">
                    <a href="upload_individual_results.php" class="menu-link">
                        <i class="fas fa-chart-line"></i>Results
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="navbar">
            <h3>Welcome Back, <?php echo $_SESSION['user']['name']; ?></h3>
            <div class="profile">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
        </div>

        <div class="container">
            <h2>Upload Individual Results</h2>
            <?php if (isset($successMessage)): ?>
                <p class="success-message"><?php echo $successMessage; ?></p>
                <div class="student-list">
                    <h3>Submitted Results</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Marks</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($marks)) {
                                foreach ($marks as $student_id => $mark) {
                                    $studentQuery = "SELECT name FROM students WHERE id = $student_id";
                                    $studentResult = $conn->query($studentQuery);
                                    $student = $studentResult->fetch_assoc();
                                    $grade = calculateGrade($mark);
                                    echo "<tr>
                                            <td>{$student['name']}</td>
                                            <td>$mark</td>
                                            <td>$grade</td>
                                          </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="classSelect">Select Class:</label>
                    <select name="class_id" id="classSelect" required>
                        <option value="">-- Select Class --</option>
                        <?php while ($class = $classResult->fetch_assoc()): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo $class['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subjectSelect">Select Subject:</label>
                    <select name="subject_id" id="subjectSelect" required>
                        <option value="">-- Select Subject --</option>
                        <?php while ($subject = $subjectResult->fetch_assoc()): ?>
                            <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="student-list">
                    <h3>Students</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Marks</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <!-- Student rows will be dynamically populated -->
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn">Submit Results</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('classSelect').addEventListener('change', function () {
            const classId = this.value;
            const subjectId = document.getElementById('subjectSelect').value;

            if (classId && subjectId) {
                fetch(`fetch_students.php?class_id=${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        const tableBody = document.getElementById('studentTableBody');
                        tableBody.innerHTML = '';
                        data.forEach(student => {
                            const row = `
                                <tr>
                                    <td>${student.name}</td>
                                    <td><input type="number" name="marks[${student.id}]" min="0" max="100" required></td>
                                </tr>
                            `;
                            tableBody.innerHTML += row;
                        });
                    });
            }
        });

        document.getElementById('subjectSelect').addEventListener('change', function () {
            document.getElementById('classSelect').dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>
