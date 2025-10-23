# Linh Ứng Điện Backend

Laravel backend API cho dự án Linh Ứng Điện.

## Cài Đặt Nhanh

```bash
# 1. Install dependencies
composer install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Create database
mysql -e "CREATE DATABASE linhungdien"

# 4. Configure .env với database credentials

# 5. Run migrations
php artisan migrate

# 6. Start server
php artisan serve --port=8000
```

## Chi Tiết

Xem file [INSTALL.md](INSTALL.md) để có hướng dẫn đầy đủ.

## Tính Năng

- ✅ Authentication (Sanctum + Social Login)
- ✅ Posts Management (Bài viết)
- ✅ Categories (Danh mục)
- ✅ Albums & Images (Album ảnh)
- ✅ Videos
- ✅ Notifications (Thông báo)
- ✅ User Submissions (Đóng góp)
- ✅ Admin Panel (AdminLTE)
- ✅ Broadcasting (Pusher)

## Tech Stack

- Laravel 12.x
- MySQL/MariaDB
- Redis (optional)
- Pusher (Broadcasting)
