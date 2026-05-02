<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Report;
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
        // User::factory()->create([
        //     'full_name' => 'Super Admin',
        //     'email' => 'superadmin@ms.uit.edu.vn',
        //     'role' => User::ROLE_SUPER_ADMIN,
        //     'status' => User::STATUS_ACTIVE,
        //     'password' => Hash::make('`12345678`'),
        // ]);

        // User::factory()->create([
        //     'full_name' => 'Admin',
        //     'email' => 'admin@ms.uit.edu.vn',
        //     'role' => User::ROLE_ADMIN,
        //     'status' => User::STATUS_ACTIVE,
        //     'password' => Hash::make('12345678'),
        // ]);

        // User::factory()->create([
        //     'full_name' => 'Student',
        //     'email' => 'student@ms.uit.edu.vn',
        //     'role' => User::ROLE_STUDENT,
        //     'status' => User::STATUS_ACTIVE,
        //     'password' => Hash::make('12345678'),
        // ]);

        // // Category 1: Học tập
        // Category::factory()->create([

        //     'category_name' => 'Học tập',
        //     'description' => 'Review môn học, đánh giá giảng viên, chia sẻ tài liệu học tập, đề thi và kinh nghiệm học tập tại UIT',
        // ]);

        // // Category 2: Hành chính
        // Category::factory()->create([
        //     'category_name' => 'Hành chính',
        //     'description' => 'Thông tin về đăng ký môn học, thủ tục học phí, học bổng, giấy tờ hành chính và các thông báo học vụ',
        // ]);

        // // Category 3: Hướng nghiệp
        // Category::factory()->create([
        //     'category_name' => 'Hướng nghiệp',
        //     'description' => 'Cơ hội việc làm, thực tập, workshop, định hướng nghề nghiệp và chia sẻ kinh nghiệm phỏng vấn',
        // ]);

        // // Category 4: Đời sống
        // Category::factory()->create([
        //     'category_name' => 'Đời sống',
        //     'description' => 'Đời sống sinh viên: canteen, ký túc xá, câu lạc bộ, hoạt động ngoại khóa và các cảnh báo học vụ',
        // ]);

        // Report nhắm vào Post
        Report::factory()->count(10)->create();

        // Report nhắm vào User
        Report::factory()->forUser()->count(5)->create();
    }
}
