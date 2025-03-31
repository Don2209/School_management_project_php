<?php
session_start();
include 'dbconfig.php';

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'teachers') {
    header('Location: index.php');
    exit();
}

// Fetch teacher's assigned classes
$teacher_id = $_SESSION['user']['id'];
$query = "
    SELECT subjects.name AS subject_name 
    FROM teacher_class_subjects 
    JOIN subjects ON teacher_class_subjects.subject_id = subjects.id 
    WHERE teacher_class_subjects.teacher_id = $teacher_id";
$result = $conn->query($query);

$assigned_classes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $assigned_classes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .class-card {
            background: linear-gradient(135deg, #ff6b6b, #f7b733);
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .result-form .form-group {
            margin-bottom: 15px;
        }

        .result-form input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .result-form .btn {
            background: #4ecdc4;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .result-form .btn:hover {
            background: #3bb0a1;
        }

        .result-actions {
            display: flex;
            justify-content: center; /* Center the buttons horizontally */
            gap: 10px;
            margin-top: 10px;
        }

        .action-btn {
            background: #556270;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .action-btn:hover {
            background: #4ecdc4;
        }

        canvas {
            max-width: 100%;
        }

        .modal {
            position: fixed;
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust for modal size */
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            padding-left: 500px;
            padding-top: 90px;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            position: absolute;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1;
            padding: 10px;
            border-radius: 5px;
        }

        .dropdown-content form {
            margin: 0;
            padding: 5px 0;
        }

        .dropdown-content button {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 10px;
            cursor: pointer;
            color: #333;
            font-size: 1em;
        }

        .dropdown-content button:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="dashboard-title">Teacher Portal</h2>
        <nav>
            <ul>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-chalkboard"></i>Classes
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

        <div class="card">
            <h4>Your Classes</h4>
            <div class="class-grid">
                <?php if (!empty($assigned_classes)): ?>
                    <?php foreach ($assigned_classes as $class): ?>
                        <div class="class-card">
                            <h5>Subject: <?php echo $class['subject_name']; ?></h5>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No classes assigned yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="result-actions">
                <button class="btn action-btn" id="importResultsBtn">Import Results</button>
                <div class="dropdown">
                    <button class="btn action-btn" id="exportResultsBtn">Export Results</button>
                    <div class="dropdown-content" style="display: none;">
                        <form method="POST" action="export_excel.php">
                            <input type="hidden" name="export_type" value="excel">
                            <button type="submit" class="btn action-btn">Download as Excel</button>
                        </form>
                        <form method="POST" action="export_slips.php">
                            <input type="hidden" name="export_type" value="slips">
                            <button type="submit" class="btn action-btn">Download Result Slips</button>
                        </form>
                    </div>
                </div>
                <button class="btn action-btn">Generate Graph Reports</button>
            </div>
        </div>

        <!-- Modal for Import Results -->
        <div id="importResultsModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-btn" id="closeModal">&times;</span>
                <h4>Select Class and Subject</h4>
                <form id="importResultsForm" method="POST" action="generate_excel.php">
                    <div class="form-group">
                        <label for="classSelect">Class:</label>
                        <select name="class_id" id="classSelect" required>
                            <?php
                            $classQuery = "SELECT id, name FROM classes";
                            $classResult = $conn->query($classQuery);
                            while ($class = $classResult->fetch_assoc()) {
                                echo "<option value='{$class['id']}'>{$class['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subjectSelect">Subject:</label>
                        <select name="subject_id" id="subjectSelect" required>
                            <?php
                            $subjectQuery = "
                                SELECT DISTINCT subjects.id, subjects.name 
                                FROM teacher_class_subjects 
                                JOIN subjects ON teacher_class_subjects.subject_id = subjects.id 
                                WHERE teacher_class_subjects.teacher_id = $teacher_id";
                            $subjectResult = $conn->query($subjectQuery);
                            while ($subject = $subjectResult->fetch_assoc()) {
                                echo "<option value='{$subject['id']}'>{$subject['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn action-btn">Download Excel Template</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h4>Progress Overview</h4>
            <canvas id="progressChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        // Show modal
        document.getElementById('importResultsBtn').addEventListener('click', function () {
            document.getElementById('importResultsModal').style.display = 'block';
        });

        // Close modal
        document.getElementById('closeModal').addEventListener('click', function () {
            document.getElementById('importResultsModal').style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            if (event.target === document.getElementById('importResultsModal')) {
                document.getElementById('importResultsModal').style.display = 'none';
            }
        });

        // Toggle dropdown for export options
        document.getElementById('exportResultsBtn').addEventListener('click', function () {
            const dropdownContent = document.querySelector('.dropdown-content');
            dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown when clicking outside
        window.addEventListener('click', function (event) {
            if (!event.target.matches('#exportResultsBtn')) {
                const dropdownContent = document.querySelector('.dropdown-content');
                if (dropdownContent) dropdownContent.style.display = 'none';
            }
        });

        // Sample data for the chart
        const labels = ['Class A', 'Class B', 'Class C', 'Class D'];
        const data = {
            labels: labels,
            datasets: [{
                label: 'Average Scores',
                data: [85, 90, 78, 88],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Class Performance Overview'
                    }
                }
            },
        };

        // Render the chart
        const progressChart = new Chart(
            document.getElementById('progressChart'),
            config
        );
    </script>
</body>
</html>