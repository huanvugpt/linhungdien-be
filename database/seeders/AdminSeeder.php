<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin account
        Admin::updateOrCreate(
            ['email' => 'admin@vuongquoclinhungdien.test'],
            [
                'name' => 'Admin Vương Quốc Linh Ứng Điện',
                'email' => 'admin@vuongquoclinhungdien.test',
                'password' => Hash::make('admin123456'),
                'email_verified_at' => now(),
            ]
        );

        // Create super admin account
        Admin::updateOrCreate(
            ['email' => 'superadmin@vuongquoclinhungdien.test'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@vuongquoclinhungdien.test', 
                'password' => Hash::make('superadmin123456'),
                'email_verified_at' => now(),
            ]
        );

        // Create developer account for testing
        Admin::updateOrCreate(
            ['email' => 'dev@vuongquoclinhungdien.test'],
            [
                'name' => 'Developer Account',
                'email' => 'dev@vuongquoclinhungdien.test',
                'password' => Hash::make('dev123456'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin accounts created successfully:');
        $this->command->info('- admin@vuongquoclinhungdien.test / admin123456');
        $this->command->info('- superadmin@vuongquoclinhungdien.test / superadmin123456');
        $this->command->info('- dev@vuongquoclinhungdien.test / dev123456');
    }
}