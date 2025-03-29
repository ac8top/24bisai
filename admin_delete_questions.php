<?php
require_once 'config.php';

$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        $questionIds = $_POST['question_ids'];
        foreach ($questionIds as $id) {
            $sql = "DELETE FROM questions WHERE id = $id";
            if ($conn->query($sql) !== TRUE) {
                echo "删除题目失败: " . $conn->error;
                $conn->close();
                return;
            }
        }
        // 重新排列序号
        $sql = "SET @count = 0;";
        $conn->query($sql);
        $sql = "UPDATE questions SET id = @count:= @count + 1 ORDER BY id;";
        if ($conn->query($sql) !== TRUE) {
            echo "重新排列序号失败: " . $conn->error;
        }
        echo "题目删除成功";
    } elseif (isset($_POST['delete_all'])) {
        $sql = "DELETE FROM questions";
        if ($conn->query($sql) === TRUE) {
            // 重置自增 ID
            $sql = "ALTER TABLE questions AUTO_INCREMENT = 1;";
            if ($conn->query($sql) !== TRUE) {
                echo "重置自增 ID 失败: " . $conn->error;
            }
            echo "所有题目删除成功";
        } else {
            echo "删除所有题目失败: " . $conn->error;
        }
    }
}
$conn->close();
header("Location: admin_add_question.php");
?>