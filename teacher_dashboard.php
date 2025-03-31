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
    FROM teacher_subjects 
    JOIN subjects ON teacher_subjects.subject_id = subjects.id 
    WHERE teacher_subjects.teacher_id = $teacher_id";
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
                    <a href="#" class="menu-link">
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
                            <p>Subject: <?php echo $class['subject_name']; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No classes assigned yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h4>Enter Results</h4>
            <form class="result-form">
                <div class="form-group">
                    <input type="text" placeholder="Student ID" class="glass-input">
                </div>
                <button class="btn">Submit Results</button>
            </form>
        </div>

        <div class="card">
            <h4>Progress Overview</h4>
            <canvas id="progressChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
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