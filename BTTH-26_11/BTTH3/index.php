<?php
require_once "config.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Upload danh sách sinh viên CSV</title>
</head>
<body>
    <h1>Upload file CSV tài khoản sinh viên (TLU)</h1>

    <form action="upload_dbs.php" method="post" enctype="multipart/form-data">
        <label>Chọn file CSV:</label><br>
        <input type="file" name="csv_file" accept=".csv" required>
        <br><br>
        <button type="submit" name="submit">Upload & Import vào MySQL</button>
    </form>
</body>
</html>
