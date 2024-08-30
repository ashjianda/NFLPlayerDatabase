<?php
session_start();
require_once('header.php');


try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['new-email'];
    $password = $_POST['new-password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {

        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email);  
        $stmt->execute();
        
        $count = $stmt->fetchColumn();

        if($count > 0){
            $_SESSION['flag'] = true;
            header("Location: signup_form.php");
            exit();
        } else {
            $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
            $stmt->bindValue(':email', $email);  
            $stmt->bindValue(':password', $hashed_password);  
            
            $stmt->execute();

            header("Location: login_form.php");
            exit();
        }
    } catch (PDOException $e) {
        exit();
    }
}

?>