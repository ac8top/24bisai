<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

$conn = getDatabaseConnection();

// 获取用户信息
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT username FROM users WHERE id = $user_id";
$result_user = $conn->query($sql_user);
if ($result_user === false) {
    die("查询用户信息失败: ". $conn->error);
}
$user = $result_user->fetch_assoc();
$username = $user['username'];

// 获取管理员设置的开始和结束时间
$sql_time = "SELECT start_time, end_time FROM game_settings";
$result_time = $conn->query($sql_time);
if ($result_time === false) {
    die("查询比赛时间失败: ". $conn->error);
}
$time_row = $result_time->fetch_assoc();
$start_time = $time_row['start_time'];
$end_time = $time_row['end_time'];

// 计算当前时间
$current_time = time();

// 计算剩余时间
$remaining_time = max(0, $end_time - $current_time);

// 获取题目
$sql = "SELECT * FROM questions";
$result = $conn->query($sql);
if ($result === false) {
    die("查询题目失败: ". $conn->error);
}
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

// 检查用户答题记录是否存在，若不存在则插入初始记录
$sql_check_record = "SELECT id FROM answers WHERE user_id = $user_id";
$result_check_record = $conn->query($sql_check_record);
if ($result_check_record->num_rows == 0) {
    $sql_insert_record = "INSERT INTO answers (user_id, correct_count) VALUES ($user_id, 0)";
    if ($conn->query($sql_insert_record) === false) {
        echo "插入用户答题记录失败: ". $conn->error;
    }
}

// 获取用户的正确答题数量
$sql_correct_count = "SELECT correct_count FROM answers WHERE user_id = $user_id";
$result_correct_count = $conn->query($sql_correct_count);
$correct_count = 0;
if ($result_correct_count === false) {
    echo "查询正确答题数量失败: ". $conn->error;
} elseif ($result_correct_count->num_rows > 0) {
    $row_correct_count = $result_correct_count->fetch_assoc();
    $correct_count = $row_correct_count['correct_count'];
}

// 获取用户已正确回答的题目ID
$sql_correct_questions = "SELECT question_id FROM user_answers 
                          WHERE user_id = $user_id AND is_correct = 1";
$result_correct_questions = $conn->query($sql_correct_questions);
if ($result_correct_questions === false) {
    die("查询已正确回答题目失败: ". $conn->error);
}
$correct_question_ids = [];
while ($row = $result_correct_questions->fetch_assoc()) {
    $correct_question_ids[] = $row['question_id'];
}

