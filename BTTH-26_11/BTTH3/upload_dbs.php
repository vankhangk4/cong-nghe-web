<?php
require_once "config.php";

$redirect = function (string $msg, ?int $code = null): void {
    $url = "index.php?msg=" . urlencode($msg);
    if ($code !== null) {
        $url .= "&code=" . urlencode((string)$code);
    }
    header("Location: {$url}");
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $redirect('invalid_method');
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] === UPLOAD_ERR_NO_FILE) {
    $redirect('nofile');
}

if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    $redirect('upload_error', (int)$_FILES['csv_file']['error']);
}

$fileTmpPath = $_FILES['csv_file']['tmp_name'];
$fileName    = $_FILES['csv_file']['name'];

$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    $redirect('invalid_ext');
}

$fileHash = hash_file('sha256', $fileTmpPath);

$checkStmt = $pdo->prepare("SELECT * FROM uploaded_files WHERE file_hash = :hash");
$checkStmt->execute([':hash' => $fileHash]);
$existingFile = $checkStmt->fetch();

if ($existingFile) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>File đã tồn tại</title>
    </head>
    <body>
        <h1>⚠ File này đã được import trước đó!</h1>
        <p>
            <strong>Tên file:</strong> <?= htmlspecialchars($existingFile['filename']) ?><br>
            <strong>Số dòng đã import:</strong> <?= (int)$existingFile['total_rows'] ?><br>
            <strong>Thời gian upload:</strong> <?= htmlspecialchars($existingFile['uploaded_at']) ?>
        </p>
        <a href="index.php">⬅ Quay lại trang upload</a>
    </body>
    </html>
    <?php
    exit;
}

$uploadedFileId = null;

try {
    $pdo->beginTransaction();

    $initFileStmt = $pdo->prepare("
        INSERT INTO uploaded_files (filename, file_hash, uploaded_at, total_rows)
        VALUES (:filename, :file_hash, NOW(), 0)
    ");
    $initFileStmt->execute([
        ':filename'  => $fileName,
        ':file_hash' => $fileHash
    ]);

    $uploadedFileId = $pdo->lastInsertId();

    $inserted = 0;
    $line     = 0;

    if (($handle = fopen($fileTmpPath, "r")) === false) {
        throw new RuntimeException("Không thể đọc file CSV.");
    }

    $sql = "INSERT INTO students 
            (username, password, lastname, firstname, city, email, course1, uploaded_file_id)
            VALUES (:username, :password, :lastname, :firstname, :city, :email, :course1, :uploaded_file_id)";
    $stmt = $pdo->prepare($sql);

    while (($data = fgetcsv($handle, 10000, ",")) !== false) {
        $line++;

        if ($line === 1) {
            $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
            continue;
        }

        if (count($data) < 7) {
            continue;
        }

        $stmt->execute([
            ':username'         => trim($data[0]),
            ':password'         => trim($data[1]),
            ':lastname'         => trim($data[2]),
            ':firstname'        => trim($data[3]),
            ':city'             => trim($data[4]),
            ':email'            => trim($data[5]),
            ':course1'          => trim($data[6]),
            ':uploaded_file_id' => $uploadedFileId,
        ]);

        $inserted++;
    }

    fclose($handle);

    $updateFileStmt = $pdo->prepare("
        UPDATE uploaded_files 
        SET total_rows = :total_rows
        WHERE id = :id
    ");
    $updateFileStmt->execute([
        ':total_rows' => $inserted,
        ':id'         => $uploadedFileId
    ]);

    $pdo->commit();
} catch (Throwable $e) {
    if (isset($handle) && is_resource($handle)) {
        fclose($handle);
    }
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($uploadedFileId !== null) {
        $cleanup = $pdo->prepare("DELETE FROM uploaded_files WHERE id = :id");
        $cleanup->execute([':id' => $uploadedFileId]);
    }
    $redirect('import_error');
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả Import</title>
</head>
<body>

<h1>✔ Đã import <?= $inserted ?> sinh viên từ file: <?= htmlspecialchars($fileName) ?></h1>

<a href="index.php">⬅ Quay lại trang upload</a>

</body>
</html>
