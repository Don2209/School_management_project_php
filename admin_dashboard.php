<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/styles.css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <h2 class="dashboard-title">Admin Portal</h2>
        <nav>
            <ul>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-home"></i>Dashboard
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-users"></i>Students
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-chalkboard-teacher"></i>Teachers
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link">
                        <i class="fas fa-cog"></i>Settings
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="navbar">
            <h3>School Analytics Dashboard</h3>
            <div class="profile">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card">
                <div class="stat-item">
                    <i class="fas fa-users fa-3x"></i>
                    <h3>1,234</h3>
                    <p>Total Students</p>
                </div>
            </div>
            <div class="card">
                <div class="stat-item">
                    <i class="fas fa-chalkboard-teacher fa-3x"></i>
                    <h3>45</h3>
                    <p>Teaching Staff</p>
                </div>
            </div>
            <div class="card">
                <div class="stat-item">
                    <i class="fas fa-book-open fa-3x"></i>
                    <h3>15</h3>
                    <p>Active Courses</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h4>Recent Activities</h4>
            <div class="activity-list">
                <!-- Activity items with glassmorphism effect -->
            </div>
        </div>
    </div>
</body>
</html>