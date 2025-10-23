<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $categories = [
            [
                'name' => 'Tin Tức Phật Giáo',
                'slug' => 'tin-tuc-phat-giao',
                'description' => 'Tin tức và sự kiện Phật giáo trong và ngoài nước',
                'color' => '#ff6b35',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Câu Chuyện Phật Giáo',
                'slug' => 'cau-chuyen-phat-giao',
                'description' => 'Những câu chuyện, truyện kể và kinh nghiệm tu tập',
                'color' => '#4ecdc4',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Phát Tâm Thiện Nguyện',
                'slug' => 'phat-tam-thien-nguyen',
                'description' => 'Các hoạt động từ thiện, thiện nguyện và phát tâm bồ đề',
                'color' => '#45b7d1',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Chân Dung Từ Bi',
                'slug' => 'chan-dung-tu-bi',
                'description' => 'Những tấm gương sống đẹp, những câu chuyện cảm động về lòng từ bi',
                'color' => '#f7b733',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Chương Trình Sắp Tới',
                'slug' => 'chuong-trinh-sap-toi',
                'description' => 'Thông tin về các chương trình, sự kiện Phật giáo sắp diễn ra',
                'color' => '#5c7cfa',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Thư viện Ảnh',
                'slug' => 'thu-vien-anh',
                'description' => 'Hình ảnh về các hoạt động Phật giáo, chùa chiền, tượng Phật',
                'color' => '#20bf6b',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Thư viện Video',
                'slug' => 'thu-vien-video',
                'description' => 'Video giảng pháp, hướng dẫn tu tập và các hoạt động Phật giáo',
                'color' => '#e74c3c',
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']], // Find by slug
                $category // Update with all category data
            );
        }
    }
}
