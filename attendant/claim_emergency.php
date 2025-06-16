<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    error_log("Session check failed: user_id=" . ($_SESSION['user_id'] ?? 'unset') . ", role=" . ($_SESSION['role'] ?? 'unset'));
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

require_once '../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim'])) {
    $request_id = $_POST['request_id'] ?? '';

    $conn->begin_transaction();
    try {
        $check_stmt = $conn->prepare("SELECT Attendant_ID FROM emergency_request WHERE Request_ID = ? AND Status = 'pending' FOR UPDATE");
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $request = $result->fetch_assoc();
        $check_stmt->close();

        if ($request && $request['Attendant_ID'] === null) {
            $update_stmt = $conn->prepare("UPDATE emergency_request SET Attendant_ID = ? WHERE Request_ID = ?");
            $update_stmt->bind_param("ii", $user_id, $request_id);
            $update_stmt->execute();
            $update_stmt->close();
            $conn->commit();
            $success_message = "Emergency request claimed successfully!";
            error_log("Request claimed: Request_ID=$request_id, Attendant_ID=$user_id");
        } else {
            $conn->rollback();
            $error_message = "Request is no longer available or has been assigned.";
            error_log("Claim failed: Request_ID=$request_id, Reason=Already assigned or invalid");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
        error_log("Claim error: Request_ID=$request_id, Error=" . $e->getMessage());
    }
}

$conn->close();

$redirect_url = "dashboard.php";
if (isset($success_message)) {
    $redirect_url .= "?msg=" . urlencode($success_message);
} elseif (isset($error_message)) {
    $redirect_url .= "?error=" . urlencode($error_message);
}
header("Location: $redirect_url");
exit();
?>