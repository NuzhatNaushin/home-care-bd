<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Attendant';

require_once '../dbconnect.php';


if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$messages = null;
$error_message = '';

$stmt = $conn->prepare("SELECT m.*,
                       sender_u.Name as SenderName, sender_u.Role as SenderRole,
                       receiver_u.Name as ReceiverName, receiver_u.Role as ReceiverRole
                       FROM messages m
                       JOIN users sender_u ON m.Sender_ID = sender_u.User_ID
                       JOIN users receiver_u ON m.Receiver_ID = receiver_u.User_ID
                       WHERE m.Sender_ID = ? OR m.Receiver_ID = ?
                       ORDER BY m.Created_at DESC");

if ($stmt) {
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $messages = $stmt->get_result();
    $stmt->close();
} else {
    $error_message = "Database error: Could not prepare statement to fetch messages. " . $conn->error;
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Attendant - HomeCareBD</title>
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
        .message-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .message-item:last-child {
            border-bottom: none;
        }
        .message-item .sender-name {
            font-weight: bold;
        }
        .message-item .message-date {
            font-size: 0.8rem;
            color: #666;
        }
        .message-item .message-content {
            margin-top: 5px;
        }
        .message-unread {
            background-color: #fffbe6;
            border-left: 4px solid #ffc107;
            padding-left: 16px;
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
                    <li class="nav-item active">
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
                    </li>
                    <li class="nav-item">
                        <a href="add_attendant_schedule_form.php" class="btn btn-light ml-2 mt-1">Add My Availability</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Messages</h2>
            <a href="compose_message.php" class="btn btn-primary"><i class="fas fa-plus mr-2"></i>Compose Message</a>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="dashboard-card">
            <?php if ($messages && $messages->num_rows > 0): ?>
                <div class="message-list">
                    <?php while ($message = $messages->fetch_assoc()): ?>
                        <div class="message-item <?php echo ($message['Status'] == 'unread' && $message['Receiver_ID'] == $user_id) ? 'message-unread' : ''; ?>">
                            <div class="d-flex justify-content-between">
                                <span class="sender-name">
                                    <?php if ($message['Sender_ID'] == $user_id): ?>
                                        To: <?php echo htmlspecialchars($message['ReceiverName']); ?> (<?php echo htmlspecialchars($message['ReceiverRole']); ?>)
                                    <?php else: ?>
                                        From: <?php echo htmlspecialchars($message['SenderName']); ?> (<?php echo htmlspecialchars($message['ReceiverRole']); ?>)
                                    <?php endif; ?>
                                </span>
                                <span class="message-date"><?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($message['Created_at']))); ?></span>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($message['Message'])); ?>
                            </div>
                            <?php if ($message['Sender_ID'] != $user_id): ?>
                                <div class="text-right mt-2">
                                    <a href="compose_message.php?reply_to=<?php echo htmlspecialchars($message['Sender_ID']); ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-reply mr-1"></i>Reply</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">You have no messages yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>