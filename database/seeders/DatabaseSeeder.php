<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // 'email',
        // 'password',
        // 'full_name',
        // 'mssv',
        // 'phone_number',
        // 'role',
        // 'status',
        // 'status_reason',
        // 'faculty',
        // 'class_name',
        // 'academic_year',
        User::factory()->create([
            'full_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'password' => Hash::make('12345678'),
        ]);
    }
}
