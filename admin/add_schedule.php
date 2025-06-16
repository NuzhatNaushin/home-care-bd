<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); 
    exit();
}

require_once '../dbconnect.php'; 


$users = [];
$sql_users = "SELECT User_ID, Name, Role FROM users ORDER BY Name";
$result_users = $conn->query($sql_users);

if ($result_users === false) {

    $error_message = "Error fetching users: " . $conn->error;
    $result_users = false; 
} else {
    if ($result_users->num_rows > 0) {
        while($row = $result_users->fetch_assoc()) {
            $users[] = $row;
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
    <title>Add Schedule - Admin - HomeCare BD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .schedule-form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <div class="schedule-form-container">
        <h2 class="text-center mb-4">Add New Schedule</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <?php
                    $error_msg = htmlspecialchars($_GET['error']);
                    if ($error_msg === 'empty_schedule_fields') echo 'Please fill in all required fields.';
                    else if ($error_msg === 'invalid_schedule_times') echo 'Invalid start or end time.';
                    else echo 'An error occurred: ' . $error_msg;
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

         <?php if (isset($error_message)):  ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <form action="../add_schedule.php" method="POST">
            <div class="mb-3">
                <label for="user_id" class="form-label">Select User:</label>
                 <?php if ($result_users !== false && count($users) > 0):  ?>
                    <select class="form-select" id="user_id" name="user_id" required>
                        <option value="">-- Select User --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['User_ID']); ?>">
                                <?php echo htmlspecialchars($user['Name']); ?> (<?php echo ucfirst(htmlspecialchars($user['Role'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                 <?php else: ?>
                     <p class="text-danger">Could not load users. Please try again later.</p>
                     <select class="form-select" id="user_id" name="user_id" required disabled>
                         <option value="">-- No Users Available --</option>
                     </select>
                 <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Start Time:</label>
                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
            </div>

            <div class="mb-3">
                <label for="end_time" class="form-label">End Time:</label>
                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="add_schedule" class="btn btn-primary">Add Schedule</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
