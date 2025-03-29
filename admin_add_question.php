<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
}

$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_question'])) {
        $num1 = $_POST['num1'];
        $num2 = $_POST['num2'];
        $num3 = $_POST['num3'];
        $num4 = $_POST['num4'];

        // 获取当前最大序号
        $sqlMaxId = "SELECT MAX(id) as max_id FROM questions";
        $resultMaxId = $conn->query($sqlMaxId);
        $rowMaxId = $resultMaxId->fetch_assoc();
        $newId = $rowMaxId['max_id'] + 1;

        $sql = "INSERT INTO questions (id, num1, num2, num3, num4) VALUES ('$newId', '$num1', '$num2', '$num3', '$num4')";
        if ($conn->query($sql) === TRUE) {
            echo "题目添加成功";
        } else {
            echo "题目添加失败: " . $conn->error;
        }
    } elseif (isset($_POST['generate_random_questions'])) {
        $difficulty = $_POST['difficulty'];
        $range = $_POST['range'];
        $quantity = $_POST['quantity'];

        $questions_to_display = [];
        $attempts = 0;
        $max_total_attempts = 1000 * $quantity;
        while (count($questions_to_display) < $quantity && $attempts < $max_total_attempts) {
            $question = generateRandomQuestion($difficulty, $range);
            if ($question) {
                $questions_to_display[] = $question;
            }
            $attempts++;
        }

        if (!empty($questions_to_display)) {
            echo "<script>
                var questionList = `";
            foreach ($questions_to_display as $index => $question) {
                $num1 = $question['num1'];
                $num2 = $question['num2'];
                $num3 = $question['num3'];
                $num4 = $question['num4'];
                $solution = $question['solution'];
                echo "<input type='checkbox' name='selected_questions[]' value='{$num1},{$num2},{$num3},{$num4},{$solution}'> {$num1}, {$num2}, {$num3}, {$num4} 解法：{$solution}<br>";
            }
            echo "`;
                var confirmInsert = confirm('是否插入这些题目？');
                if (confirmInsert) {
                    var selectedQuestions = [];
                    var checkboxes = document.querySelectorAll('input[name=\"selected_questions[]\"]:checked');
                    checkboxes.forEach(function(checkbox) {
                        selectedQuestions.push(checkbox.value);
                    });
                    if (selectedQuestions.length > 0) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'insert_selected_questions.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState == 4 && xhr.status == 200) {
                                alert(xhr.responseText);
                                location.reload();
                            }
                        };
                        var data = 'selected_questions=' + selectedQuestions.join(',');
                        xhr.send(data);
                    }
                }
            </script>";
        } else {
            echo "<script>alert('生成题目失败，请尝试降低难度或减少数量。');</script>";
        }
    }
}

function generateRandomQuestion($difficulty, $range) {
    $min = 1;
    $max = ($range == '1-10') ? 10 : 13;

    $attempts = 0;
    $max_attempts = 1000;
    while ($attempts < $max_attempts) {
        $num1 = rand($min, $max);
        $num2 = rand($min, $max);
        $num3 = rand($min, $max);
        $num4 = rand($min, $max);

        $solution = findSolution($num1, $num2, $num3, $num4, $difficulty);
        if ($solution) {
            return [
                'num1' => $num1,
                'num2' => $num2,
                'num3' => $num3,
                'num4' => $num4,
                'solution' => $solution
            ];
        }
        $attempts++;
    }
    return null;
}

function findSolution($num1, $num2, $num3, $num4, $difficulty) {
    switch ($difficulty) {
        case 'easy':
            return findSolutionEasy($num1, $num2, $num3, $num4);
        case 'medium':
            return findSolutionMedium($num1, $num2, $num3, $num4);
        case 'hard':
            return findSolutionHard($num1, $num2, $num3, $num4);
        default:
            return false;
    }
}

