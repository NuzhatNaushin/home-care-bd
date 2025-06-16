<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    error_log("Access denied to process_application.php: User ID " . ($_SESSION['user_id'] ?? 'unset') . ", Role " . ($_SESSION['role'] ?? 'unset'));
    header("Location: ../index.php"); 
    exit();
}

$attendant_id = $_SESSION['user_id'];


require_once '../dbconnect.php';


if ($conn->connect_error) {
    error_log("Database connection failed in process_application.php: " . $conn->connect_error);
    $_SESSION['error_message'] = "Database connection failed. Please try again later."; 
    header("Location: job_details.php"); 
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'], $_POST['job_id'])) {
    $job_id = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT); 

   
    if ($job_id === false || $job_id <= 0) {
        $_SESSION['error_message'] = "Invalid job ID provided.";
        header("Location: job_details.php"); 
        exit();
    }



    
    $sql_check_applied = "SELECT Application_ID FROM job_application WHERE Job_ID = ? AND Attendant_ID = ?";
    $stmt_check_applied = $conn->prepare($sql_check_applied);
    if ($stmt_check_applied) {
        $stmt_check_applied->bind_param("ii", $job_id, $attendant_id);
        $stmt_check_applied->execute();
        $stmt_check_applied->store_result();

        if ($stmt_check_applied->num_rows > 0) {
        
            $_SESSION['error_message'] = "You have already applied for this job.";
            header("Location: job_details.php"); 
            exit();
        }
        $stmt_check_applied->close();
    } else {
      
        $_SESSION['error_message'] = "Database error: Could not prepare check statement.";
        error_log("Prepare check statement failed in process_application.php: " . $conn->error); 
        header("Location: job_details.php"); 
        exit();
    }



    $sql_insert_application = "INSERT INTO job_application (Job_ID, Attendant_ID, Application_Date, Status) VALUES (?, ?, NOW(), 'pending')";
    $stmt_insert = $conn->prepare($sql_insert_application);

    if ($stmt_insert) {
        // Bind the parameters and execute the statement
        $stmt_insert->bind_param("ii", $job_id, $attendant_id);

        if ($stmt_insert->execute()) {
            

            $_SESSION['success_message'] = "Application submitted successfully. Waiting for admin approval.";

            header("Location: my_applications.php");
            exit();
        } else {
           
            $_SESSION['error_message'] = "Error submitting application: " . $stmt_insert->error;
            error_log("Job application insert failed in process_application.php: " . $stmt_insert->error); 
            header("Location: job_details.php"); 
        }
        $stmt_insert->close();
    } else {

        $_SESSION['error_message'] = "Database error: Could not prepare application statement.";
        error_log("Prepare statement failed in process_application.php: " . $conn->error); 
        header("Location: job_details.php"); 
    }

    $conn->close(); 
} else {

    $_SESSION['error_message'] = "Invalid request method or missing data.";
    header("Location: job_details.php"); 
    exit();
}
?>