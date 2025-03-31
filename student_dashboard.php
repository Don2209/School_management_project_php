<?php
session_start();
include 'dbconfig.php';

// Check if the user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user_type'] !== 'students') {
    header('Location: index.php');
    exit();
}

$student_id = $_SESSION['user']['id'];

// Fetch the current term
$currentTermQuery = "SELECT id, term_name FROM school_terms ORDER BY start_date DESC LIMIT 1";
$currentTermResult = $conn->query($currentTermQuery);
$currentTerm = $currentTermResult->fetch_assoc();
$currentTermId = $currentTerm['id'];

// Fetch results for the logged-in student for the current term
$query = "
    SELECT subjects.name AS subject_name, results.marks, results.grade 
    FROM results 
    JOIN subjects ON results.subject_id = subjects.id 
    WHERE results.student_id = $student_id AND results.term_id = $currentTermId";
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
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4ecdc4;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s, transform 0.3s;
        }

        .btn:hover {
            background: #3bb0a1;
            transform: scale(1.05);
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

        .results-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .results-table tr:hover {
            background: #f1f1f1;
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
            font-size: 2em;
            margin: 0;
        }

        .stat-card p {
            margin: 5px 0 0;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="dashboard-title">Student Portal</h2>
        <nav>
            <ul>
                <li class="menu-item">
                    <a href="student_dashboard.php" class="menu-link">
                        <i class="fas fa-chart-line"></i>View Results
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
                <h3><?php echo count($results); ?></h3>
                <p>Subjects</p>
            </div>
            <div class="stat-card">
                <h3><?php echo array_sum(array_column($results, 'marks')) / max(count($results), 1); ?></h3>
                <p>Average Marks</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $currentTerm['term_name']; ?></h3>
                <p>Current Term</p>
            </div>
        </div>

        <div class="card">
            <h4>Select Term and Year to View Results</h4>
            <form method="POST" action="view_certificate.php" target="_blank" style="margin-bottom: 20px;">
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
                <div class="form-group">
                    <label for="yearSelect">Select Year:</label>
                    <select name="year" id="yearSelect" required>
                        <?php
                        $yearQuery = "SELECT DISTINCT YEAR(start_date) AS year FROM school_terms ORDER BY year DESC";
                        $yearResult = $conn->query($yearQuery);
                        while ($year = $yearResult->fetch_assoc()) {
                            echo "<option value='{$year['year']}'>{$year['year']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn">View and Print Certificate</button>
            </form>
        </div>

        <div class="card">
            <h4>Your Results (<?php echo $currentTerm['term_name']; ?>)</h4>
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
                <form method="POST" action="view_certificate.php" target="_blank" style="margin-top: 20px;">
                    <input type="hidden" name="term_id" value="<?php echo $currentTermId; ?>">
                    <button type="submit" class="btn">View Certificate</button>
                </form>
            <?php else: ?>
                <p>No results available for this term.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
