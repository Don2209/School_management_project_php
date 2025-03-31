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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 2em;
            font-weight: bold;
            text-align: center;
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
            transition: background 0.3s, transform 0.3s;
        }

        .sidebar nav ul li a:hover {
            background: #34495e;
            transform: scale(1.05);
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
            background: #4ecdc4;
            padding: 15px 20px;
            border-radius: 10px;
            color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar h3 {
            font-size: 1.8em;
            font-weight: bold;
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

        .stats {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            flex: 1;
            background: linear-gradient(135deg, #ff6b6b, #f7b733);
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .stat-card h3 {
            font-size: 2.5em;
            margin: 0;
        }

        .stat-card p {
            margin: 5px 0 0;
            font-size: 1.2em;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .class-card {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
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
            transition: background 0.3s, transform 0.3s;
            font-size: 1em;
        }

        .action-btn:hover {
            background: #4ecdc4;
            transform: scale(1.05);
        }

        canvas {
            max-width: 100%;
        }

        .modal {
            position: fixed;
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust for modal size */
            width: 400px; /* Set a fixed width for the modal */
            background: rgba(255, 255, 255, 1); /* Solid white background */
            z-index: 1000; /* Ensure it overlays everything */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add a shadow for better visibility */
            border-radius: 10px;
            padding: 20px;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
            z-index: 999; /* Ensure it is below the modal but above everything else */
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
            font-weight: bold;
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

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo count($assigned_classes); ?></h3>
                <p>Assigned Classes</p>
            </div>
            <div class="stat-card">
                <h3>85%</h3>
                <p>Average Pass Rate</p>
            </div>
            <div class="stat-card">
                <h3>Term 1</h3>
                <p>Current Term</p>
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
                    <div class="dropdown-content" id="exportDropdown" style="display: none;">
                        <form method="POST" action="export_excel.php">
                            <div class="form-group">
                                <label for="termSelect">Select Term:</label>
                                <select name="term_id" id="termSelect" required>
                                    <?php
                                    $termQuery = "SELECT id, term_name FROM school_terms";
                                    $termResult = $conn->query($termQuery);
                                    while ($term = $termResult->fetch_assoc()) {
                                        echo "<option value='{$term['id']}'>{$term['term_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <input type="hidden" name="export_type" value="excel">
                            <button type="submit" class="btn action-btn">Download as Excel</button>
                        </form>
                        <form method="POST" action="export_slips.php">
                            <div class="form-group">
                                <label for="termSelectSlips">Select Term:</label>
                                <select name="term_id" id="termSelectSlips" required>
                                    <?php
                                    $termResult->data_seek(0); // Reset the result pointer
                                    while ($term = $termResult->fetch_assoc()) {
                                        echo "<option value='{$term['id']}'>{$term['term_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
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
                <h4>Select Class, Subject, and Term</h4>
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
                    <div class="form-group">
                        <label for="termSelect">Term:</label>
                        <select name="term_id" id="termSelect" required>
                            <?php
                            $termQuery = "SELECT id, term_name FROM school_terms";
                            $termResult = $conn->query($termQuery);
                            while ($term = $termResult->fetch_assoc()) {
                                echo "<option value='{$term['id']}'>{$term['term_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn action-btn">Download Excel Template</button>
                </form>
            </div>
        </div>
        <div id="modalOverlay" class="modal-overlay" style="display: none;"></div>

        <div class="card">
            <h4>Progress Overview</h4>
            <canvas id="progressChart" width="400" height="200"></canvas>
        </div>

        <script>
            // Show modal
            document.getElementById('importResultsBtn').addEventListener('click', function () {
                document.getElementById('importResultsModal').style.display = 'block';
                document.getElementById('modalOverlay').style.display = 'block';
            });

            // Close modal
            document.getElementById('closeModal').addEventListener('click', function () {
                document.getElementById('importResultsModal').style.display = 'none';
                document.getElementById('modalOverlay').style.display = 'none';
            });

            // Close modal when clicking outside
            document.getElementById('modalOverlay').addEventListener('click', function () {
                document.getElementById('importResultsModal').style.display = 'none';
                document.getElementById('modalOverlay').style.display = 'none';
            });

            // Toggle dropdown for export options
            document.getElementById('exportResultsBtn').addEventListener('click', function (event) {
                event.stopPropagation(); // Prevent event from propagating to the window
                const dropdownContent = document.getElementById('exportDropdown');
                dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
            });

            // Close dropdown when clicking outside
            window.addEventListener('click', function () {
                const dropdownContent = document.getElementById('exportDropdown');
                if (dropdownContent) dropdownContent.style.display = 'none';
            });

            // Fetch data for the chart
            fetch('fetch_performance_data.php')
                .then(response => response.json())
                .then(data => {
                    const labels = data.subjects;
                    const malePassRates = data.malePassRates;
                    const femalePassRates = data.femalePassRates;

                    const chartData = {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Male Pass Rate (%)',
                                data: malePassRates,
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Female Pass Rate (%)',
                                data: femalePassRates,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }
                        ]
                    };

                    const config = {
                        type: 'bar',
                        data: chartData,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Subject Performance by Gender (Pass Rate)'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    title: {
                                        display: true,
                                        text: 'Pass Rate (%)'
                                    }
                                }
                            }
                        }
                    };

                    // Render the chart
                    const progressChart = new Chart(
                        document.getElementById('progressChart'),
                        config
                    );
                });
        </script>
    </div>
</body>
</html>