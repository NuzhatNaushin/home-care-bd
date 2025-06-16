<?php

session_start();


if (!isset($_SESSION['user_id'])) {
    // login na korle redirect 
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$email = $_SESSION['email'];
$role = $_SESSION['role'];

// Db conn
$conn = new mysqli('localhost', 'root', '', 'homecarebd');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

switch ($role) {
    case 'admin':      
        header("Location: admin/dashboard.php");
        exit();
    case 'patient':
        header("Location: patient/dashboard.php");
        exit();
    case 'attendant':
        header("Location: attendant/dashboard.php");
        exit();
    default:
        //
        session_destroy();
        header("Location: index.php?error=invalid_role");
        exit();
}

$conn->close();
?>