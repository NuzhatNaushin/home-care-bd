<?php

session_start();


$conn = new mysqli('localhost', 'root', '', 'homecarebd');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$job_title = $job_description = $location = $start_date = $end_date = "";
$error_message = $success_message = "";


if(!isset($_SESSION['email'])) {
   
    header("Location: login_process.php");
    exit();
}

$email = $_SESSION['email'];


$stmt = $conn->prepare("SELECT users.User_ID, users.Role FROM users WHERE Email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {

    $_SESSION['error_message'] = "User account not found.";
    header("Location: index.php");
    exit();
}

$user_data = $result->fetch_assoc();
$user_id = $user_data['User_ID'];
$role = $user_data['Role'];
$stmt->close();

if($role !== 'patient') {
    $_SESSION['error_message'] = "You must be a patient to post a job.";
    header("Location: index.php");
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $job_title = trim(htmlspecialchars($_POST['job_title'] ?? ''));
    $job_description = trim(htmlspecialchars($_POST['job_description'] ?? ''));
    $location = trim(htmlspecialchars($_POST['location'] ?? ''));
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    

    $errors = [];
    
    if(empty($job_title)) {
        $errors[] = "Job title is required";
    }
    
    if(empty($job_description)) {
        $errors[] = "Job description is required";
    }
    
    if(empty($location)) {
        $errors[] = "Location is required";
    }
    
    if(empty($start_date)) {
        $errors[] = "Start date is required";
    }
    
    if(empty($end_date)) {
        $errors[] = "End date is required";
    }
    
   
    if(empty($errors)) {
        
        $insert_stmt = $conn->prepare("INSERT INTO job_posting (Patient_ID, Job_title, Job_description, Location, Start_date, End_date, Status) VALUES (?, ?, ?, ?, ?, ?, 'open')");
        $insert_stmt->bind_param("isssss", $user_id, $job_title, $job_description, $location, $start_date, $end_date);
        
        if($insert_stmt->execute()) {
            $success_message = "Job posted successfully!";
 
            $job_title = $job_description = $location = $start_date = $end_date = "";
            
         
            $_SESSION['success_message'] = $success_message;
            header("Location: view_my_jobs.php");
            exit();
        } else {
            $error_message = "Error posting job: " . $conn->error;
        }
        
        $insert_stmt->close();
    } else {

        $error_message = "Please fix the following errors: " . implode(", ", $errors);
    }
}


if(isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Care Job</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Post a New Care Job</h2>
        
        <?php 
        if (!empty($error_message)) {
            echo '<div class="alert alert-danger">' . $error_message . '</div>';
        }
        
        if (!empty($success_message)) {
            echo '<div class="alert alert-success">' . $success_message . '</div>';
        }
        ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="job_title">Job Title</label>
                <input type="text" class="form-control" id="job_title" name="job_title" 
                       value="<?php echo htmlspecialchars($job_title); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="job_description">Job Description</label>
                <textarea class="form-control" id="job_description" name="job_description" rows="4" required><?php echo htmlspecialchars($job_description); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location" 
                       value="<?php echo htmlspecialchars($location); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="<?php echo htmlspecialchars($start_date); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="<?php echo htmlspecialchars($end_date); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Post Job</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>