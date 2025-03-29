<?php
require_once 'config.php';

// 获取选中的题目
$selectedQuestions = explode(',', $_POST['selected_questions']);
$conn = getDatabaseConnection();

for ($i = 0; $i < count($selectedQuestions); $i += 5) {
    $num1 = mysqli_real_escape_string($conn, $selectedQuestions[$i]);
    $num2 = mysqli_real_escape_string($conn, $selectedQuestions[$i + 1]);
    $num3 = mysqli_real_escape_string($conn, $selectedQuestions[$i + 2]);
    $num4 = mysqli_real_escape_string($conn, $selectedQuestions[$i + 3]);
    $solution = mysqli_real_escape_string($conn, $selectedQuestions[$i + 4]);

    // 获取当前最大序号
    $sqlMaxId = "SELECT MAX(id) as max_id FROM questions";
    $resultMaxId = $conn->query($sqlMaxId);
    $rowMaxId = $resultMaxId->fetch_assoc();
    $newId = $rowMaxId['max_id'] + 1;

    // 插入数据的 SQL 语句
    $sql = "INSERT INTO questions (id, num1, num2, num3, num4, solution) VALUES ('$newId', '$num1', '$num2', '$num3', '$num4', '$solution')";
    if ($conn->query($sql) !== TRUE) {
        echo "插入题目失败: ". $conn->error;
        break;
    }
}

echo "题目插入成功";
$conn->close();
?>