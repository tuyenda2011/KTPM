<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin accounts
        User::updateOrCreate([
            'user_id' => 'AD_001',
        ], [
            'name' => 'Trương Ngọc Thang',
            'email' => 'admin@ad.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'gender' => 'Nam',
            'phone' => '0903000000',
            'birth_date' => '1990-01-15',
            'address' => 'Tòa nhà FLC, Hà Nội, Việt Nam',
        ]);

        User::updateOrCreate([
            'user_id' => 'AD_002',
        ], [
            'name' => 'Vũ Minh Tâm',
            'email' => 'admin2@ad.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'gender' => 'Nam',
            'phone' => '0903111111',
            'birth_date' => '1988-05-10',
            'address' => 'Quận Hoàn Kiếm, Hà Nội, Việt Nam',
        ]);

        // Staff accounts - 20 nhân viên
        $staffs = [
            ['ST_001', 'Nguyễn Minh Anh', 'staff1@staff.com', 'Nam', '0901111111', '1995-05-20', 'An Khánh, Hà Nội'],
            ['ST_002', 'Trần Thảo Vy', 'staff2@staff.com', 'Nữ', '0902222222', '1998-08-30', 'Bình Thạnh, TP.HCM'],
            ['ST_003', 'Lê Quốc Cường', 'staff3@staff.com', 'Nam', '0901333333', '1992-03-15', 'Q1, TP.HCM'],
            ['ST_004', 'Phạm Ngọc Diễm', 'staff4@staff.com', 'Nữ', '0902444444', '1996-07-22', 'Thanh Xuân, Hà Nội'],
            ['ST_005', 'Hoàng Gia Bảo', 'staff5@staff.com', 'Nam', '0901555555', '1999-11-08', 'Hải Châu, Đà Nẵng'],
            ['ST_006', 'Võ Thu Hà', 'staff6@staff.com', 'Nữ', '0902666666', '1997-02-14', 'Tây Hồ, Hà Nội'],
            ['ST_007', 'Đặng Minh Quân', 'staff7@staff.com', 'Nam', '0901777777', '1994-09-25', 'Long Biên, Hà Nội'],
            ['ST_008', 'Bùi Khánh Linh', 'staff8@staff.com', 'Nữ', '0902888888', '1998-12-03', 'Quận 3, TP.HCM'],
            ['ST_009', 'Đỗ Hoàng Nam', 'staff9@staff.com', 'Nam', '0911000009', '1996-04-11', 'Hai Bà Trưng, Hà Nội'],
            ['ST_010', 'Lý Thanh Trúc', 'staff10@staff.com', 'Nữ', '0911000010', '1997-10-08', 'Nam Từ Liêm, Hà Nội'],
            ['ST_011', 'Nguyễn Khắc Huy', 'staff11@staff.com', 'Nam', '0911000011', '1995-12-19', 'Thủ Đức, TP.HCM'],
            ['ST_012', 'Trần Bảo Ngọc', 'staff12@staff.com', 'Nữ', '0911000012', '1998-02-26', 'Sơn Trà, Đà Nẵng'],
            ['ST_013', 'Phan Đức Thành', 'staff13@staff.com', 'Nam', '0911000013', '1994-06-22', 'Cầu Giấy, Hà Nội'],
            ['ST_014', 'Mai Thu Phương', 'staff14@staff.com', 'Nữ', '0911000014', '1997-01-13', 'Thanh Khê, Đà Nẵng'],
            ['ST_015', 'Vũ Gia Khánh', 'staff15@staff.com', 'Nam', '0911000015', '1996-09-17', 'Ninh Kiều, Cần Thơ'],
            ['ST_016', 'Nguyễn Quỳnh Anh', 'staff16@staff.com', 'Nữ', '0911000016', '1999-03-09', 'Tân Bình, TP.HCM'],
            ['ST_017', 'Trương Nhật Long', 'staff17@staff.com', 'Nam', '0911000017', '1997-08-21', 'Đống Đa, Hà Nội'],
            ['ST_018', 'Lê Hồng Nhung', 'staff18@staff.com', 'Nữ', '0911000018', '1998-05-27', 'Hải Châu, Đà Nẵng'],
            ['ST_019', 'Phạm Tuấn Kiệt', 'staff19@staff.com', 'Nam', '0911000019', '1996-11-02', 'Quận 10, TP.HCM'],
            ['ST_020', 'Đinh Bảo Trâm', 'staff20@staff.com', 'Nữ', '0911000020', '1999-01-18', 'Sơn Trà, Đà Nẵng'],
        ];

        foreach ($staffs as $staff) {
            User::updateOrCreate([
                'user_id' => $staff[0],
            ], [
                'name' => $staff[1],
                'email' => $staff[2],
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'gender' => $staff[3],
                'phone' => $staff[4],
                'birth_date' => $staff[5],
                'address' => $staff[6],
            ]);
        }

        // User/Candidate accounts - 15 ứng viên
        $users = [
            // Group 1: Frontend talents
            ['US_001', 'Đàm Trần Minh Quang', 'user1@gmail.com', 'Nam', '0903333333', '1996-03-12', 'Cần Thơ'],
            ['US_002', 'Phạm Gia Hân', 'user2@gmail.com', 'Nữ', '0904444444', '1997-07-22', 'Huế'],
            ['US_003', 'Hoàng Quốc Bảo', 'user3@gmail.com', 'Nam', '0905555555', '1999-11-08', 'Hải Phòng'],
            ['US_004', 'Ngô Tuấn Hùng', 'user4@gmail.com', 'Nam', '0923456789', '1998-06-15', 'Quận 1, TPHCM'],
            ['US_005', 'Hoàng Yến Nhi', 'user5@gmail.com', 'Nữ', '0945678901', '1999-03-20', 'Quận 5, TPHCM'],
            
            // Group 2: Backend talents
            ['US_006', 'Trịnh Minh Tuấn', 'user6@gmail.com', 'Nam', '0923333333', '1995-08-10', 'Ba Đình, Hà Nội'],
            ['US_007', 'Tạ Thúy Linh', 'user7@gmail.com', 'Nữ', '0944444444', '1997-05-18', 'Cầu Giấy, Hà Nội'],
            ['US_008', 'Phan Quốc Anh', 'user8@gmail.com', 'Nam', '0924444444', '1996-11-25', 'Biên Hòa, ĐN'],
            
            // Group 3: HR talents
            ['US_009', 'Hạ Thu Hương', 'user9@gmail.com', 'Nữ', '0934555555', '1996-01-30', 'Đống Đa, Hà Nội'],
            ['US_010', 'Kiên Anh Tuấn', 'user10@gmail.com', 'Nam', '0925555555', '1998-04-12', 'Gò Vấp, TPHCM'],
            
            // Group 4: Accounting talents
            ['US_011', 'Hà Thanh Huyền', 'user11@gmail.com', 'Nữ', '0935666666', '1995-09-07', 'Liên Chiểu, Đà Nẵng'],
            ['US_012', 'Tiến Minh Quốc', 'user12@gmail.com', 'Nam', '0926666666', '1997-02-22', 'Quận 7, TPHCM'],
            
            // Group 5: Marketing talents
            ['US_013', 'Hiền Phương Linh', 'user13@gmail.com', 'Nữ', '0936777777', '1998-07-19', 'Tây Hồ, Hà Nội'],
            ['US_014', 'Vũ Văn Minh', 'user14@gmail.com', 'Nam', '0927777777', '1996-10-05', 'Nhà Bè, TPHCM'],
            ['US_015', 'Nguyễn Thúy Vân', 'user15@gmail.com', 'Nữ', '0937888888', '1999-12-14', 'Hà Đông, Hà Nội'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate([
                'user_id' => $user[0],
            ], [
                'name' => $user[1],
                'email' => $user[2],
                'password' => Hash::make('password123'),
                'role' => 'user',
                'gender' => $user[3],
                'phone' => $user[4],
                'birth_date' => $user[5],
                'address' => $user[6],
            ]);
        }
    }
}
