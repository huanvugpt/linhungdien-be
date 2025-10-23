# Hướng Dẫn Cài Đặt Linh Ứng Điện Backend

Project Laravel sạch, chỉ chứa code tính năng, không có Docker hay script deploy.

## Yêu Cầu Hệ Thống

-   PHP 8.3+
-   MySQL 8.0+ hoặc MariaDB 10.6+
-   Composer
-   Redis (optional, cho cache)
-   Node.js & NPM (nếu có frontend assets)

## Bước 1: Cài Đặt Dependencies

```bash
cd /var/www/html/linhungdien
composer install
```

## Bước 2: Cấu Hình Environment

Copy và chỉnh sửa file `.env`:

```bash
cp .env.example .env
```

Cấu hình database trong `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=linhungdien
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Bước 3: Tạo Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE linhungdien CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

## Bước 4: Generate Application Key

```bash
php artisan key:generate
```

## Bước 5: Chạy Migrations

```bash
php artisan migrate
```

## Bước 6: Seed Database (Optional)

```bash
php artisan db:seed
```

## Bước 7: Tạo Storage Link

```bash
php artisan storage:link
```

## Bước 8: Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Chạy Development Server

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Hoặc với PM2:

```bash
pm2 start --name linhungdien -- php artisan serve --host=0.0.0.0 --port=8000
```

## Chạy Queue Worker

```bash
php artisan queue:work
```

Hoặc với PM2:

```bash
pm2 start --name linhungdien-queue -- php artisan queue:work
```

## Cache Management

Clear cache:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Optimize cho production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Các Tính Năng Chính

### Models

-   User (Authentication)
-   Admin (Admin panel)
-   Category (Danh mục)
-   Post (Bài viết)
-   PostView (Lượt xem)
-   PostLike (Lượt thích)
-   PostSubmission (Đóng góp bài viết)
-   Album (Album ảnh)
-   AlbumImage (Hình ảnh album)
-   Video (Video)
-   Notification (Thông báo)
-   Contact (Liên hệ)

### API Routes

-   `/api/auth/*` - Authentication (login, register, social login)
-   `/api/categories` - Danh mục
-   `/api/posts` - Bài viết
-   `/api/profile` - Thông tin người dùng
-   `/api/submissions` - Đóng góp bài viết
-   `/api/notifications` - Thông báo
-   `/api/health` - Health check

### Admin Panel

AdminLTE đã được tích hợp sẵn. Truy cập tại `/admin`

### Broadcasting

Pusher đã được cấu hình. Cần thêm credentials vào `.env`:

```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
```

## Troubleshooting

### Lỗi Permission

```bash
sudo chown -R $USER:$USER storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Lỗi Database Connection

Kiểm tra credentials trong `.env` và đảm bảo MySQL đang chạy:

```bash
sudo systemctl status mysql
```

### Clear All Cache

```bash
php artisan optimize:clear
```

## Production Deployment

1. Set `APP_DEBUG=false` trong `.env`
2. Set `APP_ENV=production`
3. Chạy `composer install --no-dev --optimize-autoloader`
4. Chạy `php artisan optimize`
5. Setup queue worker với Supervisor
6. Setup cron job cho scheduler:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Cấu Trúc Thư Mục

```
linhungdien/
├── app/
│   ├── Http/Controllers/  # Controllers
│   └── Models/            # Eloquent Models
├── config/                # Configuration files
├── database/
│   ├── migrations/        # Database migrations
│   ├── seeders/          # Database seeders
│   └── factories/        # Model factories
├── routes/
│   ├── api.php           # API routes
│   ├── web.php           # Web routes
│   └── admin.php         # Admin routes
└── resources/
    └── views/            # Blade templates
```

## Support

Đây là project Laravel sạch, chỉ chứa code tính năng business logic.
Không có Docker, không có script tự động deploy.
Cài đặt thủ công theo từng bước ở trên.
