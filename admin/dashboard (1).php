<?php
session_start();


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {

    header("Location: ../index.php"); 
    exit();
}

$admin_id = $_SESSION['user_id'];

/*
$host = "localhost";
$username = "root";
$password = "";
$database = "homecarebd"; // 

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
*/

require_once '../dbconnect.php';

$update_login_stmt = $conn->prepare("UPDATE admin SET Last_login = NOW() WHERE User_ID = ?");
$update_login_stmt->bind_param("i", $admin_id);
$update_login_stmt->execute();
$update_login_stmt->close();

$admin_query_stmt = $conn->prepare("SELECT u.*, a.Admin_since, a.Last_login
                                 FROM users u
                                 JOIN admin a ON u.User_ID = a.User_ID
                                 WHERE u.User_ID = ?");
$admin_query_stmt->bind_param("i", $admin_id);
$admin_query_stmt->execute();
$admin_result = $admin_query_stmt->get_result();
$admin_data = $admin_result->fetch_assoc();
$admin_query_stmt->close();

$users_count_query = "SELECT Role, COUNT(*) as count FROM users GROUP BY Role";
$users_count_result = $conn->query($users_count_query);
$users_count = [];

while ($row = $users_count_result->fetch_assoc()) {
    $users_count[$row['Role']] = $row['count'];
}

$verification_query = "SELECT Verification_status, COUNT(*) as count FROM users GROUP BY Verification_status";
$verification_result = $conn->query($verification_query);
$verification_count = [];

while ($row = $verification_result->fetch_assoc()) {
    $verification_count[$row['Verification_status']] = $row['count'];
}

$jobs_query = "SELECT Status, COUNT(*) as count FROM job_posting GROUP BY Status";
$jobs_result = $conn->query($jobs_query);
$jobs_count = [];

while ($row = $jobs_result->fetch_assoc()) {
    $jobs_count[$row['Status']] = $row['count'];
}


$recent_applications_query = "SELECT ja.*, jp.Job_title, u.Name as attendant_name, u.Email as attendant_email
                             FROM job_application ja
                             JOIN job_posting jp ON ja.Job_ID = jp.Job_ID -- CORRECTED: Linking job_application.Job_ID to job_posting.Job_ID
                             JOIN users u ON ja.Attendant_ID = u.User_ID
                             ORDER BY ja.Created_at DESC LIMIT 5";
$recent_applications = $conn->query($recent_applications_query);

$recent_payments_query = "SELECT p.*, jp.Job_title, u1.Name as payer_name, u2.Name as receiver_name
                         FROM payment p
                         JOIN job_posting jp ON p.Job_ID = jp.Job_ID -- CORRECTED: Linking payment.Job_ID to job_posting.Job_ID
                         JOIN users u1 ON p.Payer_ID = u1.User_ID
                         JOIN users u2 ON p.Receiver_ID = u2.User_ID
                         ORDER BY p.Date DESC LIMIT 5";
$recent_payments = $conn->query($recent_payments_query);

$recent_messages_query = "SELECT m.*, u1.Name as sender_name, u2.Name as receiver_name
                         FROM messages m
                         JOIN users u1 ON m.Sender_ID = u1.User_ID
                         JOIN users u2 ON m.Receiver_ID = u2.User_ID
                         ORDER BY m.Created_at DESC LIMIT 5";
$recent_messages = $conn->query($recent_messages_query);


$recent_users_query = "SELECT * FROM users ORDER BY User_ID DESC LIMIT 5";
$recent_users = $conn->query($recent_users_query);

$all_schedules_sql = "SELECT s.*,
                      p_u.Name as PatientName, p_u.Role as PatientRole,
                      a_u.Name as AttendantName, a_u.Role as AttendantRole
                      FROM schedule s
                      LEFT JOIN users p_u ON s.Patient_ID = p_u.User_ID 
                      LEFT JOIN users a_u ON s.Attendant_ID = a_u.User_ID 
                      ORDER BY s.Start_time DESC";
$all_schedules_result = $conn->query($all_schedules_sql);

$all_feedback_sql = "SELECT f.*, u_r.Name as ReviewerName, u_e.Name as RevieweeName
                    FROM feedback f
                    JOIN users u_r ON f.Reviewer_ID = u_r.User_ID
                    JOIN users u_e ON f.Reviewee_ID = u_e.User_ID
                    ORDER BY f.Created_at DESC"; 
$all_feedback_result = $conn->query($all_feedback_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    $action = trim($_POST['action'] ?? '');
    if (!empty($action)) {
        $log_query_stmt = $conn->prepare("INSERT INTO audit_log (Admin_ID, Action) VALUES (?, ?)");
        $log_query_stmt->bind_param("is", $admin_id, $action);
        $log_query_stmt->execute();
        $log_query_stmt->close();
    }

    if (isset($_POST['verify_user']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];

        $update_users_stmt = $conn->prepare("UPDATE users SET Verification_status = 'verified' WHERE User_ID = ?");
        $update_users_stmt->bind_param("i", $user_id);
        $update_users_stmt->execute();
        $update_users_stmt->close();

        $user_role_query = "SELECT Role FROM users WHERE User_ID = $user_id"; 
        $role_result = $conn->query($user_role_query);
        $role_data = $role_result->fetch_assoc();

        if (($role_data['Role'] ?? '') == 'patient') {
            $update_patient_stmt = $conn->prepare("UPDATE patient SET Verification_status = 'verified' WHERE User_ID = ?");
            $update_patient_stmt->bind_param("i", $user_id);
            $update_patient_stmt->execute();
            $update_patient_stmt->close();
        } elseif (($role_data['Role'] ?? '') == 'attendant') {
            $update_attendant_stmt = $conn->prepare("UPDATE attendant SET Verification_status = 'verified' WHERE User_ID = ?");
            $update_attendant_stmt->bind_param("i", $user_id);
            $update_attendant_stmt->execute();
            $update_attendant_stmt->close();
        }
      
        header("Location: dashboard (1).php?msg=" . urlencode("User verified successfully")); // Redirect back to this file
        exit();
       
    }

    if (isset($_POST['reject_user']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
  
        $update_users_stmt = $conn->prepare("UPDATE users SET Verification_status = 'rejected' WHERE User_ID = ?");
        $update_users_stmt->bind_param("i", $user_id);
        $update_users_stmt->execute();
        $update_users_stmt->close();

        $user_role_query = "SELECT Role FROM users WHERE User_ID = $user_id"; // This query is fine for fetching role
        $role_result = $conn->query($user_role_query);
        $role_data = $role_result->fetch_assoc();

        if (($role_data['Role'] ?? '') == 'patient') {
             $update_patient_stmt = $conn->prepare("UPDATE patient SET Verification_status = 'rejected' WHERE User_ID = ?");
            $update_patient_stmt->bind_param("i", $user_id);
            $update_patient_stmt->execute();
            $update_patient_stmt->close();
        } elseif (($role_data['Role'] ?? '') == 'attendant') {
             $update_attendant_stmt = $conn->prepare("UPDATE attendant SET Verification_status = 'rejected' WHERE User_ID = ?");
            $update_attendant_stmt->bind_param("i", $user_id);
            $update_attendant_stmt->execute();
            $update_attendant_stmt->close();
        }

        header("Location: dashboard (1).php?msg=" . urlencode("User rejected")); 
        exit();
       
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HomeCare BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
        }
        .sidebar-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .sidebar-link:hover {
            background-color: #495057;
            color: #fff;
        }
        .sidebar-link.active {
            background-color: #0d6efd;
        }
        .content {
            padding: 20px;
        }
        .table-responsive {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5>HomeCare BD</h5>
                        <p>Admin Panel</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="sidebar-link active" href="#dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-link" href="#users">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="sidebar-link" href="#all-schedules">
                                <i class="fas fa-calendar-alt me-2"></i>Schedules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-link" href="#all-feedback">
                                <i class="fas fa-comments me-2"></i>Feedback
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-link" href="#jobs">
                                <i class="fas fa-briefcase me-2"></i>Jobs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-link" href="#payments">
                                <i class="fas fa-credit-card me-2"></i>Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="sidebar-link" href="#messages">
                                <i class="fas fa-envelope me-2"></i>Messages
                            </a>
                        </li>
                        <li class="nav-item">
                             <a class="sidebar-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                            </li>
                    </ul>
                </div>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="btn btn-sm btn-outline-secondary">Welcome, <?php echo htmlspecialchars($admin_data['Name']); ?></span>
                        </div>
                         <a href="../logout.php" class="btn btn-sm btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                        </div>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <section id="dashboard">
                    <h3>System Overview</h3>
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="stat-card bg-primary text-white">
                                <h5>Total Users</h5>
                                <h2><?php echo htmlspecialchars(array_sum($users_count)); ?></h2>
                                <div>
                                    <span>Attendants: <?php echo htmlspecialchars(isset($users_count['attendant']) ? $users_count['attendant'] : 0); ?></span><br>
                                    <span>Patients: <?php echo htmlspecialchars(isset($users_count['patient']) ? $users_count['patient'] : 0); ?></span><br>
                                    <span>Admins: <?php echo htmlspecialchars(isset($users_count['admin']) ? $users_count['admin'] : 0); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stat-card bg-warning text-dark">
                                <h5>Pending Verification</h5>
                                <h2><?php echo htmlspecialchars(isset($verification_count['pending']) ? $verification_count['pending'] : 0); ?></h2>
                                <div>Users requiring verification</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stat-card bg-success text-white">
                                <h5>Job Postings</h5>
                                <h2><?php echo htmlspecialchars(array_sum($jobs_count)); ?></h2>
                                <div>
                                    <span>Open: <?php echo htmlspecialchars(isset($jobs_count['open']) ? $jobs_count['open'] : 0); ?></span><br>
                                    <span>In Progress: <?php echo htmlspecialchars(isset($jobs_count['in progress']) ? $jobs_count['in progress'] : 0); ?></span><br>
                                    <span>Closed: <?php echo htmlspecialchars(isset($jobs_count['closed']) ? $jobs_count['closed'] : 0); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stat-card bg-info text-white">
                                <h5>Admin Since</h5>
                                <h2><?php echo htmlspecialchars(date('d M Y', strtotime($admin_data['Admin_since']))); ?></h2>
                                <div>Last login: <?php echo htmlspecialchars(date('d M Y H:i', strtotime($admin_data['Last_login']))); ?></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="users" class="mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Recent Users</h3>
                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#pendingUsersModal">
                            View Pending Users
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $recent_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['User_ID']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td><?php htmlspecialchars($user['Phone']); ?></td>
                                    <td><span class="badge bg-<?php echo $user['Role'] == 'admin' ? 'danger' : ($user['Role'] == 'attendant' ? 'primary' : 'success'); ?>"><?php echo ucfirst(htmlspecialchars($user['Role'])); ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['Verification_status'] == 'verified' ? 'success' : ($user['Verification_status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['Verification_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($user['Verification_status'] ?? '') == 'pending'): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <input type="hidden" name="action" value="Verified user ID <?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <button type="submit" name="verify_user" class="btn btn-sm btn-success">Verify</button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <input type="hidden" name="action" value="Rejected user ID <?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <button type="submit" name="reject_user" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>No Action</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="all-schedules" class="dashboard-section mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                         <h4>All Schedules</h4>
                         <a href="add_schedule_form.php" class="btn btn-primary btn-sm">Add New Schedule</a>
                         </div>

                    <?php if ($all_schedules_result && $all_schedules_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Schedule ID</th>
                                        <th>Patient</th>
                                        <th>Attendant</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $all_schedules_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['Schedule_ID']); ?></td>
                                            <td><?php echo htmlspecialchars($row['PatientName'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['AttendantName'] ?? 'Unassigned'); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($row['Start_time']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($row['End_time']))); ?></td>
                                            <td>
                                                 <?php
                                                    $status = htmlspecialchars($row['Status']);
                                                    $badge_class = 'bg-secondary'; // Default
                                                    switch ($status) {
                                                        case 'pending_patient_request':
                                                            $badge_class = 'bg-warning text-dark';
                                                            break;
                                                        case 'attendant_assigned':
                                                            $badge_class = 'bg-info';
                                                            break;
                                                        case 'confirmed':
                                                            $badge_class = 'bg-success';
                                                            break;
                                                        case 'completed':
                                                            $badge_class = 'bg-primary'; 
                                                            break;
                                                        case 'canceled':
                                                            $badge_class = 'bg-danger';
                                                            break;
                                                    }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['Notes'] ?? 'N/A'); ?></td>
                                            </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No schedule entries found.</p>
                    <?php endif; ?>
                </section>
                <section id="all-feedback" class="dashboard-section mt-4">
                    <h4>All System Feedback</h4>
                     <?php if ($all_feedback_result && $all_feedback_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                        <th>From</th>
                                        <th>About</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $all_feedback_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['Rating']); ?>/5</td>
                                            <td><?php echo nl2br(htmlspecialchars($row['Comment'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['ReviewerName']); ?></td>
                                            <td><?php echo htmlspecialchars($row['RevieweeName']); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($row['Created_at']))); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No feedback submitted yet.</p>
                    <?php endif; ?>
                </section>
                <section id="jobs" class="mt-5">
                    <h3>Recent Job Applications</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Job Title</th>
                                    <th>Attendant</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($app = $recent_applications->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['Application_ID']); ?></td>
                                     <td><?php echo htmlspecialchars($app['Job_title']); ?></td>
                                     <td><?php echo htmlspecialchars($app['attendant_name']) . ' (' . htmlspecialchars($app['attendant_email']) . ')'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($app['Status'] ?? '') == 'accepted' ? 'success' : (($app['Status'] ?? '') == 'rejected' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($app['Status'] ?? '')); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($app['Application_Date']))); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="payments" class="mt-5">
                    <h3>Recent Payments</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Job</th>
                                    <th>Payer</th>
                                    <th>Receiver</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $recent_payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['Payment_ID']); ?></td>
                                     <td><?php echo htmlspecialchars($payment['Job_title']); ?></td>
                                     <td><?php echo htmlspecialchars($payment['payer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['receiver_name']); ?></td>
                                    <td>৳<?php echo htmlspecialchars(number_format($payment['Amount'], 2)); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($payment['Payment_method'])); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($payment['Date']))); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="messages" class="mt-5 mb-5">
                    <h3>Recent Messages</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($message = $recent_messages->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message['Message_ID']); ?></td>
                                    <td><?php echo htmlspecialchars($message['sender_name']); ?></td>
                                    <td><?php echo htmlspecialchars($message['receiver_name']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($message['Message'], 0, 50) . (strlen($message['Message']) > 50 ? '...' : '')); ?></td>
                                    <td><span class="badge bg-<?php echo ($message['Status'] ?? '') == 'read' ? 'success' : 'warning'; ?>"><?php echo ucfirst(htmlspecialchars($message['Status'] ?? '')); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($message['Created_at']))); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <div class="modal fade" id="pendingUsersModal" tabindex="-1" aria-labelledby="pendingUsersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pendingUsersModalLabel">Pending Verification Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                  
                    $pending_users_query_stmt = $conn->prepare("SELECT * FROM users WHERE Verification_status = 'pending'");
                    $pending_users_query_stmt->execute();
                    $pending_users = $pending_users_query_stmt->get_result();
                    $pending_users_query_stmt->close();
                   


                    if ($pending_users && $pending_users->num_rows > 0):
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $pending_users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['User_ID']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Phone']); ?></td>
                                    <td><span class="badge bg-<?php echo ($user['Role'] ?? '') == 'admin' ? 'danger' : (($user['Role'] ?? '') == 'attendant' ? 'primary' : 'success'); ?>"><?php echo ucfirst(htmlspecialchars($user['Role'] ?? '')); ?></span></td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <input type="hidden" name="action" value="Verified user ID <?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <button type="submit" name="verify_user" class="btn btn-sm btn-success">Verify</button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <input type="hidden" name="action" value="Rejected user ID <?php echo htmlspecialchars($user['User_ID']); ?>">
                                            <button type="submit" name="reject_user" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">No pending users found.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar-link');

            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                
                    if (href.startsWith('#')) {
                         e.preventDefault();

                   
                        sidebarLinks.forEach(l => l.classList.remove('active'));

                       
                        this.classList.add('active');

                        
                        const targetId = href.substring(1);

                    
                        document.getElementById(targetId).scrollIntoView({
                            behavior: 'smooth'
                        });
                         
                         history.pushState(null, null, href);
                    } else if (href === '../logout.php') {
                        
                        return true;
                    } else {
                         
                         sidebarLinks.forEach(l => l.classList.remove('active'));
                         
                    }
                });
            });
          
            const hash = window.location.hash;
            if (hash) {
                const targetLink = document.querySelector(`.sidebar-link[href="${hash}"]`);
                if (targetLink) {
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    targetLink.classList.add('active');
                     
                     window.addEventListener('load', () => {
                         document.getElementById(hash.substring(1)).scrollIntoView({
                             behavior: 'smooth'
                         });
                     });
                }
            } else {
                 
                 const dashboardLink = document.querySelector('.sidebar-link[href="#dashboard"]');
                 if (dashboardLink) {
                     dashboardLink.classList.add('active');
                 }
            }
          
        });
    </script>
</body>
</html>

<?php

if (isset($conn) && $conn) {
    $conn->close();
}

?>
