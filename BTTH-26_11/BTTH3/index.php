<?php
require_once "config.php";

$message = '';
$messageClass = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    switch ($msg) {
        case 'nofile':
            $message = "Vui lòng chọn một file CSV trước khi upload.";
            $messageClass = 'warning';
            break;
        case 'invalid_method':
            $message = "Hành động không hợp lệ. Vui lòng upload file qua form.";
            $messageClass = 'warning';
            break;
        case 'invalid_ext':
            $message = "Chỉ chấp nhận file .csv.";
            $messageClass = 'warning';
            break;
        case 'upload_error':
            $code = isset($_GET['code']) ? (int)$_GET['code'] : 0;
            $message = "Có lỗi xảy ra khi upload file (mã: {$code}). Hãy thử lại.";
            $messageClass = 'error';
            break;
        case 'import_error':
            $message = "Có lỗi trong quá trình import. Dữ liệu chưa được ghi.";
            $messageClass = 'error';
            break;
    }
}

$filesStmt = $pdo->query("
    SELECT id, filename, uploaded_at, total_rows
    FROM uploaded_files
    ORDER BY uploaded_at DESC
");
$files = $filesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Upload danh sách sinh viên</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f5f7fb; }
        h1 { margin-top: 0; }
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        form label { display: block; margin-bottom: 6px; font-weight: bold; }
        form input[type="file"] { margin-bottom: 10px; }
        button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #eef2ff; }
        .message { padding: 10px 12px; border-radius: 6px; margin-bottom: 14px; }
        .message.warning { background: #fff7ed; border: 1px solid #fdba74; }
        .message.error { background: #fef2f2; border: 1px solid #fca5a5; }
        .help { color: #6b7280; font-size: 13px; margin-top: 4px; }
        .empty { color: #6b7280; }
    </style>
</head>
<body>

<h1>📤 Upload danh sách sinh viên (CSV)</h1>

<?php if ($message): ?>
    <div class="message <?= htmlspecialchars($messageClass) ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="card">
    <form action="upload_dbs.php" method="post" enctype="multipart/form-data">
        <label for="csv_file">Chọn file CSV</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv" required>
        <div class="help">Định dạng cột: username, password, lastname, firstname, city, email, course1</div>
        <button type="submit">Upload và import</button>
    </form>
</div>

<div class="card">
    <h2>📁 Các lần upload trước</h2>
    <?php if (empty($files)): ?>
        <p class="empty">Chưa có file nào được import.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên file</th>
                <th>Số dòng import</th>
                <th>Thời gian upload</th>
                <th>Xem chi tiết</th>
            </tr>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td><?= (int)$file['id'] ?></td>
                    <td><?= htmlspecialchars($file['filename']) ?></td>
                    <td><?= (int)$file['total_rows'] ?></td>
                    <td><?= htmlspecialchars($file['uploaded_at']) ?></td>
                    <td><a href="view_students.php?file_id=<?= (int)$file['id'] ?>">Xem</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
