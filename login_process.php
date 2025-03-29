<?php
session_start();
require_once 'config.php';

$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "密码错误";
        }
    } else {
        $_SESSION['error'] = "用户不存在";
    }

    $stmt->close();
    $conn->close();
    
    // 重定向回登录页面并传递错误信息
    header("Location: login.php");
    exit;
}
?>