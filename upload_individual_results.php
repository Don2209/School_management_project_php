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

// Fetch terms
$termQuery = "SELECT id, term_name FROM school_terms";
$termResult = $conn->query($termQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $term_id = $_POST['term_id']; // Capture the selected term
    $marks = $_POST['marks'];

    foreach ($marks as $student_id => $mark) {
        $grade = calculateGrade($mark);
        $query = "
            INSERT INTO results (student_id, subject_id, teacher_id, marks, grade, term_id) 
            VALUES ($student_id, $subject_id, $teacher_id, $mark, '$grade', $term_id)
            ON DUPLICATE KEY UPDATE marks = $mark, grade = '$grade', term_id = $term_id";
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
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: #ecf0f1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            border-right: 3px solid #4ecdc4;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 2em;
            font-weight: bold;
            text-align: center;
            color: #4ecdc4;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
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
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .sidebar nav ul li a i {
            font-size: 1.5em;
            color: #ecf0f1;
            transition: color 0.3s;
        }

        .sidebar nav ul li a:hover i {
            color: #fff;
        }

        .sidebar nav ul li a:hover {
            background: #4ecdc4;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f4f4f4;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #4ecdc4, #3bb0a1);
            padding: 15px 20px;
            border-radius: 10px;
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .navbar h3 {
            font-size: 1.8em;
            font-weight: bold;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
        }

        .profile {
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile i {
            font-size: 2.5em;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            color: #4ecdc4;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .form-group select, .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
        }

        .student-list {
            margin-top: 30px;
        }

        .student-list table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .student-list th, .student-list td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
            font-size: 1em;
        }

        .student-list th {
            background: #4ecdc4;
            color: #fff;
            font-size: 1.1em;
        }

        .student-list tr:nth-child(even) {
            background: #f9f9f9;
        }

        .student-list tr:hover {
            background: #f1f1f1;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #4ecdc4;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s, transform 0.3s;
        }

        .btn:hover {
            background: #3bb0a1;
            transform: scale(1.05);
        }

        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: bold;
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
                        <i class="fas fa-home"></i>Dashboard
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
                <li class="menu-item">
                    <a href="logout.php" class="menu-link">
                        <i class="fas fa-sign-out-alt"></i>Logout
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
                <div class="form-group">
                    <label for="termSelect">Select Term:</label>
                    <select name="term_id" id="termSelect" required>
                        <option value="">-- Select Term --</option>
                        <?php while ($term = $termResult->fetch_assoc()): ?>
                            <option value="<?php echo $term['id']; ?>"><?php echo $term['term_name']; ?></option>
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
