<?php


ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Attendant';

require_once '../dbconnect.php';


if (!$conn || $conn->connect_error) {
    error_log("Database connection failed in attendant/schedule.php: " . $conn->connect_error);
    die("Database connection failed. Please try again later."); // For development
}

$error_message = '';
$success_message = $_GET['success'] ?? ''; 


$sql_messages_count_stmt = $conn->prepare("SELECT COUNT(*) as unread FROM messages
                                          WHERE Receiver_ID = ?
                                          AND Status = 'unread'");
if ($sql_messages_count_stmt) {
    $sql_messages_count_stmt->bind_param("i", $user_id);
    $sql_messages_count_stmt->execute();
    $result_messages_count = $sql_messages_count_stmt->get_result();
    $unread_messages = 0;
    if ($result_messages_count && $row = $result_messages_count->fetch_assoc()) {
        $unread_messages = $row['unread'];
    }
    $sql_messages_count_stmt->close();
} else {
     error_log("Error preparing messages count statement: " . $conn->error);
     $unread_messages = 0; 
}

$all_activities = []; 


$sql_schedules_stmt = $conn->prepare("SELECT 'Regular Job' as type, s.Schedule_ID as id, u.Name as PatientName, s.Start_time, s.End_time, s.Status
                                     FROM schedule s
                                     JOIN users u ON s.Patient_ID = u.User_ID
                                     WHERE s.Attendant_ID = ?
                                     ORDER BY s.Start_time ASC");

if ($sql_schedules_stmt) {
 
    if ($sql_schedules_stmt->bind_param("i", $user_id)) {
        if (!$sql_schedules_stmt->execute()) {
            $error_message .= "Database error executing schedules query: " . $sql_schedules_stmt->error . "<br>";
        } else {
            $result_schedules = $sql_schedules_stmt->get_result();
            while ($row = $result_schedules->fetch_assoc()) {
              
                $row['Details'] = 'Details not available';
                $all_activities[] = $row;
            }
        }
    } else {
         $error_message .= "Database error binding parameters for schedules: " . $sql_schedules_stmt->error . "<br>";
    }
    $sql_schedules_stmt->close();
} else {
    $error_message .= "Database error preparing schedules query: " . $conn->error . "<br>";
}


$sql_emergencies_stmt = $conn->prepare("SELECT 'Emergency Request' as type, er.Request_ID as id, u.Name as PatientName, er.Request_time as Start_time, er.Request_time as End_time, er.Description as Details, er.Status
                                        FROM emergency_request er
                                        JOIN users u ON er.Patient_ID = u.User_ID
                                        WHERE er.Attendant_ID = ? AND er.Status != 'completed' AND er.Status != 'canceled'
                                        ORDER BY er.Request_time ASC");

if ($sql_emergencies_stmt) {
   
    if ($sql_emergencies_stmt->bind_param("i", $user_id)) {
        if (!$sql_emergencies_stmt->execute()) {
             $error_message .= "Database error executing emergency query: " . $sql_emergencies_stmt->error . "<br>";
        } else {
            $result_emergencies = $sql_emergencies_stmt->get_result();
            while ($row = $result_emergencies->fetch_assoc()) {
                $all_activities[] = $row;
            }
        }
    } else {
         $error_message .= "Database error binding parameters for emergencies: " . $sql_emergencies_stmt->error;
    }
    $sql_emergencies_stmt->close();
} else {
    $error_message .= "Database error preparing emergency query: " . $conn->error;
}


usort($all_activities, function($a, $b) {
    $timeA = isset($a['Start_time']) ? strtotime($a['Start_time']) : 0;
    $timeB = isset($b['Start_time']) ? strtotime($b['Start_time']) : 0;
    return $timeA - $timeB; 
});



if (isset($conn) && $conn && $conn->ping()) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Attendant - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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

        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.85em;
            font-weight: bold;
        }

         .badge-success { background-color: #28a745; color: white; }
         .badge-warning { background-color: #ffc107; color: #212529; }
         .badge-danger { background-color: #dc3545; color: white; }
         .badge-info { background-color: #17a2b8; color: white; }
         .badge-primary { background-color: #007bff; color: white; }
         .badge-secondary { background-color: #6c757d; color: white; }

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
                    <p class="text-light mb-0">Attendant</p>
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
                        <a class="nav-link" href="job_details.php"> <i class="fas fa-search"></i> Available Jobs
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="schedule.php"> <i class="fas fa-calendar-check"></i> My Schedule
                        </a>
                    </li>
                     <li class="nav-item">
                         <a class="nav-link" href="messages.php">
                             <i class="fas fa-envelope"></i> Messages
                              <?php if ($unread_messages > 0): ?>
                                 <span class="badge badge-danger"><?php echo $unread_messages; ?></span>
                             <?php endif; ?>
                         </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="received_feedback.php"> <i class="fas fa-comments"></i> Received Feedback
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="payment_history.php"> <i class="fas fa-money-bill"></i> Payment History
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
                    <h2>My Schedule</h2>
                    </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo nl2br(htmlspecialchars($error_message)); ?></div>
                <?php endif; ?>
                 <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>


                <div class="dashboard-card">
                    <?php if (!empty($all_activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Patient Name</th>
                                        <th>Time/Dates</th>
                                        <th>Details/Notes</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_activities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['type'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($activity['PatientName'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $start_time = $activity['Start_time'] ?? 'now';
                                                $end_time = $activity['End_time'] ?? 'now';
                                                if (($activity['type'] ?? '') === 'Regular Job') {
                                                    echo htmlspecialchars(date('M d, Y h:i A', strtotime($start_time)))
                                                         . ' to '
                                                         . htmlspecialchars(date('M d, Y h:i A', strtotime($end_time)));
                                                } elseif (($activity['type'] ?? '') === 'Emergency Request') {
                                                    
                                                    echo 'Requested on ' . htmlspecialchars(date('M d, Y h:i A', strtotime($start_time)));
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                             <td><?php echo nl2br(htmlspecialchars($activity['Details'] ?? 'Details not available')); ?></td>
                                            <td>
                                                <?php
                                                    $status = htmlspecialchars($activity['Status'] ?? 'unknown');
                                                    $badge_class = 'badge-secondary'; 
                                                    $display_text = ucfirst(str_replace('_', ' ', $status)); 
                                                    switch ($status) {
                                                        case 'pending_patient_request': 
                                                        case 'pending': 
                                                            $badge_class = 'badge-warning';
                                                            break;
                                                        case 'attendant_assigned': 
                                                        case 'in_progress': 
                                                            $badge_class = 'badge-info';
                                                            break;
                                                        case 'confirmed': 
                                                        case 'completed': 
                                                            $badge_class = 'badge-success';
                                                            break;
                                                        case 'canceled': 
                                                            $badge_class = 'badge-danger';
                                                            break;
                                                        
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $display_text; ?></span>
                                            </td>
                                            <td>
                                                <?php if (($activity['type'] ?? '') === 'Regular Job'): ?>
                                                    <a href="view_schedule_details.php?id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    <?php if (($activity['Status'] ?? '') == 'attendant_assigned'): ?>
                                                         <a href="process_schedule_action.php?action=confirm&id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-success" onclick="return confirm('Confirm this schedule?');">Confirm</a>
                                                    <?php endif; ?>
                                                    <?php if (($activity['Status'] ?? '') == 'confirmed' && strtotime($activity['End_time'] ?? 'now') < time()): // If confirmed and time is past end time ?>
                                                         <a href="process_schedule_action.php?action=complete&id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-primary" onclick="return confirm('Mark this schedule as completed?');">Complete</a>
                                                    <?php endif; ?>
                                                     <?php if (($activity['Status'] ?? '') != 'completed' && ($activity['Status'] ?? '') != 'canceled'): ?>
                                                          <?php endif; ?>
                                                <?php elseif (($activity['type'] ?? '') === 'Emergency Request'): ?>
                                                     <a href="view_emergency_request.php?id=<?php echo htmlspecialchars($activity['id'] ?? ''); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                    <?php if (($activity['Status'] ?? '') == 'in_progress'): ?>
                                                         <form action="update_emergency_status.php" method="post" style="display: inline;">
                                                             <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($activity['id'] ?? ''); ?>">
                                                             <button type="submit" name="complete" class="btn btn-sm btn-success" onclick="return confirm('Mark this emergency request as completed?');">Complete</button>
                                                         </form>
                                                    <?php endif; ?>
                                                    <?php if (($activity['Status'] ?? '') != 'completed' && ($activity['Status'] ?? '') != 'canceled'): // Option to cancel emergency if not already ?>
                                                         <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">You have no scheduled jobs or claimed emergency requests yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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