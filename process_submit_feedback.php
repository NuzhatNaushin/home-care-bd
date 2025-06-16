<?php
session_start();

// Check if the user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: index.php"); // Redirect to login if not logged in as patient
    exit();
}

// Include the database connection file from the same directory
require_once 'dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $reviewer_id = $_SESSION['user_id']; // Patient's User_ID from session
    $reviewee_id = $_POST['reviewee'] ?? null; // Attendant's User_ID from the form
    $rating = $_POST['rating'] ?? null;
    $comment = trim($_POST['comment'] ?? '');

    // Validate data
    if (empty($reviewee_id) || empty($rating)) {
        // Redirect back to the form with an error message
        header("Location: patient/submit_feedback.php?error=empty_feedback_fields");
        exit();
    }

    // Validate rating
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        // Redirect back to the form with an error message
        header("Location: patient/submit_feedback.php?error=invalid_rating");
        exit();
    }

    // Prepare and execute the SQL statement to insert feedback
    $stmt = $conn->prepare("INSERT INTO feedback (Reviewer_ID, Reviewee_ID, Rating, Comment) VALUES (?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("iiis", $reviewer_id, $reviewee_id, $rating, $comment);

        if ($stmt->execute()) {
            // Feedback successfully submitted
            // Redirect to patient dashboard with a success message
            header("Location: patient/dashboard.php?success=feedback_submitted");
            exit();
        } else {
            // Error executing the statement
            // Redirect back to the form with an error message
            error_log("Error submitting feedback: " . $stmt->error); // Log the error for debugging
            header("Location: patient/submit_feedback.php?error=db_error");
            exit();
        }

        $stmt->close();
    } else {
        // Error preparing the statement
        error_log("Error preparing feedback statement: " . $conn->error); // Log the error for debugging
        header("Location: patient/submit_feedback.php?error=prepare_error");
        exit();
    }

    $conn->close();
} else {
    // If accessed directly without POST
    header("Location: patient/submit_feedback.php");
    exit();
}
?>