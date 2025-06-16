<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to catch stray output
ob_start();

session_start();

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    error_log("Session check failed: user_id=" . ($_SESSION['user_id'] ?? 'unset') . ", role=" . ($_SESSION['role'] ?? 'unset'));
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require_once '../dbconnect.php';

// Verify database connection
if (!$conn || $conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed: " . $conn->connect_error);
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_emergency'])) {
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate inputs
    if (empty($location) || empty($description)) {
        $error_message = 'empty_fields';
        error_log("Validation failed: empty location or description");
    } else {
        // Prepare insert query
        $stmt = $conn->prepare("INSERT INTO emergency_request (Patient_ID, Attendant_ID, Request_time, Description, Location, Status)
                                VALUES (?, NULL, NOW(), ?, ?, 'pending')");
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $description, $location);
            if ($stmt->execute()) {
                $success_message = "Emergency request submitted successfully!";
                error_log("Emergency request inserted: Patient_ID=$user_id, Location=$location");
            } else {
                $error_message = "insert_failed";
                error_log("Insert failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $error_message = "Database error: Could not prepare statement. " . $conn->error;
            error_log("Prepare failed: " . $conn->error);
        }
    }
}

// Close database connection
$conn->close();

// Clear output buffer
ob_end_clean();

// Redirect back to emergency.php with message
$redirect_url = "emergency.php";
if ($success_message) {
    $redirect_url .= "?success=" . urlencode($success_message);
} elseif ($error_message) {
    $redirect_url .= "?error=" . urlencode($error_message);
}
header("Location: $redirect_url");
exit();
?>