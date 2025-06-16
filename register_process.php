<?php

session_start();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homecarebd";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$errors = [];


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $conn->real_escape_string(trim($_POST['name']));
    $username = $conn->real_escape_string(trim($_POST['username']));
    $birth_date = $conn->real_escape_string(trim($_POST['birth_date']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $role = $conn->real_escape_string(trim($_POST['role']));

    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 4) {
        $errors[] = "Password must be at least 4 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match("/^01[0-9]{9}$/", $phone)) {
        $errors[] = "Phone number must be a valid Bangladeshi number (01XXXXXXXXX)";
    }
    
    if (empty($role) || !in_array($role, ['attendant', 'patient'])) {
        $errors[] = "Please select a valid role";
    }
    

    $sql = "SELECT User_ID FROM users WHERE Email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already exists. Please use a different email.";
    }
    
   
    $sql = "SELECT User_ID FROM users WHERE Username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $errors[] = "Username already exists. Please choose a different username.";
    }

    if (empty($errors)) {

        $hashed_password = $password;
        

        $conn->begin_transaction();
        
        try {
 
            $sql = "INSERT INTO users (Name, Username, Birth_date, Email, Password, Phone, Verification_status, Role) 
                    VALUES ('$name', '$username', '$birth_date', '$email', '$hashed_password', '$phone', 'pending', '$role')";
            
            if ($conn->query($sql) === TRUE) {
                $user_id = $conn->insert_id;
                

                if ($role == 'attendant') {
                    $sql = "INSERT INTO attendant (User_ID, Verification_status) 
                            VALUES ($user_id, 'pending')";
                    $conn->query($sql);
                } elseif ($role == 'patient') {
                    $sql = "INSERT INTO patient (User_ID, Verification_status) 
                            VALUES ($user_id, 'pending')";
                    $conn->query($sql);
                }

                $conn->commit();

                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                $_SESSION['success_message'] = "Registration successful! Your account is pending verification.";

                header("Location: dashboard.php");
                exit();
            } else {
                throw new Exception("Error: " . $sql . "<br>" . $conn->error);
            }
        } catch (Exception $e) {

            $conn->rollback();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['form_data'] = $_POST; 
        header("Location: register.php");
        exit();
    }
}

header("Location: register.php");
exit();
?>