function findSolutionEasy($num1, $num2, $num3, $num4) {
    $numbers = [$num1, $num2, $num3, $num4];
    $operators = ['+', '-', '*', '/'];
    $permutations = getPermutations($numbers);
    foreach ($permutations as $perm) {
        foreach ($operators as $op1) {
            foreach ($operators as $op2) {
                foreach ($operators as $op3) {
                    $expressions = [
                        "(({$perm[0]}{$op1}{$perm[1]}){$op2}{$perm[2]}){$op3}{$perm[3]}",
                        "({$perm[0]}{$op1}({$perm[1]}{$op2}{$perm[2]})){$op3}{$perm[3]}",
                        "{$perm[0]}{$op1}(({$perm[1]}{$op2}{$perm[2]}){$op3}{$perm[3]})",
                        "{$perm[0]}{$op1}({$perm[1]}{$op2}({$perm[2]}{$op3}{$perm[3]}))",
                        "({$perm[0]}{$op1}{$perm[1]}){$op2}({$perm[2]}{$op3}{$perm[3]})"
                    ];
                    foreach ($expressions as $expr) {
                        try {
                            if (eval("return $expr;") == 24) {
                                return $expr;
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function findSolutionMedium($num1, $num2, $num3, $num4) {
    $numbers = [$num1, $num2, $num3, $num4];
    $operators = ['+', '-', '*', '/'];
    $permutations = getPermutations($numbers);
    foreach ($permutations as $perm) {
        foreach ($operators as $op1) {
            foreach ($operators as $op2) {
                foreach ($operators as $op3) {
                    $expressions = [
                        "(({$perm[0]}{$op1}{$perm[1]}){$op2}{$perm[2]}){$op3}{$perm[3]}",
                        "({$perm[0]}{$op1}({$perm[1]}{$op2}{$perm[2]})){$op3}{$perm[3]}",
                        "{$perm[0]}{$op1}(({$perm[1]}{$op2}{$perm[2]}){$op3}{$perm[3]})",
                        "{$perm[0]}{$op1}({$perm[1]}{$op2}({$perm[2]}{$op3}{$perm[3]}))",
                        "({$perm[0]}{$op1}{$perm[1]}){$op2}({$perm[2]}{$op3}{$perm[3]})"
                    ];
                    foreach ($expressions as $expr) {
                        try {
                            $result = eval("return $expr;");
                            if (is_numeric($result) && abs($result - 24) < 0.0001) {
                                return $expr;
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function findSolutionHard($num1, $num2, $num3, $num4) {
    $numbers = [$num1, $num2, $num3, $num4];
    $operators = ['+', '-', '*', '/'];
    $permutations = getPermutations($numbers);
    foreach ($permutations as $perm) {
        foreach ($operators as $op1) {
            foreach ($operators as $op2) {
                foreach ($operators as $op3) {
                    $expressions = [
                        "(({$perm[0]}{$op1}{$perm[1]}){$op2}{$perm[2]}){$op3}{$perm[3]}",
                        "({$perm[0]}{$op1}({$perm[1]}{$op2}{$perm[2]})){$op3}{$perm[3]}",
                        "{$perm[0]}{$op1}(({$perm[1]}{$op2}{$perm[2]}){$op3}{$perm[3]})",
                        "{$perm[0]}{$op1}({$perm[1]}{$op2}({$perm[2]}{$op3}{$perm[3]}))",
                        "({$perm[0]}{$op1}{$perm[1]}){$op2}({$perm[2]}{$op3}{$perm[3]})"
                    ];
                    foreach ($expressions as $expr) {
                        try {
                            for ($i = 0; $i < 16; $i++) {
                                $new_expr = $expr;
                                if ($i & 1) {
                                    $new_expr = str_replace($perm[0], "sqrt({$perm[0]})", $new_expr);
                                }
                                if ($i & 2) {
                                    $new_expr = str_replace($perm[1], "sqrt({$perm[1]})", $new_expr);
                                }
                                if ($i & 4) {
                                    $new_expr = str_replace($perm[2], "sqrt({$perm[2]})", $new_expr);
                                }
                                if ($i & 8) {
                                    $new_expr = str_replace($perm[3], "sqrt({$perm[3]})", $new_expr);
                                }
                                $result = eval("return $new_expr;");
                                if (is_numeric($result) && abs($result - 24) < 0.0001) {
                                    return $new_expr;
                                }
                            }
                        } catch (Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function getPermutations($array) {
    $result = [];
    $permute = function ($items, $perms = []) use (&$permute, &$result) {
        if (empty($items)) {
            $result[] = $perms;
        } else {
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $permute($newitems, $newperms);
            }
        }
    };
    $permute($array);
    return $result;
}

$sql = "SELECT * FROM questions ORDER BY id";
$result = $conn->query($sql);
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

function replaceSpacesWithPlus($solution) {
    return str_replace(' ', '+', $solution);
}




$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加题目</title>
    <style>
        /* 设置根元素字体大小，方便使用 rem 单位 */
        html {
            font-size: 16px;
        }

        /* 全局样式 */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        /* 导航栏样式 */
        nav {
            background-color: #333;
            overflow: hidden;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1;
            display: flex;
            justify-content: center;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
            flex-wrap: wrap;
        }

        nav a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 0.875rem 1rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
            flex: 1 0 auto;
        }

        nav a:hover {
            background-color: #ddd;
            color: black;
        }


        /* 内容容器样式 */
        .container {
            max-width: 75rem;
            /* 1200px 转换为 rem */
            margin: 5rem auto 1.25rem;
            /* 80px 20px 转换为 rem */
            padding: 1.25rem;
            /* 20px 转换为 rem */
            background-color: #fff;
            border-radius: 0.5rem;
            /* 8px 转换为 rem */
            box-shadow: 0 0 0.625rem rgba(0, 0, 0, 0.1);
            /* 10px 转换为 rem */
        }

        /* 标题样式 */
        h1,
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.25rem;
            /* 20px 转换为 rem */
        }

        /* 表单样式 */
        form {
            margin-bottom: 1.25rem;
            /* 20px 转换为 rem */
        }

        input[type="number"],
        input[type="submit"],
        button,
        select {
            padding: 0.625rem 0.9375rem;
            /* 10px 15px 转换为 rem */
            border: 1px solid #ccc;
            border-radius: 0.3125rem;
            /* 5px 转换为 rem */
            font-size: 1rem;
            /* 16px 转换为 rem */
            margin: 0.3125rem;
            /* 5px 转换为 rem */
        }

        input[type="number"] {
            width: 6.25rem;
            /* 100px 转换为 rem */
        }

        input[type="submit"],
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #0056b3;
        }

        /* 表格样式 */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 0.5rem;
            /* 8px 转换为 rem */
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* 媒体查询，当屏幕宽度小于 768px 时的样式调整 */
        @media (max-width: 768px) {
            html {
                font-size: 14px;
            }

            nav {
                flex-wrap: wrap;
            }

            nav a {
                width: 50%;
            }

            input[type="number"] {
                width: calc(50% - 0.625rem);
                /* 减去 margin 的值 */
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .container {
                margin-top: 17rem;
            }
        }
    </style>
    <script>
        document.getElementById('select-all').addEventListener('change', function () {
            var checkboxes = document.querySelectorAll('input[name="question_ids[]"]');
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = this.checked;
            }, this);
        });

        function generateRandomQuestions() {
            var difficulty = document.querySelector('select[name="difficulty"]').value;
            var range = document.querySelector('select[name="range"]').value;
            var quantity = document.querySelector('input[name="quantity"]').value;

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'generate_questions.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        var questionList = xhr.responseText;
                        var popup = document.createElement('div');
                        popup.style.position = 'fixed';
                        popup.style.top = '50%';
                        popup.style.left = '50%';
                        popup.style.transform = 'translate(-50%, -50%)';
                        popup.style.backgroundColor = 'white';
                        popup.style.padding = '20px';
                        popup.style.border = '1px solid #ccc';
                        popup.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.2)';
                        popup.style.zIndex = '1000';

                        var title = document.createElement('h2');
                        title.textContent = '生成的题目';
                        popup.appendChild(title);

                        var questionDiv = document.createElement('div');
                        questionDiv.innerHTML = questionList;
                        popup.appendChild(questionDiv);

                        var selectAllButton = document.createElement('button');
                        selectAllButton.textContent = '全选';
                        selectAllButton.onclick = function () {
                            var checkboxes = questionDiv.querySelectorAll('input[type="checkbox"]');
                            checkboxes.forEach(function (checkbox) {
                                checkbox.checked = true;
                            });
                        };
                        popup.appendChild(selectAllButton);

                        var deleteAllButton = document.createElement('button');
                        deleteAllButton.textContent = '全部删除';
                        deleteAllButton.onclick = function () {
                            var checkboxes = questionDiv.querySelectorAll('input[type="checkbox"]');
                            checkboxes.forEach(function (checkbox) {
                                checkbox.parentNode.remove();
                            });
                        };
                        popup.appendChild(deleteAllButton);

                        var regenerateButton = document.createElement('button');
                        regenerateButton.textContent = '重新生成';
                        regenerateButton.onclick = function () {
                            generateRandomQuestions();
                            popup.parentNode.removeChild(popup);
                        };
                        popup.appendChild(regenerateButton);

                        var insertButton = document.createElement('button');
                        insertButton.textContent = '插入选择好的题目';
                        insertButton.onclick = function () {
                            var selectedQuestions = [];
                            var checkboxes = questionDiv.querySelectorAll('input[type="checkbox"]:checked');
                            checkboxes.forEach(function (checkbox) {
                                selectedQuestions.push(checkbox.value);
                            });
                            if (selectedQuestions.length > 0) {
                                var xhrInsert = new XMLHttpRequest();
                                xhrInsert.open('POST', 'insert_selected_questions.php', true);
                                xhrInsert.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                xhrInsert.onreadystatechange = function () {
                                    if (xhrInsert.readyState == 4 && xhrInsert.status == 200) {
                                        alert(xhrInsert.responseText);
                                        location.reload();
                                    }
                                };
                                var data = 'selected_questions=' + selectedQuestions.join(',');
                                xhrInsert.send(data);
                            } else {
                                alert('请选择要插入的题目');
                            }
                        };
                        popup.appendChild(insertButton);

                        var closeButton = document.createElement('button');
                        closeButton.textContent = '关闭';
                        closeButton.onclick = function () {
                            popup.parentNode.removeChild(popup);
                        };
                        popup.appendChild(closeButton);

                        document.body.appendChild(popup);
                    } else {
                        console.error('AJAX 请求失败，状态码: ', xhr.status);
                        alert('哎呀！脑子不够用了，请再拍一下我的脑袋（重新点击“生成随机题目”按钮）。');
                    }
                }
            };
            var data = 'difficulty=' + difficulty + '&range=' + range + '&quantity=' + quantity;
            xhr.send(data);
        }
    </script>
</head>

<body>
    <nav>
        <a href="admin_add_announcement.php">添加比赛名称</a>
        <a href="admin_add_question.php">添加题目</a>
        <a href="admin_start_game.php">开始比赛</a>
        <a href="admin_rank.php">实时排名</a>
        <a href="admin_clear_data.php">清除数据</a>
        <a href="admin_logout.php">退出登录</a>
    </nav>
    <div class="container">
        <h1>添加题目</h1>
        <form action="admin_add_question.php" method="post">
            <input type="number" id="num1" name="num1" max="99" min="1" required>
            <input type="number" id="num2" name="num2" max="99" min="1" required>
            <input type="number" id="num3" name="num3" max="99" min="1" required>
            <input type="number" id="num4" name="num4" max="99" min="1" required>
            <input type="submit" name="add_question" value="添加题目">
            <select name="difficulty">
                <option value="easy">简单</option>
                <option value="medium">中等</option>
                <option value="hard">困难</option>
            </select>
            <select name="range">
                <option value="1-10">1 - 10</option>
                <option value="1-13">1 - 13</option>
            </select>
            <input type="number" name="quantity" min="1" value="1" placeholder="题目数量">
            <input type="button" value="生成随机题目" onclick="generateRandomQuestions()">
        </form>

        <h2>题目列表</h2>
        <form action="admin_delete_questions.php" method="post">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>题目 ID</th>
                        <th>数字 1</th>
                        <th>数字 2</th>
                        <th>数字 3</th>
                        <th>数字 4</th>
                        <th>解法</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $question): ?>
                        <tr>
                            <td><input type="checkbox" name="question_ids[]" value="<?php echo $question['id']; ?>"></td>
                            <td><?php echo $question['id']; ?></td>
                            <td><?php echo $question['num1']; ?></td>
                            <td><?php echo $question['num2']; ?></td>
                            <td><?php echo $question['num3']; ?></td>
                            <td><?php echo $question['num4']; ?></td>
                            <td><?php echo replaceSpacesWithPlus(htmlspecialchars_decode($question['solution'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="delete">删除选中题目</button>
            <button type="submit" name="delete_all">全部删除</button>
        </form>
    </div>
</body>

</html>