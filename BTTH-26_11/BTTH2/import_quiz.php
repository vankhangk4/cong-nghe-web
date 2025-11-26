<?php
$host = 'localhost';
$user = 'root';
$pass = 'Dk@17092004';
$db   = 'qizz_db'; 

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function loadQuestionsFromFile($filename)
{
    if (!file_exists($filename)) {
        die("Không tìm thấy file: " . htmlspecialchars($filename));
    }

    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    $questions = [];
    $current = null;

    foreach ($lines as $rawLine) {
        $line = trim($rawLine);
        if ($line === '') continue;

        if (stripos($line, 'ANSWER:') === 0) {
            $answerStr = trim(substr($line, strlen('ANSWER:'))); // ví dụ "C" hoặc "C, D"
            $answers = array_map('trim', explode(',', $answerStr));
            $answers = array_map('strtoupper', $answers);         // -> ['C'] hoặc ['C','D']

            if ($current !== null) {
                $current['correct'] = $answers;
                $questions[] = $current;
                $current = null;
            }
        }
        elseif (preg_match('/^[A-D]\./u', $line)) {
            $optKey  = strtoupper($line[0]);       // A/B/C/D
            $optText = trim(substr($line, 2));     // phần sau "A."
            if ($current === null) {
                $current = ['question' => '', 'options' => [], 'correct' => []];
            }
            $current['options'][$optKey] = $optText;
        }
        else {
            if ($current === null) {
                $current = [
                    'question' => $line,
                    'options'  => [],
                    'correct'  => []
                ];
            } else {
                $current['question'] .= ' ' . $line;
            }
        }
    }

    return $questions;
}

$questions = loadQuestionsFromFile(__DIR__ . '/Quiz.txt');


$conn->query("DELETE FROM quiz_options");
$conn->query("DELETE FROM quiz_questions");

$stmtQ = $conn->prepare("INSERT INTO quiz_questions (content) VALUES (?)");
$stmtO = $conn->prepare("INSERT INTO quiz_options (question_id, opt_key, content, is_correct) VALUES (?, ?, ?, ?)");

if (!$stmtQ || !$stmtO) {
    die("Lỗi prepare: " . $conn->error);
}

foreach ($questions as $q) {
    $content = $q['question'];

    $stmtQ->bind_param("s", $content);
    $stmtQ->execute();
    $questionId = $conn->insert_id;

    foreach ($q['options'] as $key => $text) {
        $isCorrect = in_array($key, $q['correct']) ? 1 : 0;
        $stmtO->bind_param("issi", $questionId, $key, $text, $isCorrect);
        $stmtO->execute();
    }
}

echo "Import thành công " . count($questions) . " câu hỏi!";

$stmtQ->close();
$stmtO->close();
$conn->close();
