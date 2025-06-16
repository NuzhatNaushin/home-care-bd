<?php

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {

    header("Location: ../index.php"); 
    exit();
}


$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Patient'; 


require_once '../dbconnect.php'; 


$payments = null;
$error_message = '';


$stmt = $conn->prepare("SELECT p.*, jp.Job_title, receiver_u.Name as ReceiverName
                       FROM payment p
                       JOIN job_posting jp ON p.Job_ID = jp.Job_ID
                       JOIN users receiver_u ON p.Receiver_ID = receiver_u.User_ID
                       WHERE p.Payer_ID = ?
                       ORDER BY p.Date DESC");

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $payments = $stmt->get_result();
    $stmt->close();
} else {
    $error_message = "Database error: Could not prepare statement to fetch payments. " . $conn->error;
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
    <title>Payment History - Patient - Home Care BD</title>
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="profile.php"> <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="job_posting.php"> <i class="fas fa-search"></i> Find Attendants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_my_jobs.php"> <i class="fas fa-briefcase"></i> My Job Postings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedules.php"> <i class="fas fa-calendar-check"></i> My Schedules
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="medical_history.php"> <i class="fas fa-notes-medical"></i> Medical History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php"> <i class="fas fa-envelope"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="emergency.php"> <i class="fas fa-ambulance"></i> Emergency
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="payment_history.php"> <i class="fas fa-money-bill"></i> Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="submit_feedback.php"> <i class="fas fa-user"></i> Feedback
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="request_appointment.php"> <i class="fas fa-calendar-plus"></i> Request Appointment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php"> <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                         <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>My Payment History</h2>
                    </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div class="dashboard-card">
                    <?php if ($payments && $payments->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Job Title</th>
                                        <th>Paid To</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    <?php while ($payment = $payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['Payment_ID']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['Job_title']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['ReceiverName']); ?></td>
                                            <td>৳<?php echo htmlspecialchars(number_format($payment['Amount'], 2)); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($payment['Date']))); ?></td>
                                            <td><?php echo ucfirst(htmlspecialchars($payment['Payment_method'] ?? 'N/A')); ?></td>
                                            <td>
                                                <?php
                                                    $status = htmlspecialchars($payment['Payment_status'] ?? 'unspecified');
                                                    $badge_class = 'bg-secondary';
                                                    if ($status == 'paid') $badge_class = 'bg-success';
                                                    if ($status == 'unpaid') $badge_class = 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                            </td>
                                            </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php elseif ($payments): ?>
                        <div class="alert alert-info">You have no payment records yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
