<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    
    header("Location: ../index.php"); 
    exit();
}


$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Attendant'; 

require_once '../dbconnect.php'; 


$received_feedback = null;
$error_message = '';


$stmt = $conn->prepare("SELECT f.*, u_r.Name as ReviewerName
                       FROM feedback f
                       JOIN users u_r ON f.Reviewer_ID = u_r.User_ID
                       WHERE f.Reviewee_ID = ?
                       ORDER BY f.Created_at DESC"); 

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $received_feedback = $stmt->get_result();
    $stmt->close();
} else {
    $error_message = "Database error: Could not prepare statement to fetch feedback. " . $conn->error;
}

if (isset($conn) && $conn) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Received - Attendant - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .dashboard-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
         .feedback-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
         }
         .feedback-item:last-child {
             border-bottom: none;
         }
         .feedback-item strong {
             margin-right: 10px;
         }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
             <a class="navbar-brand" href="../index.php">HomeCareBD</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                         <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                    <li class="nav-item">
                         <a class="nav-link" href="job_details.php">Find Jobs</a>
                        </li>
                    <li class="nav-item">
                         <a class="nav-link" href="schedule.php">My Schedule</a>
                        </li>
                    <li class="nav-item">
                         <a class="nav-link" href="messages.php">Messages</a>
                        </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($name); ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                             <a class="dropdown-item" href="profile.php">My Profile</a>
                            <a class="dropdown-item" href="edit_profile.php">Edit Profile</a>
                            <div class="dropdown-divider"></div>
                             <a class="dropdown-item" href="../logout.php">Logout</a>
                            </div>
                         <a href="add_attendant_schedule_form.php" class="btn btn-light ml-2">Add My Availability</a>
                         </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Feedback Received</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="dashboard-card">
            <?php if ($received_feedback && $received_feedback->num_rows > 0): ?>
                <div class="feedback-list">
                    <?php while ($feedback = $received_feedback->fetch_assoc()): ?>
                        <div class="feedback-item">
                            <p>
                                <strong>Rating:</strong> <?php echo htmlspecialchars($feedback['Rating']); ?>/5
                                <?php
                                    $rating = $feedback['Rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star text-warning"></i>';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                        } else {
                                            echo '<i class="far fa-star text-warning"></i>';
                                        }
                                    }
                                ?>
                            </p>
                            <p><strong>Comment:</strong> <?php echo nl2br(htmlspecialchars($feedback['Comment'])); ?></p>
                            <p><strong>From:</strong> <?php echo htmlspecialchars($feedback['ReviewerName']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($feedback['Created_at']))); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php elseif ($received_feedback): ?>
                <div class="alert alert-info">No feedback received yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
