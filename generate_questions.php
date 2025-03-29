<?php
// 引入数据库配置文件
require_once 'config.php';

// 生成随机题目的函数
function generateRandomQuestion($difficulty, $range) {
    // 确定数字范围
    $min = 1;
    $max = ($range === '1-10') ? 10 : 13;

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

// 根据难度查找题目的解法
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

// 查找简单难度题目的解法
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

// 查找中等难度题目的解法
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

// 查找困难难度题目的解法
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

// 获取数组的全排列
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

// 检查是否为 POST 请求
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 从 POST 数据中获取参数
    $difficulty = $_POST['difficulty'] ?? null;
    $range = $_POST['range'] ?? null;
    $quantity = $_POST['quantity'] ?? null;

    // 验证参数是否有效
    if (!$difficulty || !in_array($difficulty, ['easy', 'medium', 'hard']) ||
        !$range || !in_array($range, ['1-10', '1-13']) ||
        !$quantity || !is_numeric($quantity) || $quantity <= 0
    ) {
        http_response_code(400);
        echo "无效的请求参数，请检查难度、范围和数量。";
        exit;
    }

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
        foreach ($questions_to_display as $question) {
            $num1 = $question['num1'];
            $num2 = $question['num2'];
            $num3 = $question['num3'];
            $num4 = $question['num4'];
            $solution = $question['solution'];
            echo "<input type='checkbox' name='selected_questions[]' value='{$num1},{$num2},{$num3},{$num4},{$solution}'> {$num1}, {$num2}, {$num3}, {$num4} 解法：{$solution}<br>";
        }
    } else {
        http_response_code(404);
        echo "未找到符合条件的题目，请尝试调整难度或范围。";
    }
} else {
    http_response_code(405);
    echo "无效的请求方式，请使用 POST 请求。";
}
    