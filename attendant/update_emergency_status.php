<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['complete']) || !isset($_POST['request_id'])) {
    $_SESSION['error_message'] = "Invalid request";
    header("Location: emergency_requests.php");
    exit();
}

require_once '../dbconnect.php';

$request_id = intval($_POST['request_id']);


$check_stmt = $conn->prepare("SELECT * FROM emergency_request WHERE Request_ID = ? AND Attendant_ID = ? AND Status = 'pending'");
$check_stmt->bind_param("ii", $request_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "This request is not assigned to you or has already been completed";
    header("Location: emergency_requests.php");
    exit();
}
$check_stmt->close();


$complete_stmt = $conn->prepare("UPDATE emergency_request SET Status = 'completed', Completion_time = NOW() WHERE Request_ID = ?");
$complete_stmt->bind_param("i", $request_id);

if ($complete_stmt->execute()) {
  
    $update_status = $conn->prepare("UPDATE attendant SET Availability = 'available' WHERE User_ID = ?");
    $update_status->bind_param("i", $user_id);
    $update_status->execute();
    $update_status->close();
    
    $get_patient = $conn->prepare("SELECT Patient_ID FROM emergency_request WHERE Request_ID = ?");
    $get_patient->bind_param("i", $request_id);
    $get_patient->execute();
    $patient_id = $get_patient->get_result()->fetch_assoc()['Patient_ID'];
    $get_patient->close();
    
  
    $notify_stmt = $conn->prepare("INSERT INTO notifications (User_ID, Message, Created_At, Is_Read) 
                                  VALUES (?, 'Your emergency request has been completed. Thank you for using HomeCareBD.', NOW(), 0)");
    $notify_stmt->bind_param("i", $patient_id);
    $notify_stmt->execute();
    $notify_stmt->close();
    
    $_SESSION['success_message'] = "Emergency request marked as completed successfully.";
} else {
    $_SESSION['error_message'] = "Failed to update request: " . $conn->error;
}

$complete_stmt->close();
$conn->close();

header("Location: emergency_requests.php");
exit();
?>