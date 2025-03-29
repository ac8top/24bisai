<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $correct_count = $_POST['correct_count'];

    $conn = getDatabaseConnection();

    // 检查用户是否已经存在于 answers 表中
    $check_sql = "SELECT id FROM answers WHERE user_id = $user_id";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // 如果存在，则更新正确答题数量
        $update_sql = "UPDATE answers SET correct_count = $correct_count WHERE user_id = $user_id";
        if ($conn->query($update_sql) === TRUE) {
            echo "正确答题数量更新成功";
        } else {
            echo "更新失败: ". $conn->error;
        }
    } else {
        // 如果不存在，则插入新记录
        $insert_sql = "INSERT INTO answers (user_id, correct_count) VALUES ($user_id, $correct_count)";
        if ($conn->query($insert_sql) === TRUE) {
            echo "新记录插入成功";
        } else {
            echo "插入失败: ". $conn->error;
        }
    }

    $conn->close();
}
?>