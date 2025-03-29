<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDatabaseConnection();
    if (!$conn) {
        die("数据库连接失败: ". mysqli_connect_error());
    }
    $user_id = $_POST['user_id'];
    $user_id = intval($user_id);
    $sql = "UPDATE answers SET correct_count = correct_count + 1 WHERE user_id = $user_id";
    if ($conn->query($sql) === TRUE) {
        echo "更新成功";
    } else {
        echo "更新失败: ". $conn->error;
    }
    $conn->close();
}
?>