// 查询比赛名称
$sql_game_name = "SELECT * FROM game_name LIMIT 1";
$stmt_game_name = $conn->prepare($sql_game_name);
$stmt_game_name->execute();
$result_game_name = $stmt_game_name->get_result();
$gameName = "";
if ($result_game_name->num_rows > 0) {
    $row_game_name = $result_game_name->fetch_assoc();
    $gameName = $row_game_name['name'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>答题页面</title>
    <style>
        /* 设置根元素字体大小，方便使用 rem 单位 */
        html {
            font-size: 16px;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 1.25rem;
        }

        .top-info {
            background-color: #fff;
            padding: 0.625rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 0.625rem rgba(0, 0, 0, 0.1);
            margin-bottom: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 50rem;
            margin: 0 auto;
            background-color: #fff;
            padding: 1.25rem;
            border-radius: 0.5rem;
            box-shadow: 0 0 0.625rem rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .question-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            margin-bottom: 1.25rem;
        }

        .question-btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 0.625rem 0.9375rem;
            border-radius: 0.3125rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 1.875rem;
        }

        .question-btn.used-btn {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .question-display {
            border: 1px solid #ddd;
            padding: 1.25rem;
            border-radius: 0.3125rem;
            margin-bottom: 1.25rem;
        }

        .operator-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            margin-bottom: 1.25rem;
        }

        .operator-btns button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 0.3125rem 0.9375rem;
            border-radius: 0.3125rem;
            cursor: pointer;
            font-size: 1.875rem;
        }

        .input-group {
            display: flex;
            gap: 0.625rem;
            align-items: center;
        }

        #input {
            flex: 1;
            padding: 0.625rem;
            border: 1px solid #ddd;
            border-radius: 0.3125rem;
            font-size: 1rem;
            pointer-events: none;
        }

        .action-btns {
            display: flex;
            gap: 0.625rem;
            margin-top: 1.25rem;
        }

        .action-btns button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.625rem 0.9375rem;
            border-radius: 0.3125rem;
            cursor: pointer;
        }

        #countdown-section {
            margin-bottom: 1.25rem;
        }

        #countdown-bar {
            height: 1.25rem;
            background-color: #007BFF;
            border-radius: 0.3125rem;
            transition: width 1s ease;
        }

        nav {
            background-color: #333;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        nav a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 0.875rem 1rem;
            text-decoration: none;
        }

        nav a:hover {
            background-color: #ddd;
            color: black;
        }

        /* 媒体查询，当屏幕宽度小于 600px 时的样式调整 */
        @media (max-width: 600px) {
            html {
                font-size: 14px;
            }

            .top-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .question-btn,
            .operator-btns button {
                font-size: 1.5rem;
            }

            /* 题目序号一排显示 4 个 */
            .question-list {
                justify-content: space-between;
            }

            .question-list button {
                width: calc(25% - 0.625rem);
            }

            /* 运算符按钮分 2 行显示，一行 3 个 */
            .operator-btns button {
                width: calc(33.33% - 0.625rem);
            }
        }

        .action-btn {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

       .logout-btn {
            background-color: #dc3545;
        }

       .logout-btn:hover {
            background-color: #c82333;
        }

        /* 响应式调整 */
        @media (max-width: 100px) {
           .action-btn {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 100px) {
           .action-btn {
                flex-direction: column;
                padding: 1rem;
            }
        }

        /* 设置比赛名称显示样式 */
       .game-name-display {
            text-align: center;
            font-size: 20px;
            font-size: clamp(16px, 2vw, 20px); /* 自适应字体大小 */
            margin: 10px 0;
            color: white; /* 文字颜色 */
            background-color: #007BFF; /* 背景颜色 */
            padding: 10px; /* 内边距 */
            border-radius: 5px; /* 圆角 */
        }
    </style>
    <script>
        var startTimestamp = <?php echo $start_time * 1000; ?>;
        var endTimestamp = <?php echo $end_time * 1000; ?>;
        var totalDuration = endTimestamp - startTimestamp;
        var correctCount = <?php echo $correct_count; ?>;
        var questions = <?php echo json_encode($questions); ?>;
        var correctQuestionIds = <?php echo json_encode($correct_question_ids); ?>;
        var currentQuestionIndex = null;
        var isGameStarted = <?php echo ($current_time >= $start_time)? 'true' : 'false'; ?>;
        var isGameEnded = <?php echo ($current_time >= $end_time)? 'true' : 'false'; ?>;
        var usedButtons = [];
        var remainingQuestionIndices = [];
        var inputButtonMap = [];

        function startCountdown() {
            var now = new Date().getTime();
            var remainingTime = Math.max(0, endTimestamp - now);
            var progress = (remainingTime / totalDuration) * 100;

            document.getElementById('countdown').textContent = Math.floor(remainingTime / 1000) + '秒';
            document.getElementById('countdown-bar').style.width = progress + '%';

            if (remainingTime <= 0) {
                endGame();
                return;
            }

            var countdownInterval = setInterval(function () {
                now = new Date().getTime();
                remainingTime = Math.max(0, endTimestamp - now);
                progress = (remainingTime / totalDuration) * 100;

                document.getElementById('countdown').textContent = Math.floor(remainingTime / 1000) + '秒';
                document.getElementById('countdown-bar').style.width = progress + '%';

                if (remainingTime <= 0) {
                    clearInterval(countdownInterval);
                    endGame();
                }
            }, 1000);
        }

        function endGame() {
            alert('比赛结束');
            document.querySelectorAll('.question-btn').forEach(function (btn) {
                btn.disabled = true;
                btn.classList.add('used-btn');
            });
            document.getElementById('submit-btn').disabled = true;
            document.getElementById('next-btn').disabled = true;
            document.querySelectorAll('.operator-btns button').forEach(function (btn) {
                btn.disabled = true;
            });
            document.querySelector('.action-btns button').disabled = true;
        }

        function showQuestion(index) {
            if (!isGameStarted) {
                alert('比赛还未开始，请等待。');
                return;
            }
            if (isGameEnded) {
                alert('比赛已经结束。');
                return;
            }
            currentQuestionIndex = index;
            var question = questions[index];
            var questionDiv = document.getElementById('question');
            questionDiv.innerHTML = '';
            document.getElementById('input').value = '';
            usedButtons = [];
            inputButtonMap = [];
            document.getElementById('result-message').textContent = '';

            var btn1 = document.createElement('button');
            btn1.innerHTML = question.num1;
            btn1.classList.add('question-btn');
            btn1.onclick = function () {
                if (isConsecutiveNumber(this.innerHTML)) {
                    alert('输入框中不能出现连续的两个数字');
                    return;
                }
                this.classList.add('used-btn');
                this.disabled = true;
                var input = document.getElementById('input');
                var startIndex = input.value.length;
                input.value += this.innerHTML;
                var endIndex = input.value.length;
                usedButtons.push(this);
                inputButtonMap.push({ start: startIndex, end: endIndex, button: this });
            };
            questionDiv.appendChild(btn1);

            var btn2 = document.createElement('button');
            btn2.innerHTML = question.num2;
            btn2.classList.add('question-btn');
            btn2.onclick = function () {
                if (isConsecutiveNumber(this.innerHTML)) {
                    alert('输入框中不能出现连续的两个数字');
                    return;
                }
                this.classList.add('used-btn');
                this.disabled = true;
                var input = document.getElementById('input');
                var startIndex = input.value.length;
                input.value += this.innerHTML;
                var endIndex = input.value.length;
                usedButtons.push(this);
                inputButtonMap.push({ start: startIndex, end: endIndex, button: this });
            };
            questionDiv.appendChild(btn2);

            var btn3 = document.createElement('button');
            btn3.innerHTML = question.num3;
            btn3.classList.add('question-btn');
            btn3.onclick = function () {
                if (isConsecutiveNumber(this.innerHTML)) {
                    alert('输入框中不能出现连续的两个数字');
                    return;
                }
                this.classList.add('used-btn');
                this.disabled = true;
                var input = document.getElementById('input');
                var startIndex = input.value.length;
                input.value += this.innerHTML;
                var endIndex = input.value.length;
                usedButtons.push(this);
                inputButtonMap.push({ start: startIndex, end: endIndex, button: this });
            };
            questionDiv.appendChild(btn3);

            var btn4 = document.createElement('button');
            btn4.innerHTML = question.num4;
            btn4.classList.add('question-btn');
            btn4.onclick = function () {
                if (isConsecutiveNumber(this.innerHTML)) {
                    alert('输入框中不能出现连续的两个数字');
                    return;
                }
                this.classList.add('used-btn');
                this.disabled = true;
                var input = document.getElementById('input');
                var startIndex = input.value.length;
                input.value += this.innerHTML;
                var endIndex = input.value.length;
                usedButtons.push(this);
                inputButtonMap.push({ start: startIndex, end: endIndex, button: this });
            };
            questionDiv.appendChild(btn4);

            document.getElementById('current-question-number').textContent = index + 1;
        }

        function isConsecutiveNumber(num) {
            var input = document.getElementById('input').value;
            if (input.length > 0) {
                var lastChar = input.slice(-1);
                return!isNaN(lastChar) &&!isNaN(num);
            }
            return false;
        }

      function submitAnswer() {
    if (!isGameStarted) {
        alert('比赛还未开始，请等待。');
        return;
    }
    if (isGameEnded) {
        alert('比赛已经结束。');
        return;
    }
    var input = document.getElementById('input').value;
    var usedNumbers = document.querySelectorAll('#question .used-btn').length;
    if (usedNumbers < 4) {
        alert('还没有完成计算');
        return;
    }
    try {
        // 更新正则表达式，支持开方符号 √
        var validChars = /^[0-9+\-*/().√]+$/;
        if (!validChars.test(input)) {
            alert('输入的式子包含非法字符');
            return;
        }
        // 处理 √ 符号，支持 √4 写法
        input = input.replace(/√(\d+)/g, 'Math.sqrt($1)');
        input = input.replace(/√\(([^)]+)\)/g, 'Math.sqrt($1)');
        var result = eval(input);
        if (result == 24) {
            correctCount++;
            document.getElementById('correct-count').innerHTML = correctCount;
            document.querySelectorAll('#question .question-btn').forEach(function (btn) {
                btn.classList.add('used-btn');
                btn.disabled = true;
            });
            document.querySelectorAll('.question-btn')[currentQuestionIndex].classList.add('used-btn');
            document.querySelectorAll('.question-btn')[currentQuestionIndex].disabled = true;
            document.getElementById('result-message').textContent = '恭喜，回答正确';
            document.getElementById('input').value = '';
            usedButtons = [];
            inputButtonMap = [];
            var questionId = questions[currentQuestionIndex].id;
            var user_id = <?php echo $_SESSION['user_id'];?>;
            var xhr = new XMLHttpRequest();
            // 检查路径是否正确
            var url = 'update_answer_status.php'; 
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        console.log(xhr.responseText);
                        if (xhr.responseText.indexOf("success") === -1) {
                            correctCount--;
                            document.getElementById('correct-count').innerHTML = correctCount;
                            alert('更新数据库失败: ' + xhr.responseText);
                        } else {
                            correctQuestionIds.push(questionId);
                            remainingQuestionIndices = questions
                               .map((q, i) => i)
                               .filter(i =>!correctQuestionIds.includes(questions[i].id));
                            document.getElementById('current-question-number').textContent = currentQuestionIndex + 1;
                        }
                    } else {
                        correctCount--;
                        document.getElementById('correct-count').innerHTML = correctCount;
                        alert('AJAX 请求失败: 状态码 ' + xhr.status + ', 响应文本: ' + xhr.responseText);
                    }
                }
            };
            var data = 'user_id=' + user_id + '&question_id=' + questionId + '&is_correct=1';
            xhr.send(data);
        } else {
            alert('回答错误');
        }
    } catch (error) {
        alert('输入的式子不符合数学计算: ' + error.message);
    }
}    

        function nextQuestion() {
            if (!isGameStarted) {
                alert('比赛还未开始，请等待。');
                return;
            }
            if (isGameEnded) {
                alert('比赛已经结束。');
                return;
            }

            remainingQuestionIndices = questions
               .map((q, i) => i)
               .filter(i =>!correctQuestionIds.includes(questions[i].id));

            if (remainingQuestionIndices.length === 0) {
                alert('已经没有题目了');
                return;
            }

            if (currentQuestionIndex!== null) {
                var currentPos = remainingQuestionIndices.indexOf(currentQuestionIndex);
                if (currentPos!== -1) {
                    if (currentPos < remainingQuestionIndices.length - 1) {
                        currentQuestionIndex = remainingQuestionIndices[currentPos + 1];
                    } else {
                        // 如果当前是最后一题，从头开始
                        currentQuestionIndex = remainingQuestionIndices[0];
                    }
                } else {
                    // 当前题目已被回答，找下一个未回答的题目
                    for (var i = currentQuestionIndex + 1; i < questions.length; i++) {
                        if (!correctQuestionIds.includes(questions[i].id)) {
                            currentQuestionIndex = i;
                            break;
                        }
                    }
                    // 如果没找到，从头开始找
                    if (currentQuestionIndex === null) {
                        for (var j = 0; j < questions.length; j++) {
                            if (!correctQuestionIds.includes(questions[j].id)) {
                                currentQuestionIndex = j;
                                break;
                            }
                        }
                    }
                }
            } else {
                currentQuestionIndex = remainingQuestionIndices[0];
            }

            showQuestion(currentQuestionIndex);

            document.getElementById('input').value = '';
            document.querySelectorAll('#question .used-btn').forEach(function (btn) {
                btn.classList.remove('used-btn');
                btn.disabled = false;
            });
            usedButtons = [];
            inputButtonMap = [];
        }

        function backspace() {
            if (!isGameStarted) {
                alert('比赛还未开始，请等待。');
                return;
            }
            if (isGameEnded) {
                alert('比赛已经结束。');
                return;
            }
            var input = document.getElementById('input');
            var inputValue = input.value;
            if (inputValue.length === 0) return;

            var lastChar = inputValue.slice(-1);
            if (!isNaN(lastChar)) {
                var lastNumberEntry = inputButtonMap.pop();
                if (lastNumberEntry) {
                    input.value = inputValue.slice(0, lastNumberEntry.start);
                    lastNumberEntry.button.classList.remove('used-btn');
                    lastNumberEntry.button.disabled = false;
                    var index = usedButtons.indexOf(lastNumberEntry.button);
                    if (index!== -1) {
                        usedButtons.splice(index, 1);
                    }
                }
            } else {
                input.value = inputValue.slice(0, -1);
            }
        }

        function clearInput() {
            if (!isGameStarted) {
                alert('比赛还未开始，请等待。');
                return;
            }
            if (isGameEnded) {
                alert('比赛已经结束。');
                return;
            }
            var input = document.getElementById('input');
            input.value = '';
            usedButtons = [];
            inputButtonMap = [];
            var questionButtons = document.querySelectorAll('#question .question-btn');
            questionButtons.forEach(function (button) {
                button.classList.remove('used-btn');
                button.disabled = false;
            });
        }

        function addOperator(operator) {
            if (!isGameStarted) {
                alert('比赛还未开始，请等待。');
                return;
            }
            if (isGameEnded) {
                alert('比赛已经结束。');
                return;
            }
            document.getElementById('input').value += operator;
        }

        window.onload = function () {
            console.log('questions:', questions);
            console.log('correctQuestionIds:', correctQuestionIds);
            document.getElementById('username').innerHTML = '欢迎，' + '<?php echo $username; ?>';
            document.getElementById('start-time').innerHTML = '比赛开始时间: ' + new Date(startTimestamp).toLocaleString();
            document.getElementById('end-time').innerHTML = '比赛结束时间: ' + new Date(endTimestamp).toLocaleString();
            document.getElementById('correct-count').innerHTML = correctCount;

            remainingQuestionIndices = questions
               .map((q, i) => i)
               .filter(i =>!correctQuestionIds.includes(questions[i].id));

            if (isGameStarted) {
                startCountdown();
            } else {
                document.getElementById('countdown').textContent = '比赛还未开始，请等待';
                document.getElementById('countdown-bar').style.width = '0%';
                var checkStartInterval = setInterval(function () {
                    var now = new Date().getTime();
                    if (now >= startTimestamp) {
                        clearInterval(checkStartInterval);
                        isGameStarted = true;
                        startCountdown();
                    }
                }, 1000);
            }

            var questionList = document.getElementById('question-list');
            questions.forEach(function (question, index) {
                var btn = document.createElement('button');
                btn.innerHTML = index + 1;
                btn.classList.add('question-btn');

                if (correctQuestionIds.includes(question.id)) {
                    btn.classList.add('used-btn');
                    btn.disabled = true;
                    btn.title = '已正确回答';
                } else {
                    btn.onclick = function () {
                        currentQuestionIndex = index;
                        showQuestion(index);
                    };
                }
                questionList.appendChild(btn);
            });

            if (isGameEnded) {
                endGame();
            }

            if (remainingQuestionIndices.length > 0) {
                currentQuestionIndex = remainingQuestionIndices[0];
                document.getElementById('current-question-number').textContent = currentQuestionIndex + 1;
            } else {
                document.getElementById('current-question-number').textContent = '无剩余题目';
            }
        };
    </script>
