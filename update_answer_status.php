<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('非法请求');

$conn = getDatabaseConnection();

$user_id = $_POST['user_id'] ?? 0;
$question_id = $_POST['question_id'] ?? 0;
$is_correct = $_POST['is_correct'] ?? 0;

if (!$user_id || !$question_id) die('缺少参数');

// 防止重复提交
$sql_check = "SELECT id FROM user_answers 
              WHERE user_id = $user_id AND question_id = $question_id";
if ($conn->query($sql_check)->num_rows > 0) {
    echo 'success';
    exit;
}

// 插入答题记录
$sql = "INSERT INTO user_answers (user_id, question_id, is_correct)
        VALUES ($user_id, $question_id, $is_correct)";

if ($conn->query($sql) === TRUE) {
    // 同步更新correct_count（可选，也可直接统计user_answers）
    if ($is_correct) {
        $sql_update = "UPDATE answers SET correct_count = correct_count + 1 
                       WHERE user_id = $user_id";
        $conn->query($sql_update);
    }
    echo 'success';
} else {
    echo '数据库错误: ' . $conn->error;
}

$conn->close();
?>