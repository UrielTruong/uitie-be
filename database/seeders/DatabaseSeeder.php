<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\GroupChat;
use App\Models\GroupMember;
use App\Models\Like;
use App\Models\Message;
use App\Models\Post;
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
        // ============================================================
        // USERS
        // ============================================================

        $superAdmin = User::factory()->create([
            'email'         => 'superadmin@ms.uit.edu.vn',
            'password'      => Hash::make('12345678'),
            'full_name'     => 'Super Admin UIT',
            'mssv'          => null,
            'phone_number'  => null,
            'role'          => User::ROLE_SUPER_ADMIN,
            'status'        => User::STATUS_ACTIVE,
            'faculty'       => null,
            'class_name'    => null,
            'academic_year' => null,
        ]);

        $admin1 = User::factory()->create([
            'email'         => 'admin1@ms.uit.edu.vn',
            'password'      => Hash::make('12345678'),
            'full_name'     => 'Admin One',
            'mssv'          => null,
            'phone_number'  => null,
            'role'          => User::ROLE_ADMIN,
            'status'        => User::STATUS_ACTIVE,
            'faculty'       => null,
            'class_name'    => null,
            'academic_year' => null,
        ]);

        $admin2 = User::factory()->create([
            'email'         => 'admin2@ms.uit.edu.vn',
            'password'      => Hash::make('12345678'),
            'full_name'     => 'Admin Two',
            'mssv'          => null,
            'phone_number'  => null,
            'role'          => User::ROLE_ADMIN,
            'status'        => User::STATUS_ACTIVE,
            'faculty'       => null,
            'class_name'    => null,
            'academic_year' => null,
        ]);

        $students = [];

        $studentsData = [
            ['email' => 'student01@ms.uit.edu.vn', 'full_name' => 'Nguyễn Văn A', 'mssv' => '22520001', 'phone' => '0900000001', 'faculty' => 'CNPM', 'class' => 'SE1', 'year' => '2022-2026', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student02@ms.uit.edu.vn', 'full_name' => 'Trần Thị B',   'mssv' => '22520002', 'phone' => '0900000002', 'faculty' => 'CNPM', 'class' => 'SE1', 'year' => '2022-2026', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student03@ms.uit.edu.vn', 'full_name' => 'Lê Văn C',     'mssv' => '22520003', 'phone' => '0900000003', 'faculty' => 'KHMT', 'class' => 'CS1', 'year' => '2022-2026', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student04@ms.uit.edu.vn', 'full_name' => 'Phạm Thị D',   'mssv' => '22520004', 'phone' => '0900000004', 'faculty' => 'HTTT', 'class' => 'IS1', 'year' => '2021-2025', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student05@ms.uit.edu.vn', 'full_name' => 'Hoàng Văn E',  'mssv' => '22520005', 'phone' => '0900000005', 'faculty' => 'CNPM', 'class' => 'SE2', 'year' => '2021-2025', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student06@ms.uit.edu.vn', 'full_name' => 'Võ Thị F',     'mssv' => '22520006', 'phone' => '0900000006', 'faculty' => 'KHMT', 'class' => 'CS2', 'year' => '2022-2026', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student07@ms.uit.edu.vn', 'full_name' => 'Đặng Văn G',   'mssv' => '22520007', 'phone' => '0900000007', 'faculty' => 'HTTT', 'class' => 'IS2', 'year' => '2020-2024', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student08@ms.uit.edu.vn', 'full_name' => 'Bùi Thị H',    'mssv' => '22520008', 'phone' => '0900000008', 'faculty' => 'CNPM', 'class' => 'SE3', 'year' => '2023-2027', 'status' => User::STATUS_ACTIVE],
            ['email' => 'student09@ms.uit.edu.vn', 'full_name' => 'Nguyễn Văn I', 'mssv' => '22520009', 'phone' => '0900000009', 'faculty' => 'KHMT', 'class' => 'CS3', 'year' => '2023-2027', 'status' => User::STATUS_INACTIVE],
            ['email' => 'student10@ms.uit.edu.vn', 'full_name' => 'Trần Thị K',   'mssv' => '22520010', 'phone' => '0900000010', 'faculty' => 'HTTT', 'class' => 'IS3', 'year' => '2023-2027', 'status' => User::STATUS_LOCKED],
        ];

        foreach ($studentsData as $data) {
            $students[] = User::factory()->create([
                'email'         => $data['email'],
                'password'      => Hash::make('12345678'),
                'full_name'     => $data['full_name'],
                'mssv'          => $data['mssv'],
                'phone_number'  => $data['phone'],
                'role'          => User::ROLE_STUDENT,
                'status'        => $data['status'],
                'status_reason' => match ($data['status']) {
                    User::STATUS_INACTIVE => 'Tài khoản chưa kích hoạt',
                    User::STATUS_LOCKED   => 'Vi phạm quy định cộng đồng',
                    default               => null,
                },
                'faculty'       => $data['faculty'],
                'class_name'    => $data['class'],
                'academic_year' => $data['year'],
            ]);
        }

        // Aliases matching original SQL INSERT order (user IDs 1–13):
        // $superAdmin = users[0] (id=1), $admin1 (id=2), $admin2 (id=3)
        // $students[0..9] = student01..student10 (id=4..13)
        [$stu1, $stu2, $stu3, $stu4, $stu5, $stu6, $stu7, $stu8, $stu9, $stu10] = $students;

        // ============================================================
        // CATEGORIES  (id: 1=Học tập, 2=Hành chính, 3=Hướng nghiệp, 4=Đời sống)
        // ============================================================

        $catHocTap = Category::factory()->create([
            'category_name' => 'Học tập',
            'description'   => 'Review môn học, đánh giá giảng viên, chia sẻ tài liệu học tập, đề thi và kinh nghiệm học tập tại UIT',
        ]);

        $catHanhChinh = Category::factory()->create([
            'category_name' => 'Hành chính',
            'description'   => 'Thông tin về đăng ký môn học, thủ tục học phí, học bổng, giấy tờ hành chính và các thông báo học vụ',
        ]);

        $catHuongNghiep = Category::factory()->create([
            'category_name' => 'Hướng nghiệp',
            'description'   => 'Cơ hội việc làm, thực tập, workshop, định hướng nghề nghiệp và chia sẻ kinh nghiệm phỏng vấn',
        ]);

        $catDoiSong = Category::factory()->create([
            'category_name' => 'Đời sống',
            'description'   => 'Đời sống sinh viên: canteen, ký túc xá, câu lạc bộ, hoạt động ngoại khóa và các cảnh báo học vụ',
        ]);

        // ============================================================
        // POSTS
        // ============================================================

        // Học tập
        $post1 = Post::factory()->create([
            'user_id'     => $stu1->id,
            'category_id' => $catHocTap->id,
            'content'     => 'Review môn Cơ sở dữ liệu: môn khá nặng nhưng rất hữu ích cho backend.',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_ACCEPTED,
        ]);

        $post2 = Post::factory()->create([
            'user_id'     => $stu2->id,
            'category_id' => $catHocTap->id,
            'content'     => 'Chia sẻ tài liệu ôn tập cuối kỳ môn Lập trình Web.',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_ACCEPTED,
        ]);

        $post3 = Post::factory()->create([
            'user_id'     => $stu3->id,
            'category_id' => $catHocTap->id,
            'content'     => 'Kinh nghiệm học tốt môn Cấu trúc dữ liệu và giải thuật.',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_PENDING,
        ]);

        // Hành chính
        $post4 = Post::factory()->create([
            'user_id'     => $stu1->id,
            'category_id' => $catHanhChinh->id,
            'content'     => 'Hỏi về thủ tục đăng ký môn học học kỳ tiếp theo.',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_ACCEPTED,
        ]);

        $post5 = Post::factory()->create([
            'user_id'     => $stu2->id,
            'category_id' => $catHanhChinh->id,
            'content'     => 'Thông tin mới về học phí và thời hạn đóng học kỳ này.',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_ACCEPTED,
        ]);

        // Hướng nghiệp
        $post6 = Post::factory()->create([
            'user_id'     => $stu3->id,
            'category_id' => $catHuongNghiep->id,
            'content'     => 'Chia sẻ cơ hội thực tập Backend cho sinh viên năm 3.',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_ACCEPTED,
        ]);

        $post7 = Post::factory()->create([
            'user_id'     => $stu1->id,
            'category_id' => $catHuongNghiep->id,
            'content'     => 'Mọi người review giúp workshop về AI tuần sau có đáng đi không?',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_PENDING,
        ]);

        // Đời sống
        $post8 = Post::factory()->create([
            'user_id'     => $stu2->id,
            'category_id' => $catDoiSong->id,
            'content'     => 'Canteen trường dạo này có món nào ngon không mọi người?',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_ACCEPTED,
        ]);

        $post9 = Post::factory()->create([
            'user_id'     => $stu3->id,
            'category_id' => $catDoiSong->id,
            'content'     => 'Ký túc xá khu A hiện tại còn chỗ trống không?',
            'visibility'  => Post::VISIBILITY_PUBLIC,
            'status'      => Post::STATUS_ACCEPTED,
        ]);

        // Private post
        $post10 = Post::factory()->create([
            'user_id'     => $stu1->id,
            'category_id' => $catHocTap->id,
            'content'     => 'Ghi chú cá nhân về kế hoạch học tập trong kỳ.',
            'visibility'  => Post::VISIBILITY_PRIVATE,
            'status'      => Post::STATUS_ACCEPTED,
        ]);



        // ============================================================
        // REPORTS
        // ============================================================

        $reportsData = [
            // reporter_id, reported_user_id, reported_post_id, target_type, reason
            [$stu1->id, $stu2->id,   null,       'User', 'Người dùng có hành vi spam comment.'],
            [$stu2->id, null,        $post1->id, 'Post', 'Nội dung bài viết không phù hợp.'],
            [$stu3->id, null,        $post2->id, 'Post', 'Bài viết có thông tin sai sự thật.'],
            [$stu4->id, $stu3->id,   null,       'User', 'Tài khoản này nhắn tin làm phiền.'],
            [$stu5->id, null,        $post4->id, 'Post', 'Bài viết mang tính công kích cá nhân.'],
            [$stu6->id, $stu1->id,   null,       'User', 'Người dùng này vi phạm quy định nhóm.'],
            [$stu1->id, null,        $post5->id, 'Post', 'Bài viết quảng cáo trái phép.'],
            [$stu2->id, $stu4->id,   null,       'User', 'Cư xử thiếu văn minh trong bình luận.'],
            [$stu3->id, null,        $post8->id, 'Post', 'Nội dung không liên quan đến UIT.'],
            [$stu5->id, $stu6->id,   null,       'User', 'Tài khoản có dấu hiệu giả mạo.'],
        ];

        foreach ($reportsData as [$reporterId, $reportedUserId, $reportedPostId, $targetType, $reason]) {
            Report::factory()->create([
                'reporter_id'      => $reporterId,
                'reported_user_id' => $reportedUserId,
                'reported_post_id' => $reportedPostId,
                'target_type'      => $targetType,
                'reason'           => $reason,
                'status'           => Report::STATUS_PENDING,
                'resolved_by'      => null,
                'resolved_at'      => null,
            ]);
        }
    }
}
