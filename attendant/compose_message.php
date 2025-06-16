<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    die("Session error: user_id or role not set.");
}

if ($_SESSION['role'] !== 'attendant') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'Attendant';

require_once '../dbconnect.php';


if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$error_message = '';
$success_message = '';
$users = null;


$default_receiver_id = filter_input(INPUT_GET, 'reply_to', FILTER_VALIDATE_INT) ?: null;


if ($default_receiver_id) {
    error_log("Reply to User_ID: " . $default_receiver_id);
}


$stmt = $conn->prepare("SELECT User_ID, Name, Role FROM users WHERE User_ID != ? ORDER BY Role, Name");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $users = $stmt->get_result();
    $stmt->close();
} else {
    $error_message = "Database error: Could not fetch users. " . $conn->error;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
    $message = trim($_POST['message'] ?? '');


    if (!$receiver_id) {
        $error_message = "Please select a valid recipient.";
    } elseif (empty($message)) {
        $error_message = "Message cannot be empty.";
    } else {
  
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


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message - Attendant - HomeCareBD</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f9ff;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
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
            color: #2c4f87;
            margin-bottom: 10px;
            font-size: 1rem;
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
            <h2>Compose Message</h2>
            <a href="messages.php" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i>Back to Messages</a>
        </div>


        <div class="alert alert-info">
            Debug Info: User_ID = <?php echo htmlspecialchars($user_id); ?>, Role = <?php echo htmlspecialchars($_SESSION['role']); ?>,
            Reply_To = <?php echo $default_receiver_id ?: 'None'; ?>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="dashboard-card">
            <form method="post" action="">
                <div class="form-group">
                    <label for="receiver_id" class="font-weight-bold">Select Recipient:</label>
                    <?php if (!$users || $users->num_rows === 0): ?>
                        <div class="alert alert-warning">No recipients available.</div>
                    <?php else: ?>
                        <?php if (!empty($patients)): ?>
                            <div class="recipient-group">
                                <h5><i class="fas fa-user mr-2"></i>Patients</h5>
                                <?php foreach ($patients as $patient): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="receiver_id" id="patient_<?php echo $patient['User_ID']; ?>" value="<?php echo $patient['User_ID']; ?>" <?php echo ($default_receiver_id == $patient['User_ID'] || (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $patient['User_ID'])) ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="patient_<?php echo $patient['User_ID']; ?>">
                                            <?php echo htmlspecialchars($patient['Name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($attendants)): ?>
                            <div class="recipient-group">
                                <h5><i class="fas fa-user-nurse mr-2"></i>Other Attendants</h5>
                                <?php foreach ($attendants as $attendant): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="receiver_id" id="attendant_<?php echo $attendant['User_ID']; ?>" value="<?php echo $attendant['User_ID']; ?>" <?php echo ($default_receiver_id == $attendant['User_ID'] || (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $attendant['User_ID'])) ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="attendant_<?php echo $attendant['User_ID']; ?>">
                                            <?php echo htmlspecialchars($attendant['Name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($admins)): ?>
                            <div class="recipient-group">
                                <h5><i class="fas fa-user-shield mr-2"></i>Administrators</h5>
                                <?php foreach ($admins as $admin): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="receiver_id" id="admin_<?php echo $admin['User_ID']; ?>" value="<?php echo $admin['User_ID']; ?>" <?php echo ($default_receiver_id == $admin['User_ID'] || (isset($_POST['receiver_id']) && $_POST['receiver_id'] == $admin['User_ID'])) ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="admin_<?php echo $admin['User_ID']; ?>">
                                            <?php echo htmlspecialchars($admin['Name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="message" class="font-weight-bold">Message:</label>
                    <textarea class="form-control" id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>

                <div class="form-group text-right">
                    <button type="submit" name="send_message" class="btn btn-primary"><i class="fas fa-paper-plane mr-2"></i>Send Message</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>