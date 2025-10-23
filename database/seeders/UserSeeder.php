<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        User::create([
            'name' => 'Nguyễn Văn A',
            'email' => 'user1@vuongquoclinhungdien.test',
            'password' => Hash::make('user123456'),
            'phone' => '0901234567',
            'address' => 'Hà Nội',
            'status' => 'approved',
            'approved_at' => now(),
            'gender' => 'male',
        ]);

        User::create([
            'name' => 'Trần Thị B',
            'email' => 'user2@vuongquoclinhungdien.test',
            'password' => Hash::make('user123456'),
            'phone' => '0907654321',
            'address' => 'Hồ Chí Minh',
            'status' => 'approved',
            'approved_at' => now(),
            'gender' => 'female',
        ]);

        User::create([
            'name' => 'Phạm Văn C',
            'email' => 'user3@vuongquoclinhungdien.test',
            'password' => Hash::make('user123456'),
            'phone' => '0909876543',
            'address' => 'Đà Nẵng',
            'status' => 'approved',
            'approved_at' => now(),
            'gender' => 'male',
        ]);
    }
}
