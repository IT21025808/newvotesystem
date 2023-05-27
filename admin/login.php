<?php
session_start();
include 'includes/conn.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user has reached the maximum number of failed attempts
    if(isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 3) {
        $lastAttemptTime = isset($_SESSION['last_attempt_time']) ? $_SESSION['last_attempt_time'] : 0;
        $currentTime = time();
        $delaySeconds = 60; // Delay in seconds
        
        if($currentTime - $lastAttemptTime < $delaySeconds) {
            $_SESSION['error'] = "Too many failed login attempts. Please wait $delaySeconds seconds and try again.";
            header('location: index.php');
            exit();
        }
        
        // Reset the login attempts counter
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['last_attempt_time']);
    }

    $sql = "SELECT * FROM admin WHERE username = '$username'";
    $query = $conn->query($sql);

    if($query->num_rows < 1){
        $_SESSION['error'] = 'Cannot find an account with the provided username.';
        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? ($_SESSION['login_attempts'] + 1) : 1;
        $_SESSION['last_attempt_time'] = time();
    }
    else{
        $row = $query->fetch_assoc();
        if(password_verify($password, $row['password'])){
            $_SESSION['admin'] = $row['id'];
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt_time']);
        }
        else{
            $_SESSION['error'] = 'Incorrect password';
            $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? ($_SESSION['login_attempts'] + 1) : 1;
            $_SESSION['last_attempt_time'] = time();
        }
    }
    
}
else{
    $_SESSION['error'] = 'Please input admin credentials first.';
}

header('location: index.php');
exit();
?>