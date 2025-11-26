<?php
require 'config.php';

// ======================
// 2. LẤY CÂU HỎI + ĐÁP ÁN
// ======================
$sql = "
    SELECT 
        q.id       AS qid,
        q.content  AS q_content,
        o.opt_key  AS opt_key,
        o.content  AS opt_content,
        o.is_correct
    FROM quiz_questions q
    JOIN quiz_options  o ON o.question_id = q.id
    ORDER BY q.id, o.opt_key
";

$result = $conn->query($sql);
if (!$result) {
    die("Lỗi query: " . $conn->error);
}

$questions = [];
while ($row = $result->fetch_assoc()) {
    $qid = (int)$row['qid'];

    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'id'      => $qid,
            'content' => $row['q_content'],
            'options' => []
        ];
    }

    $questions[$qid]['options'][$row['opt_key']] = [
        'text'       => $row['opt_content'],
        'is_correct' => (int)$row['is_correct']
    ];
}

// Tính đáp án đúng & loại câu (1 đáp án / nhiều đáp án)
foreach ($questions as &$q) {
    $correct = [];
    foreach ($q['options'] as $key => $opt) {
        if ($opt['is_correct']) {
            $correct[] = $key;
        }
    }
    sort($correct);
    $q['correct_keys'] = $correct;
    $q['is_multi']     = count($correct) > 1; // true -> checkbox
}
unset($q);

// ======================
// 3. CHẤM ĐIỂM NẾU NỘP BÀI
// ======================
$score = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;

    foreach ($questions as &$q) {
        $fieldName = 'q' . $q['id'];

        if ($q['is_multi']) {
            // Câu nhiều đáp án -> checkbox
            $userAns = isset($_POST[$fieldName]) ? (array)$_POST[$fieldName] : [];
            $userAns = array_map('strtoupper', $userAns);
            sort($userAns);
            $q['user'] = $userAns;

            if ($userAns === $q['correct_keys']) {
                $score++;
            }
        } else {
            // Câu 1 đáp án -> radio
            $userAns = isset($_POST[$fieldName]) ? strtoupper($_POST[$fieldName]) : null;
            $q['user'] = $userAns;

            $correct = $q['correct_keys'][0] ?? null;
            if ($userAns !== null && $userAns === $correct) {
                $score++;
            }
        }
    }
    unset($q);
}

$totalQuestions = count($questions);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài thi trắc nghiệm Android</title>
    <link rel="stylesheet" href="quiz.css">
</head>
<body>

<h1>Bài thi trắc nghiệm Android</h1>
<p>Câu hỏi &amp; đáp án được lấy từ MySQL (qizz_db / quiz_questions / quiz_options).</p>

<?php if ($score !== null): ?>
    <div class="result">
        Bạn làm đúng <strong><?php echo $score; ?></strong> /
        <strong><?php echo $totalQuestions; ?></strong> câu.
    </div>
<?php endif; ?>

<form method="post">
    <?php
    $stt = 1;
    foreach ($questions as $q):
        ksort($q['options']); // đảm bảo A, B, C, D theo thứ tự
        $fieldName = 'q' . $q['id'];
    ?>
        <div class="question">
            <div class="q-text">
                Câu <?php echo $stt++; ?>:
                <?php echo htmlspecialchars($q['content']); ?>
                <?php if ($q['is_multi']): ?>
                    <span class="multi-note">(Chọn nhiều đáp án)</span>
                <?php endif; ?>
            </div>

            <?php foreach ($q['options'] as $key => $opt): ?>
                <div class="option">
                    <label>
                        <?php if ($q['is_multi']): ?>
                            <input type="checkbox"
                                   name="<?php echo $fieldName; ?>[]"
                                   value="<?php echo $key; ?>"
                                   <?php
                                   if (isset($q['user']) && is_array($q['user']) && in_array($key, $q['user'])) {
                                       echo 'checked';
                                   }
                                   ?>>
                        <?php else: ?>
                            <input type="radio"
                                   name="<?php echo $fieldName; ?>"
                                   value="<?php echo $key; ?>"
                                   <?php
                                   if (isset($q['user']) && $q['user'] === $key) {
                                       echo 'checked';
                                   }
                                   ?>>
                        <?php endif; ?>

                        <?php echo $key . '. ' . htmlspecialchars($opt['text']); ?>
                    </label>
                </div>
            <?php endforeach; ?>

            <?php if ($score !== null): ?>
                <div class="answer-info">
                    Đáp án đúng:
                    <span class="correct">
                        <?php echo implode(', ', $q['correct_keys']); ?>
                    </span>

                    <?php if (isset($q['user'])): ?>
                        &nbsp;| Bạn chọn:
                        <?php if ($q['is_multi']): ?>
                            <?php
                            $userShow = $q['user'];
                            if (empty($userShow)) {
                                echo '<span class="wrong">Không chọn</span>';
                            } else {
                                echo implode(', ', $userShow);
                                echo ($userShow === $q['correct_keys'])
                                    ? ' <span class="correct">✓</span>'
                                    : ' <span class="wrong">✗</span>';
                            }
                            ?>
                        <?php else: ?>
                            <?php
                            if ($q['user'] === null) {
                                echo '<span class="wrong">Không chọn</span>';
                            } elseif ($q['user'] === $q['correct_keys'][0]) {
                                echo '<span class="correct">' . $q['user'] . ' ✓</span>';
                            } else {
                                echo '<span class="wrong">' . $q['user'] . ' ✗</span>';
                            }
                            ?>
                        <?php endif; ?>
                    <?php else: ?>
                        &nbsp;| Bạn chưa chọn.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <button type="submit">Nộp bài</button>
</form>

</body>
</html>
