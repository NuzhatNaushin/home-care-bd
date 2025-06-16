<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../index.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Patient';

// Dummy notification data for frontend display
$notifications = [
    [
        'id' => 1,
        'title' => 'New Job Application',
        'message' => 'Sarah Johnson has applied for your "Home Care Nurse" job posting.',
        'created_at' => '2025-05-04 14:30:00',
        'status' => 'unread',
        'type' => 'job_application'
    ],
    [
        'id' => 2,
        'title' => 'Appointment Confirmed',
        'message' => 'Your appointment with Dr. Michael Brown has been confirmed for May 6, 2025 at 10:00 AM.',
        'created_at' => '2025-05-03 09:15:00',
        'status' => 'unread',
        'type' => 'appointment'
    ],
    [
        'id' => 3,
        'title' => 'Payment Received',
        'message' => 'Payment of $150.00 has been successfully processed for Invoice #INV-2025-04.',
        'created_at' => '2025-05-02 16:45:00',
        'status' => 'read',
        'type' => 'payment'
    ],
    [
        'id' => 4,
        'title' => 'Message from Attendant',
        'message' => 'You have a new message from Emily Davis regarding your upcoming appointment.',
        'created_at' => '2025-05-01 11:20:00',
        'status' => 'read',
        'type' => 'message'
    ],
    [
        'id' => 5,
        'title' => 'Schedule Updated',
        'message' => 'Your appointment on May 8, 2025 has been rescheduled to 2:00 PM.',
        'created_at' => '2025-04-30 13:10:00',
        'status' => 'read',
        'type' => 'schedule'
    ],
    [
        'id' => 6,
        'title' => 'Medical Record Updated',
        'message' => 'Your medical records have been updated with new information.',
        'created_at' => '2025-04-29 15:25:00',
        'status' => 'read',
        'type' => 'medical'
    ],
    [
        'id' => 7,
        'title' => 'Job Posting Expiring',
        'message' => 'Your job posting "Night Shift Caregiver" will expire in 3 days. Consider extending it.',
        'created_at' => '2025-04-28 10:05:00',
        'status' => 'read',
        'type' => 'job_posting'
    ]
];

// Count unread notifications
$unread_notifications = 0;
foreach ($notifications as $notification) {
    if ($notification['status'] == 'unread') {
        $unread_notifications++;
    }
}

// Handle mark as read action
if (isset($_GET['action']) && $_GET['action'] == 'mark_read' && isset($_GET['id'])) {
    // In a real implementation, you would update the database
    // For this frontend-only page, we'll just update the array
    $notification_id = (int)$_GET['id'];
    foreach ($notifications as $key => $notification) {
        if ($notification['id'] == $notification_id) {
            $notifications[$key]['status'] = 'read';
            break;
        }
    }
    // Recalculate unread count
    $unread_notifications = 0;
    foreach ($notifications as $notification) {
        if ($notification['status'] == 'unread') {
            $unread_notifications++;
        }
    }
}

// Handle mark all as read action
if (isset($_GET['action']) && $_GET['action'] == 'mark_all_read') {
    // In a real implementation, you would update the database
    // For this frontend-only page, we'll just update the array
    foreach ($notifications as $key => $notification) {
        $notifications[$key]['status'] = 'read';
    }
    $unread_notifications = 0;
}

