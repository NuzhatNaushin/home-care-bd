<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Patient';

require_once '../dbconnect.php';

// Verify database connection
if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$error_message = '';
$success_message = '';
$users = null;

// Fetch all users who can receive messages (excluding the current user)
$stmt = $conn->prepare("SELECT User_ID, Name, Role FROM users WHERE User_ID != ? ORDER BY Role, Name");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $users = $stmt->get_result();
    $stmt->close();
} else {
    $error_message = "Database error: Could not fetch users. " . $conn->error;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
    $message = trim($_POST['message'] ?? '');

    // Validate inputs
    if (!$receiver_id) {
        $error_message = "Please select a valid recipient.";
    } elseif (empty($message)) {
        $error_message = "Message cannot be empty.";
    } else {
        // Insert message into database
        $stmt = $conn->prepare("INSERT INTO messages (Sender_ID, Receiver_ID, Message, Status, Created_at) VALUES (?, ?, ?, 'unread', NOW())");
        if ($stmt) {
            $stmt->bind_param("iis", $user_id, $receiver_id, $message);
            if ($stmt->execute()) {
                $success_message = "Message sent successfully!";
                $_POST = [];
            } else {
                $error_message = "Failed to send message: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Database error: Could not prepare message insert. " . $conn->error;
        }
    }
}

// Group users by role for display
$attendants = [];
$patients = [];
$admins = [];
if ($users && $users->num_rows > 0) {
    while ($user = $users->fetch_assoc()) {
        switch ($user['Role']) {
            case 'attendant':
                $attendants[] = $user;
                break;
            case 'patient':
                $patients[] = $user;
                break;
            case 'admin':
                $admins[] = $user;
                break;
        }
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message - Patient - Home Care BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c4f87;
            --secondary-color: #4caf50;
            --light-bg: #f5f9ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }

        .sidebar {
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            padding-top: 20px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .main-content {
            padding: 20px;
        }

        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .recipient-group {
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .recipient-group h5 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="text-center mb-4">
                    <h4 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Home Care BD</h4>
                </div>
                <div class="text-center mb-4">
                    <img src="../images/avatar-placeholder.jpg" class="profile-image mb-3" alt="Profile">
                    <h5 class="mb-0"><?php echo htmlspecialchars($name); ?></h5>
                    <p class="text-light mb-0">Patient</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="job_posting.php"><i class="fas fa-search"></i> Find Attendants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_my_jobs.php"><i class="fas fa-briefcase"></i> My Job Postings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedules.php"><i class="fas fa-calendar-check"></i> My Schedules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical_history.php"><i class="fas fa-notes-medical"></i> Medical History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="emergency.php"><i class="fas fa-ambulance"></i> Emergency</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment_history.php"><i class="fas fa-money-bill"></i> Payments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="submit_feedback.php"><i class="fas fa-comment"></i> Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="request_appointment.php"><i class="fas fa-calendar-plus"></i> Request Appointment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Compose Message</h2>
                    <a href="messages.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Messages</a>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <div class="dashboard-card">
                    <form method="post" action="">
                        <div class="form-group mb-4">
                            <label for="receiver_id" class="form-label fw-bold">Select Recipient:</label>
                            <?php if (!$users || $users->num_rows === 0): ?>
                                <div class="alert alert-warning">No recipients available.</div>
                            <?php else: ?>
                                <?php if (!empty($attendants)): ?>
                                    <div class="recipient-group">
                                        <h5><i class="fas fa-user-nurse me-2"></i>Attendants</h5>
                                        <?php foreach ($attendants as $attendant): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="receiver_id" id="attendant_<?php echo $attendant['User_ID']; ?>" value="<?php echo $attendant['User_ID']; ?>" <?php echo (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $attendant['User_ID']) ? 'checked' : ''; ?> required>
                                                <label class="form-check-label" for="attendant_<?php echo $attendant['User_ID']; ?>">
                                                    <?php echo htmlspecialchars($attendant['Name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($patients)): ?>
                                    <div class="recipient-group">
                                        <h5><i class="fas fa-user me-2"></i>Other Patients</h5>
                                        <?php foreach ($patients as $patient): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="receiver_id" id="patient_<?php echo $patient['User_ID']; ?>" value="<?php echo $patient['User_ID']; ?>" <?php echo (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $patient['User_ID']) ? 'checked' : ''; ?> required>
                                                <label class="form-check-label" for="patient_<?php echo $patient['User_ID']; ?>">
                                                    <?php echo htmlspecialchars($patient['Name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($admins)): ?>
                                    <div class="recipient-group">
                                        <h5><i class="fas fa-user-shield me-2"></i>Administrators</h5>
                                        <?php foreach ($admins as $admin): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="receiver_id" id="admin_<?php echo $admin['User_ID']; ?>" value="<?php echo $admin['User_ID']; ?>" <?php echo (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $admin['User_ID']) ? 'checked' : ''; ?> required>
                                                <label class="form-check-label" for="admin_<?php echo $admin['User_ID']; ?>">
                                                    <?php echo htmlspecialchars($admin['Name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="form-group mb-4">
                            <label for="message" class="form-label fw-bold">Message:</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>

                        <div class="form-group text-end">
                            <button type="submit" name="send_message" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
            const currentPath = window.location.pathname.split('/').pop();
            sidebarLinks.forEach(link => {
                const linkPath = link.getAttribute('href').split('/').pop();
                if (linkPath === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>