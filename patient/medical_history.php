<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    
    header("Location: ../index.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$email = $_SESSION['email'];


$conn = new mysqli('localhost', 'root', '', 'homecarebd');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$success_message = $error_message = "";
$medical_history = null;


$stmt = $conn->prepare("SELECT * FROM medical_history WHERE Patient_ID = ? ORDER BY Updated_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$medical_history = $result->fetch_assoc();
$stmt->close();


$stmt = $conn->prepare("SELECT * FROM medical_history WHERE Patient_ID = ? ORDER BY Updated_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history_result = $stmt->get_result();
$history_records = [];
while ($row = $history_result->fetch_assoc()) {
    $history_records[] = $row;
}
$stmt->close();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_medical'])) {

    $condition = trim(htmlspecialchars($_POST['condition'] ?? ''));
    $medication = trim(htmlspecialchars($_POST['medication'] ?? ''));
    $allergies = trim(htmlspecialchars($_POST['allergies'] ?? ''));
    $blood_group = trim(htmlspecialchars($_POST['blood_group'] ?? ''));
    $height = !empty($_POST['height']) ? (float)$_POST['height'] : null;
    $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $emergency_contact = trim(htmlspecialchars($_POST['emergency_contact'] ?? ''));
    $additional_notes = trim(htmlspecialchars($_POST['additional_notes'] ?? ''));
    

    $stmt = $conn->prepare("INSERT INTO medical_history (`Patient_ID`, `Condition`, `Medication`, `Allergies`, `Blood_Group`, `Height`, `Weight`, `Emergency_Contact`, `Additional_Notes`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssdiss", $user_id, $condition, $medication, $allergies, $blood_group, $height, $weight, $emergency_contact, $additional_notes);
    
    if ($stmt->execute()) {
        $success_message = "Medical history updated successfully!";
        

        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM medical_history WHERE Patient_ID = ? ORDER BY Updated_at DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $medical_history = $result->fetch_assoc();
        

        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM medical_history WHERE Patient_ID = ? ORDER BY Updated_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $history_result = $stmt->get_result();
        $history_records = [];
        while ($row = $history_result->fetch_assoc()) {
            $history_records[] = $row;
        }
    } else {
        $error_message = "Error updating medical history: " . $conn->error;
    }
    
    $stmt->close();
}


$stmt = $conn->prepare("SELECT COUNT(*) as notification_count FROM notifications 
                        WHERE User_ID = ? AND Status = 'unread'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notification_data = $result->fetch_assoc();
$unread_notifications = $notification_data['notification_count'];
$stmt->close();


$stmt = $conn->prepare("SELECT COUNT(*) as message_count FROM messages 
                        WHERE Receiver_ID = ? AND Status = 'unread'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$message_data = $result->fetch_assoc();
$unread_messages = isset($message_data['message_count']) ? $message_data['message_count'] : 0;
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical History - Home Care BD</title>

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
        
        .medical-info {
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
            margin-bottom: 15px;
        }
        
        .history-item {
            padding: 15px;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 15px;
            background-color: #f8f9fa;
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
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
                        <a class="nav-link active" href="medical_history.php">
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
                    <h2>Medical History</h2>
                    <div class="d-flex">
                        <div class="icon-container">
                            <a href="notifications.php" class="text-dark">
                                <i class="fas fa-bell small-icon"></i>
                                <?php if ($unread_notifications > 0): ?>
                                <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="icon-container">
                            <a href="messages.php" class="text-dark">
                                <i class="fas fa-envelope small-icon"></i>
                                <?php if ($unread_messages > 0): ?>
                                <span class="notification-badge"><?php echo $unread_messages; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="dashboard-card mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Current Medical Information</h4>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateMedicalModal">
                            <i class="fas fa-edit me-2"></i>Update Medical Info
                        </button>
                    </div>
                    
                    <?php if ($medical_history): ?>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-heartbeat me-2"></i>Medical Condition</h6>
                                    <p><?php echo htmlspecialchars($medical_history['Condition'] ?? 'Not specified'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-pills me-2"></i>Medications</h6>
                                    <p><?php echo htmlspecialchars($medical_history['Medication'] ?? 'None'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Allergies</h6>
                                    <p><?php echo htmlspecialchars($medical_history['Allergies'] ?? 'None'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-tint me-2"></i>Blood Group</h6>
                                    <p><?php echo htmlspecialchars($medical_history['Blood_Group'] ?? 'Not specified'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-ruler me-2"></i>Height</h6>
                                    <p><?php echo ($medical_history['Height'] ?? 'Not specified') . ($medical_history['Height'] ? ' cm' : ''); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-weight me-2"></i>Weight</h6>
                                    <p><?php echo ($medical_history['Weight'] ?? 'Not specified') . ($medical_history['Weight'] ? ' kg' : ''); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h6>
                                    <p><?php echo htmlspecialchars($medical_history['Emergency_Contact'] ?? 'Not specified'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="medical-info">
                                    <h6><i class="fas fa-clipboard me-2"></i>Additional Notes</h6>
                                    <p><?php echo htmlspecialchars($medical_history['Additional_Notes'] ?? 'None'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="text-muted mt-2">
                            <small>Last updated: <?php echo date('F d, Y h:i A', strtotime($medical_history['Updated_at'])); ?></small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No medical information available. Please update your medical history.</div>
                        <div class="text-center">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateMedicalModal">
                                Add Medical Information
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (count($history_records) > 1): ?>
                <div class="dashboard-card">
                    <h4 class="mb-3">Medical History Changes</h4>
                    <div class="accordion" id="historyAccordion">
                        <?php 
    
                        array_shift($history_records);
                        $counter = 1;
                        foreach ($history_records as $record): 
                        ?>
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading<?php echo $counter; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?php echo $counter; ?>" aria-expanded="false" 
                                        aria-controls="collapse<?php echo $counter; ?>">
                                    Medical record from <?php echo date('F d, Y h:i A', strtotime($record['Updated_at'])); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $counter; ?>" class="accordion-collapse collapse" 
                                 aria-labelledby="heading<?php echo $counter; ?>" data-bs-parent="#historyAccordion">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Medical Condition:</strong> <?php echo htmlspecialchars($record['Condition'] ?? 'Not specified'); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Medications:</strong> <?php echo htmlspecialchars($record['Medication'] ?? 'None'); ?>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Allergies:</strong> <?php echo htmlspecialchars($record['Allergies'] ?? 'None'); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Blood Group:</strong> <?php echo htmlspecialchars($record['Blood_Group'] ?? 'Not specified'); ?>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Height:</strong> <?php echo ($record['Height'] ?? 'Not specified') . ($record['Height'] ? ' cm' : ''); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Weight:</strong> <?php echo ($record['Weight'] ?? 'Not specified') . ($record['Weight'] ? ' kg' : ''); ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Emergency Contact:</strong> <?php echo htmlspecialchars($record['Emergency_Contact'] ?? 'Not specified'); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Additional Notes:</strong> <?php echo htmlspecialchars($record['Additional_Notes'] ?? 'None'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                        $counter++;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    

    <div class="modal fade" id="updateMedicalModal" tabindex="-1" aria-labelledby="updateMedicalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateMedicalModalLabel">Update Medical Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="condition">Medical Condition</label>
                                    <textarea class="form-control" id="condition" name="condition" rows="3"><?php echo htmlspecialchars($medical_history['Condition'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="medication">Medications</label>
                                    <textarea class="form-control" id="medication" name="medication" rows="3"><?php echo htmlspecialchars($medical_history['Medication'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="allergies">Allergies</label>
                                    <textarea class="form-control" id="allergies" name="allergies" rows="3"><?php echo htmlspecialchars($medical_history['Allergies'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="blood_group">Blood Group</label>
                                    <select class="form-select" id="blood_group" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" <?php echo ($medical_history['Blood_Group'] ?? '') == 'A+' ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo ($medical_history['Blood_Group'] ?? '') == 'A-' ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo ($medical_history['Blood_Group'] ?? '') == 'B+' ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo ($medical_history['Blood_Group'] ?? '') == 'B-' ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo ($medical_history['Blood_Group'] ?? '') == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo ($medical_history['Blood_Group'] ?? '') == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                        <option value="O+" <?php echo ($medical_history['Blood_Group'] ?? '') == 'O+' ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo ($medical_history['Blood_Group'] ?? '') == 'O-' ? 'selected' : ''; ?>>O-</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="height">Height (cm)</label>
                                    <input type="number" class="form-control" id="height" name="height" step="0.01" value="<?php echo htmlspecialchars($medical_history['Height'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="weight">Weight (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.01" value="<?php echo htmlspecialchars($medical_history['Weight'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="emergency_contact">Emergency Contact</label>
                                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" value="<?php echo htmlspecialchars($medical_history['Emergency_Contact'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="additional_notes">Additional Notes</label>
                                    <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3"><?php echo htmlspecialchars($medical_history['Additional_Notes'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_medical" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>