<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
    * Tạo employee profile cho 2 admin + 20 staff accounts
     */
    public function run(): void
    {
        // Admin accounts
        Employee::updateOrCreate([
            'user_id' => 'AD_001',
        ], [
            'position' => 'Giám đốc điều hành',
            'department_id' => 'DEPT001',
            'identity_card' => '012345678901',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'Hà Nội',
            'current_address' => '123 Tòa FLC, Đống Đa, Hà Nội',
            'start_date' => '2010-01-01',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Quản trị kinh doanh',
            'school_name' => 'Đại học Kinh tế Quốc dân',
            'certificates' => 'Chứng chỉ Quản trị doanh nghiệp, Chứng chỉ lãnh đạo cấp cao',
            'language_certificates' => 'IELTS 7.5',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Admin - Giám đốc hệ thống',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'AD_002',
        ], [
            'position' => 'Phó giám đốc',
            'department_id' => 'DEPT003',
            'identity_card' => '111111111111',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'Hà Nội',
            'current_address' => '456 Phố Huế, Hoàn Kiếm, Hà Nội',
            'start_date' => '2012-03-15',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Công nghệ thông tin',
            'school_name' => 'Đại học Bách Khoa Hà Nội',
            'certificates' => 'CCNA, AWS Cloud Practitioner',
            'language_certificates' => 'TOEIC 850',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phó giám đốc IT',
        ]);

        // HR Department (DEPT001)
        Employee::updateOrCreate([
            'user_id' => 'ST_001',
        ], [
            'position' => 'Trưởng phòng Nhân sự',
            'department_id' => 'DEPT001',
            'identity_card' => '012345678902',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'Hà Nội',
            'current_address' => '123 Đường Láng, Đống Đa, Hà Nội',
            'start_date' => '2015-02-01',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Quản trị nhân lực',
            'school_name' => 'Đại học Lao động - Xã hội',
            'certificates' => 'Chứng chỉ C&B, Chứng chỉ Tuyển dụng chuyên sâu',
            'language_certificates' => 'TOEIC 700',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Quản lý tuyển dụng và quản lý nhân sự',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_004',
        ], [
            'position' => 'Chuyên viên Nhân sự',
            'department_id' => 'DEPT001',
            'identity_card' => '012345678904',
            'marital_status' => 'Độc thân',
            'hometown' => 'Hà Nội',
            'current_address' => '789 Nguyễn Chí Thanh, Thanh Xuân, Hà Nội',
            'start_date' => '2018-06-15',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Quản trị kinh doanh',
            'school_name' => 'Đại học Hà Nội',
            'certificates' => 'Chứng chỉ Tin học văn phòng MOS',
            'language_certificates' => 'TOEIC 650',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Hỗ trợ tuyển dụng, đào tạo nhân viên',
        ]);

        // Accounting Department (DEPT002)
        Employee::updateOrCreate([
            'user_id' => 'ST_002',
        ], [
            'position' => 'Trưởng phòng Kế toán',
            'department_id' => 'DEPT002',
            'identity_card' => '123456789012',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'TP Hồ Chí Minh',
            'current_address' => '456 Nguyễn Hữu Cảnh, Bình Thạnh, TPHCM',
            'start_date' => '2016-03-15',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Kế toán',
            'school_name' => 'Đại học Kinh tế TP.HCM',
            'certificates' => 'Chứng chỉ Kế toán trưởng, Chứng chỉ Excel nâng cao',
            'language_certificates' => 'TOEIC 620',
            'ethnicity' => 'Kinh',
            'religion' => 'Công giáo',
            'nationality' => 'Việt Nam',
            'notes' => 'Quản lý kế toán tài chính',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_006',
        ], [
            'position' => 'Chuyên viên Kế toán',
            'department_id' => 'DEPT002',
            'identity_card' => '123456789906',
            'marital_status' => 'Độc thân',
            'hometown' => 'Tây Hồ',
            'current_address' => '321 Cộng Hòa, Tây Hồ, Hà Nội',
            'start_date' => '2019-01-20',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Tài chính - Kế toán',
            'school_name' => 'Học viện Tài chính',
            'certificates' => 'MOS Excel, Chứng chỉ nghiệp vụ kế toán tổng hợp',
            'language_certificates' => 'TOEIC 580',
            'ethnicity' => 'Kinh',
            'religion' => 'Phật giáo',
            'nationality' => 'Việt Nam',
            'notes' => 'Hỗ trợ lập báo cáo tài chính',
        ]);

        // IT Department (DEPT003)
        Employee::updateOrCreate([
            'user_id' => 'ST_003',
        ], [
            'position' => 'Trưởng phòng IT',
            'department_id' => 'DEPT003',
            'identity_card' => '234567890123',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'TP Hồ Chí Minh',
            'current_address' => '789 Tôn Đức Thắng, Quận 1, TPHCM',
            'start_date' => '2014-05-10',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Kỹ sư Công nghệ thông tin',
            'school_name' => 'Đại học Công nghệ Thông tin - ĐHQG TP.HCM',
            'certificates' => 'AWS Solutions Architect, Scrum Master',
            'language_certificates' => 'IELTS 6.5',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Quản lý phát triển hệ thống IT',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_005',
        ], [
            'position' => 'Lập trình viên PHP',
            'department_id' => 'DEPT003',
            'identity_card' => '234567890125',
            'marital_status' => 'Độc thân',
            'hometown' => 'Đà Nẵng',
            'current_address' => '654 Trần Phú, Hải Châu, Đà Nẵng',
            'start_date' => '2017-08-01',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Khoa học máy tính',
            'school_name' => 'Đại học Bách Khoa Đà Nẵng',
            'certificates' => 'Laravel Certification, Chứng chỉ MySQL cơ bản',
            'language_certificates' => 'TOEIC 720',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phát triển backend Laravel',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_007',
        ], [
            'position' => 'Lập trình viên Frontend',
            'department_id' => 'DEPT003',
            'identity_card' => '234567890127',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'Long Biên',
            'current_address' => '111 Phạm Văn Bạch, Long Biên, Hà Nội',
            'start_date' => '2018-04-15',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Hệ thống thông tin',
            'school_name' => 'Đại học FPT',
            'certificates' => 'Chứng chỉ UI/UX Foundation, MOS PowerPoint',
            'language_certificates' => 'IELTS 6.0',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phát triển frontend React',
        ]);

        // Marketing Department (DEPT004)
        Employee::updateOrCreate([
            'user_id' => 'ST_008',
        ], [
            'position' => 'Trưởng phòng Marketing',
            'department_id' => 'DEPT004',
            'identity_card' => '345678901028',
            'marital_status' => 'Độc thân',
            'hometown' => 'Quận 3',
            'current_address' => '222 Trần Xuân Soạn, Quận 3, TPHCM',
            'start_date' => '2019-09-01',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Marketing',
            'school_name' => 'Đại học Thương mại',
            'certificates' => 'Google Ads Search Certification, Facebook Blueprint',
            'language_certificates' => 'TOEIC 680',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phụ trách quản lý phòng Marketing và chiến dịch Digital',
        ]);

        // Additional sample staff - 2 người mỗi phòng ban
        Employee::updateOrCreate([
            'user_id' => 'ST_009',
        ], [
            'position' => 'Chuyên viên Tuyển dụng',
            'department_id' => 'DEPT001',
            'identity_card' => '456789012339',
            'marital_status' => 'Độc thân',
            'hometown' => 'Hà Nội',
            'current_address' => '32 Lê Thanh Nghị, Hai Bà Trưng, Hà Nội',
            'start_date' => '2020-03-02',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Quản trị nhân lực',
            'school_name' => 'Đại học Nội vụ Hà Nội',
            'certificates' => 'Chứng chỉ Talent Acquisition',
            'language_certificates' => 'TOEIC 650',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phụ trách sàng lọc CV và phỏng vấn sơ bộ',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_010',
        ], [
            'position' => 'Chuyên viên C&B',
            'department_id' => 'DEPT001',
            'identity_card' => '456789012340',
            'marital_status' => 'Độc thân',
            'hometown' => 'Nam Định',
            'current_address' => '88 Mễ Trì Hạ, Nam Từ Liêm, Hà Nội',
            'start_date' => '2021-06-15',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Kinh tế lao động',
            'school_name' => 'Đại học Lao động - Xã hội',
            'certificates' => 'Chứng chỉ Payroll',
            'language_certificates' => 'TOEIC 600',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Quản lý lương thưởng và phúc lợi nhân sự',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_011',
        ], [
            'position' => 'Kế toán tổng hợp',
            'department_id' => 'DEPT002',
            'identity_card' => '567890123451',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'Nghệ An',
            'current_address' => '14 Võ Văn Ngân, Thủ Đức, TP.HCM',
            'start_date' => '2020-09-10',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Kế toán doanh nghiệp',
            'school_name' => 'Đại học Kinh tế TP.HCM',
            'certificates' => 'Chứng chỉ Kế toán tổng hợp',
            'language_certificates' => 'TOEIC 610',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phụ trách hạch toán và báo cáo tháng',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_012',
        ], [
            'position' => 'Kế toán công nợ',
            'department_id' => 'DEPT002',
            'identity_card' => '567890123452',
            'marital_status' => 'Độc thân',
            'hometown' => 'Đà Nẵng',
            'current_address' => '27 Ngô Quyền, Sơn Trà, Đà Nẵng',
            'start_date' => '2021-11-20',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Tài chính doanh nghiệp',
            'school_name' => 'Đại học Duy Tân',
            'certificates' => 'Chứng chỉ Kế toán công nợ',
            'language_certificates' => 'TOEIC 580',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Theo dõi và đối soát công nợ khách hàng',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_013',
        ], [
            'position' => 'Kỹ sư Backend',
            'department_id' => 'DEPT003',
            'identity_card' => '678901234563',
            'marital_status' => 'Đã kết hôn',
            'hometown' => 'Hải Dương',
            'current_address' => '67 Trần Thái Tông, Cầu Giấy, Hà Nội',
            'start_date' => '2019-05-07',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Kỹ sư Khoa học máy tính',
            'school_name' => 'Đại học Bách Khoa Hà Nội',
            'certificates' => 'AWS Developer Associate',
            'language_certificates' => 'IELTS 6.5',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phát triển API và tối ưu hiệu năng hệ thống',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_014',
        ], [
            'position' => 'QA Engineer',
            'department_id' => 'DEPT003',
            'identity_card' => '678901234564',
            'marital_status' => 'Độc thân',
            'hometown' => 'Quảng Nam',
            'current_address' => '12 Điện Biên Phủ, Thanh Khê, Đà Nẵng',
            'start_date' => '2022-02-14',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Công nghệ thông tin',
            'school_name' => 'Đại học Bách Khoa Đà Nẵng',
            'certificates' => 'ISTQB Foundation',
            'language_certificates' => 'TOEIC 700',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phụ trách kiểm thử chức năng và regression test',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_015',
        ], [
            'position' => 'Content Marketing',
            'department_id' => 'DEPT004',
            'identity_card' => '789012345675',
            'marital_status' => 'Độc thân',
            'hometown' => 'An Giang',
            'current_address' => '45 Nguyễn Trãi, Ninh Kiều, Cần Thơ',
            'start_date' => '2020-07-30',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Truyền thông',
            'school_name' => 'Đại học Cần Thơ',
            'certificates' => 'Content Marketing Specialist',
            'language_certificates' => 'TOEIC 620',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Xây dựng nội dung tuyển dụng và thương hiệu tuyển dụng',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_016',
        ], [
            'position' => 'Chuyên viên Performance Marketing',
            'department_id' => 'DEPT004',
            'identity_card' => '789012345676',
            'marital_status' => 'Độc thân',
            'hometown' => 'TP Hồ Chí Minh',
            'current_address' => '91 Cộng Hòa, Tân Bình, TP.HCM',
            'start_date' => '2021-04-05',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Marketing số',
            'school_name' => 'Đại học Kinh tế TP.HCM',
            'certificates' => 'Google Ads Display Certification',
            'language_certificates' => 'TOEIC 640',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Quản lý quảng cáo và tối ưu chuyển đổi',
        ]);

        // Ensure each department has at least 5 staff members
        Employee::updateOrCreate([
            'user_id' => 'ST_017',
        ], [
            'position' => 'Chuyên viên Đào tạo nội bộ',
            'department_id' => 'DEPT001',
            'identity_card' => '890123456787',
            'marital_status' => 'Độc thân',
            'hometown' => 'Hà Nam',
            'current_address' => '18 Tôn Đức Thắng, Đống Đa, Hà Nội',
            'start_date' => '2022-08-01',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Quản trị nhân sự',
            'school_name' => 'Đại học Thương mại',
            'certificates' => 'Chứng chỉ Train The Trainer',
            'language_certificates' => 'TOEIC 620',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phụ trách đào tạo hội nhập và kỹ năng mềm',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_018',
        ], [
            'position' => 'Chuyên viên Kiểm soát chi phí',
            'department_id' => 'DEPT002',
            'identity_card' => '890123456788',
            'marital_status' => 'Độc thân',
            'hometown' => 'Quảng Ngãi',
            'current_address' => '55 Nguyễn Văn Linh, Hải Châu, Đà Nẵng',
            'start_date' => '2022-03-12',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Kế toán kiểm toán',
            'school_name' => 'Đại học Kinh tế Đà Nẵng',
            'certificates' => 'Chứng chỉ Kế toán quản trị',
            'language_certificates' => 'TOEIC 600',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Theo dõi ngân sách và kiểm soát chi phí dự án',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_019',
        ], [
            'position' => 'Chuyên viên Brand Marketing',
            'department_id' => 'DEPT004',
            'identity_card' => '890123456789',
            'marital_status' => 'Độc thân',
            'hometown' => 'TP Hồ Chí Minh',
            'current_address' => '102 Lý Thường Kiệt, Quận 10, TP.HCM',
            'start_date' => '2021-09-06',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Truyền thông marketing',
            'school_name' => 'Đại học Kinh tế TP.HCM',
            'certificates' => 'Brand Management Certification',
            'language_certificates' => 'TOEIC 630',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Phát triển nhận diện thương hiệu và nội dung chiến dịch',
        ]);

        Employee::updateOrCreate([
            'user_id' => 'ST_020',
        ], [
            'position' => 'Chuyên viên Social Media',
            'department_id' => 'DEPT004',
            'identity_card' => '890123456790',
            'marital_status' => 'Độc thân',
            'hometown' => 'Đà Nẵng',
            'current_address' => '9 Võ Nguyên Giáp, Sơn Trà, Đà Nẵng',
            'start_date' => '2023-01-10',
            'status' => 'Đang làm',
            'education_level' => 'Đại học',
            'degree' => 'Cử nhân Quan hệ công chúng',
            'school_name' => 'Đại học Duy Tân',
            'certificates' => 'Meta Certified Digital Marketing Associate',
            'language_certificates' => 'TOEIC 590',
            'ethnicity' => 'Kinh',
            'religion' => 'Không',
            'nationality' => 'Việt Nam',
            'notes' => 'Quản lý kênh mạng xã hội và lịch nội dung',
        ]);

        $previousExperienceMap = [
            'AD_001' => '15 năm quản trị doanh nghiệp và vận hành nhân sự tại tập đoàn dịch vụ.',
            'AD_002' => '12 năm quản lý công nghệ thông tin và triển khai hệ thống doanh nghiệp.',
            'ST_001' => '8 năm kinh nghiệm tuyển dụng và xây dựng chính sách nhân sự.',
            'ST_002' => '7 năm kinh nghiệm kế toán tổng hợp và lập báo cáo tài chính.',
            'ST_003' => '10 năm kinh nghiệm phát triển phần mềm và quản lý đội kỹ thuật.',
            'ST_004' => '5 năm kinh nghiệm hành chính nhân sự và đào tạo nội bộ.',
            'ST_005' => '6 năm phát triển ứng dụng web PHP/Laravel cho doanh nghiệp.',
            'ST_006' => '4 năm kinh nghiệm kế toán nội bộ và kiểm soát chứng từ.',
            'ST_007' => '5 năm kinh nghiệm frontend với React, Tailwind và tối ưu UX.',
            'ST_008' => '7 năm kinh nghiệm digital marketing và quản lý chiến dịch đa kênh.',
            'ST_009' => '4 năm kinh nghiệm tuyển dụng vị trí IT và nghiệp vụ văn phòng.',
            'ST_010' => '3 năm kinh nghiệm C&B, quản lý chấm công và phúc lợi.',
            'ST_011' => '5 năm kinh nghiệm hạch toán kế toán và quyết toán thuế.',
            'ST_012' => '4 năm kinh nghiệm công nợ phải thu phải trả và đối soát số liệu.',
            'ST_013' => '6 năm kinh nghiệm backend API, database design và tối ưu hiệu năng.',
            'ST_014' => '3 năm kinh nghiệm kiểm thử phần mềm manual và automation cơ bản.',
            'ST_015' => '4 năm kinh nghiệm content marketing và xây dựng kịch bản truyền thông.',
            'ST_016' => '4 năm kinh nghiệm chạy quảng cáo hiệu suất và phân tích chuyển đổi.',
            'ST_017' => '3 năm kinh nghiệm tổ chức đào tạo hội nhập và phát triển năng lực nhân viên.',
            'ST_018' => '3 năm kinh nghiệm kiểm soát chi phí và phân tích ngân sách hoạt động.',
            'ST_019' => '4 năm kinh nghiệm triển khai brand campaign và quản lý tài sản thương hiệu.',
            'ST_020' => '2 năm kinh nghiệm quản trị social media và sáng tạo nội dung số.',
        ];

        foreach ($previousExperienceMap as $userId => $experience) {
            Employee::where('user_id', $userId)->update([
                'previous_experience' => $experience,
            ]);
        }
    }
}

