<?php

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
 
    header("Location: ../index.php"); 
    exit();
}


require_once '../dbconnect.php'; 

$user_id = $_SESSION['user_id']; 


$attendants = [];

$sql_attendants = "SELECT u.User_ID, u.Name FROM users u JOIN attendant a ON u.User_ID = a.User_ID WHERE u.Role = 'attendant' AND u.Verification_status = 'verified' ORDER BY u.Name";
$result_attendants = $conn->query($sql_attendants);

if ($result_attendants === false) {
  
    $error_message = "Error fetching attendants: " . $conn->error;
    $result_attendants = false; 
} else {
     if ($result_attendants->num_rows > 0) {
         while($row = $result_attendants->fetch_assoc()) {
             $attendants[] = $row;
         }
     }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback - HomeCare BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .feedback-form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <div class="feedback-form-container">
        <h2 class="text-center mb-4">Submit Feedback</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                    $error_msg = htmlspecialchars($_GET['error']);
                    if ($error_msg === 'empty_feedback_fields') echo 'Please fill in all required fields.';
                    else if ($error_msg === 'invalid_rating') echo 'Rating must be between 1 and 5.';
                    else echo 'An error occurred: ' . $error_msg;
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

         <?php if (isset($error_message)):  ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <form action="../process_submit_feedback.php" method="POST">
            <input type="hidden" name="reviewer" value="<?php echo htmlspecialchars($user_id); ?>">

            <div class="mb-3">
                <label for="reviewee" class="form-label">Select Attendant:</label>
                 <?php if ($result_attendants !== false && count($attendants) > 0):  ?>
                    <select class="form-select" id="reviewee" name="reviewee" required>
                        <option value="">-- Select Attendant --</option>
                        <?php foreach ($attendants as $attendant): ?>
                            <option value="<?php echo htmlspecialchars($attendant['User_ID']); ?>">
                                <?php echo htmlspecialchars($attendant['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                 <?php else: ?>
                     <p class="text-danger">Could not load attendants. Please try again later.</p>
                     <select class="form-select" id="reviewee" name="reviewee" required disabled>
                         <option value="">-- No Attendants Available --</option>
                     </select>
                 <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1-5):</label>
                <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" required>
            </div>

            <div class="mb-3">
                <label for="comment" class="form-label">Comment:</label>
                <textarea class="form-control" id="comment" name="comment" rows="4"></textarea>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
