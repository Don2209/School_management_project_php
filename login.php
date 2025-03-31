<?php
session_start();
include 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    $roles = ['admins', 'teachers', 'students'];
    $user = null;
    $user_type = null;

    foreach ($roles as $role) {
        $query = "SELECT * FROM $role WHERE email = '$email' AND password = '$password'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_type = $role;
            break;
        }
    }

    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['user_type'] = $user_type;

        if ($user_type === 'admin') {
            header('Location: admin_dashboard.php');
        } elseif ($user_type === 'teachers') {
            header('Location: teacher_dashboard.php');
        } elseif ($user_type === 'students') {
            header('Location: student_dashboard.php');
        }
        exit();
    } else {
        echo "Invalid email or password.";
    }
}
$conn->close();
?>