</head>

<body>
    <div class="top-info">
        <span id="username"></span>
        <span id="start-time"></span>
        <span id="end-time"></span>
        <a href="logout.php" class="action-btn logout-btn">退出登录</a>
    </div>
    <div class="game-name-display">
        <center><strong><p style="font-size: 40px; font-family: Arial, sans-serif;"><?php echo htmlspecialchars($gameName); ?></p></strong></center>
    </div>
    <div class="container">
        <h1>答题页面</h1>
        <div id="countdown-section">
            <div>倒计时：<span id="countdown"><?php echo $remaining_time; ?></span> 秒</div>
            <div id="countdown-bar" style="width: 100%;"></div>
            <div>答题正确数量：<span id="correct-count">0</span></div>
        </div>

        题目序号<div id="question-list" class="question-list"></div>
        题目显示区<div class="question-display" id="question"></div>
        <div class="operator-btns">
            <button onclick="addOperator('+')">+</button>
            <button onclick="addOperator('-')">-</button>
            <button onclick="addOperator('*')">*</button>
            <button onclick="addOperator('/')">÷</button>
            <button onclick="addOperator('(')">(</button>
            <button onclick="addOperator(')')">)</button>
            <button onclick="addOperator('√')">√</button>
        </div>
        <div id="result-message" style="color: green;"></div>
        <div class="input-group">
            <input type="text" id="input" readonly>
            <span>=24</span>
            <button onclick="backspace()">退格</button>
            <button onclick="clearInput()">清空</button>
        </div>
        <div class="action-btns">
            <button id="submit-btn" onclick="submitAnswer()">提交</button>
            <button id="next-btn" onclick="nextQuestion()">下一题&emsp; 当前第（<span id="current-question-number"></span>）题</button>
        </div>
        <br>
        <button onclick="window.open('rank.php', '_blank')">实时排名</button>
    </div>
</body>

</html>    