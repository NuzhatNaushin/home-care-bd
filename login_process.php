<?php

session_start();


$conn = new mysqli('localhost', 'root', '', 'homecarebd');


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;
    

    if (empty($email) || empty($password)) {
        header("Location: index.php?error=empty");
        exit();
    }
    
   
    $stmt = $conn->prepare("SELECT User_ID, Name, Email, Password, Role FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
 
        if ($password === $user['Password']) {  
     
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['name'] = $user['Name'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['role'] = $user['Role'];
            
            
            if ($remember) {
                $token = bin2hex(random_bytes(16)); 
                
                setcookie('remember_token', $token, time() + (86400 * 30), "/");
                setcookie('user_email', $email, time() + (86400 * 30), "/");

            }
            
  
            if ($user['Role'] === 'admin') {
                $stmt = $conn->prepare("UPDATE admin SET Last_login = NOW() WHERE User_ID = ?");
                $stmt->bind_param("i", $user['User_ID']);
                $stmt->execute();
            }
            

            switch ($user['Role']) {
                case 'admin':
                    header("Location: admin/dashboard (1).php");
                    break;
                case 'patient':
                    header("Location: patient/dashboard.php");
                    break;
                case 'attendant':
                    header("Location: attendant/dashboard.php");
                    break;
                default:
                    header("Location: index.php");
                    break;
            }
            exit();
            
        } else {
    
            header("Location: index.php?error=invalid");
            exit();
        }
    } else {
 
        header("Location: index.php?error=invalid");
        exit();
    }
    
    $stmt->close();
} else {

    header("Location: index.php");
    exit();
}

$conn->close();
?>