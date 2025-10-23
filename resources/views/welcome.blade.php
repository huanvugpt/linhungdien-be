<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vương Quốc Linh Ứng Điện</title>
    <meta http-equiv="refresh" content="0; url={{ env('FRONTEND_URL', 'http://localhost:3001') }}">
</head>
<body>
    <div style="text-align: center; margin-top: 50px; font-family: Arial, sans-serif;">
        <h1>Vương Quốc Linh Ứng Điện</h1>
        <p>Đang chuyển hướng đến trang chủ...</p>
        <p><a href="{{ env('FRONTEND_URL', 'http://localhost:3001') }}">Nhấn vào đây nếu trang không tự động chuyển hướng</a></p>
    </div>
    <script>
        window.location.href = "{{ env('FRONTEND_URL', 'http://localhost:3001') }}";
    </script>
</body>
</html>