// Dummy unread messages count
$unread_messages = 2;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Home Care BD</title>
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

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: red;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon-container {
            position: relative;
            display: inline-block;
            margin-right: 15px;
        }

        .small-icon {
            font-size: 1.2rem;
        }

        .notification-item {
            border-left: 5px solid transparent;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .notification-item:hover {
            background-color: #f0f0f0;
        }

        .notification-item.unread {
            border-left-color: var(--primary-color);
            background-color: #e8f4ff;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .notification-time {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .notification-message {
            color: #444;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(44, 79, 135, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            color: var(--primary-color);
        }

        .notification-action {
            margin-top: 10px;
        }

        .notification-badge-count {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 0.8rem;
            margin-left: 5px;
        }

        .notification-empty {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
        }

        .notification-empty i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #d1d1d1;
        }

        .notification-filters {
            margin-bottom: 20px;
        }

        .filter-btn {
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .notification-type-job_application i { color: #007bff; }
        .notification-type-appointment i { color: #28a745; }
        .notification-type-payment i { color: #17a2b8; }
        .notification-type-message i { color: #6610f2; }
        .notification-type-schedule i { color: #fd7e14; }
        .notification-type-medical i { color: #dc3545; }
        .notification-type-job_posting i { color: #20c997; }
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
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="job_posting.php">
                            <i class="fas fa-search"></i> Find Attendants
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_my_jobs.php">
                            <i class="fas fa-briefcase"></i> My Job Postings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedules.php">
                            <i class="fas fa-calendar-check"></i> My Schedules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical_history.php">
                            <i class="fas fa-notes-medical"></i> Medical History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="emergency.php">
                            <i class="fas fa-ambulance"></i> Emergency
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment_history.php">
                            <i class="fas fa-money-bill"></i> Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="submit_feedback.php">
                            <i class="fas fa-user"></i> Feedback
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="request_appointment.php">
                            <i class="fas fa-calendar-plus"></i> Request Appointment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_notifications > 0): ?>
                                <span class="notification-badge-count"><?php echo htmlspecialchars($unread_notifications); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
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
                    <h2>
                        Notifications
                        <?php if ($unread_notifications > 0): ?>
                            <span class="notification-badge-count"><?php echo htmlspecialchars($unread_notifications); ?> new</span>
                        <?php endif; ?>
                    </h2>
                    <div class="d-flex">
                        <div class="icon-container">
                            <a href="notifications.php" class="text-dark">
                                <i class="fas fa-bell small-icon"></i>
                                <?php if ($unread_notifications > 0): ?>
                                    <span class="notification-badge"><?php echo htmlspecialchars($unread_notifications); ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="icon-container">
                            <a href="messages.php" class="text-dark">
                                <i class="fas fa-envelope small-icon"></i>
                                <?php if ($unread_messages > 0): ?>
                                    <span class="notification-badge"><?php echo htmlspecialchars($unread_messages); ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="notification-filters">
                            <button class="btn btn-sm btn-primary filter-btn active" data-filter="all">All</button>
                            <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="unread">Unread</button>
                            <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="job_application">Jobs</button>
                            <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="appointment">Appointments</button>
                            <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="payment">Payments</button>
                            <button class="btn btn-sm btn-outline-primary filter-btn" data-filter="message">Messages</button>
                        </div>
                        <?php if ($unread_notifications > 0): ?>
                            <a href="?action=mark_all_read" class="btn btn-sm btn-outline-primary">Mark All as Read</a>
                        <?php endif; ?>
                    </div>

                    <div class="notification-list">
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?php echo $notification['status']; ?> notification-type-<?php echo $notification['type']; ?>" data-type="<?php echo $notification['type']; ?>">
                                    <div class="d-flex">
                                        <div class="notification-icon">
                                            <?php if ($notification['type'] == 'job_application'): ?>
                                                <i class="fas fa-briefcase"></i>
                                            <?php elseif ($notification['type'] == 'appointment'): ?>
                                                <i class="fas fa-calendar-check"></i>
                                            <?php elseif ($notification['type'] == 'payment'): ?>
                                                <i class="fas fa-money-bill"></i>
                                            <?php elseif ($notification['type'] == 'message'): ?>
                                                <i class="fas fa-envelope"></i>
                                            <?php elseif ($notification['type'] == 'schedule'): ?>
                                                <i class="fas fa-calendar-alt"></i>
                                            <?php elseif ($notification['type'] == 'medical'): ?>
                                                <i class="fas fa-notes-medical"></i>
                                            <?php elseif ($notification['type'] == 'job_posting'): ?>
                                                <i class="fas fa-search"></i>
                                            <?php else: ?>
                                                <i class="fas fa-bell"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="notification-header">
                                                <h6 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                <span class="notification-time"><?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?></span>
                                            </div>
                                            <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <div class="notification-action">
                                                <?php if ($notification['status'] == 'unread'): ?>
                                                    <a href="?action=mark_read&id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-secondary me-2">Mark as Read</a>
                                                <?php endif; ?>
                                                
                                                <?php if ($notification['type'] == 'job_application'): ?>
                                                    <a href="view_application.php?id=<?php echo mt_rand(1, 100); ?>" class="btn btn-sm btn-primary">View Application</a>
                                                <?php elseif ($notification['type'] == 'appointment'): ?>
                                                    <a href="view_schedule.php?id=<?php echo mt_rand(1, 100); ?>" class="btn btn-sm btn-primary">View Appointment</a>
                                                <?php elseif ($notification['type'] == 'payment'): ?>
                                                    <a href="payment_details.php?id=<?php echo mt_rand(1, 100); ?>" class="btn btn-sm btn-primary">View Payment</a>
                                                <?php elseif ($notification['type'] == 'message'): ?>
                                                    <a href="messages.php" class="btn btn-sm btn-primary">View Message</a>
                                                <?php elseif ($notification['type'] == 'schedule'): ?>
                                                    <a href="schedules.php" class="btn btn-sm btn-primary">View Schedule</a>
                                                <?php elseif ($notification['type'] == 'medical'): ?>
                                                    <a href="medical_history.php" class="btn btn-sm btn-primary">View Records</a>
                                                <?php elseif ($notification['type'] == 'job_posting'): ?>
                                                    <a href="view_my_jobs.php" class="btn btn-sm btn-primary">View Job</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <h5>No Notifications</h5>
                                <p>You don't have any notifications at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight active sidebar link
            const sidebarLinks = document.querySelectorAll('.nav-link');
            const currentPath = window.location.pathname.split('/').pop();
            
            sidebarLinks.forEach(link => {
                const linkPath = link.getAttribute('href').split('/').pop();
                if (linkPath === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });

            // Notification filtering
            const filterButtons = document.querySelectorAll('.filter-btn');
            const notificationItems = document.querySelectorAll('.notification-item');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active', 'btn-primary'));
                    filterButtons.forEach(btn => btn.classList.add('btn-outline-primary'));
                    
                    // Add active class to clicked button
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('active', 'btn-primary');
                    
                    // Get filter value
                    const filterValue = this.getAttribute('data-filter');
                    
                    // Show/hide notifications based on filter
                    notificationItems.forEach(item => {
                        if (filterValue === 'all') {
                            item.style.display = 'block';
                        } else if (filterValue === 'unread') {
                            if (item.classList.contains('unread')) {
                                item.style.display = 'block';
                            } else {
                                item.style.display = 'none';
                            }
                        } else {
                            if (item.getAttribute('data-type') === filterValue) {
                                item.style.display = 'block';
                            } else {
                                item.style.display = 'none';
                            }
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>