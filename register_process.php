<?php
session_start();
require_once 'config.php';

// 输入验证
function validateInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school = validateInput($_POST['school']);
    $grade = validateInput($_POST['grade']);
    $class = validateInput($_POST['class']);
    $name = validateInput($_POST['name']);
    $username = validateInput($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 简单的输入验证，确保所有字段都有值
    if (empty($school) || empty($grade) || empty($class) || empty($name) || empty($username) || empty($_POST['password'])) {
        $_SESSION['error'] = "所有字段都是必填项，请填写完整。";
        header("Location: register.php");
        exit();
    }

    $conn = getDatabaseConnection();

    // 准备预处理语句
    $stmt = $conn->prepare("INSERT INTO users (school, grade, class, name, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $school, $grade, $class, $name, $username, $password);

    if ($stmt->execute()) {
        // 注册成功，设置成功消息
        $_SESSION['success'] = "注册成功！请使用您的用户名和密码登录。";
        header("Location: login.php");
    } else {
        // 注册失败，给出详细错误信息
        $_SESSION['error'] = "注册失败: " . $stmt->error;
        header("Location: register.php");
    }

    $stmt->close();
    $conn->close();
}
?>