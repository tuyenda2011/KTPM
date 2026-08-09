<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\JobPosting;
use App\Models\Candidate;
use App\Models\Interview;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Thống kê nhân viên
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'Đang làm')->count();
        $onLeaveEmployees = Employee::where('status', 'Tạm nghỉ')->count();
        $resignedEmployees = Employee::where('status', 'Nghỉ việc')->count();

        // Thống kê tuyển dụng
        $totalApplications = Candidate::count();
        $totalInterviews = Candidate::where('status', 'Phỏng vấn')->count();
        $hiredCandidates = Candidate::whereIn('status', ['Đậu', 'Nhận việc', 'Đã nhận việc'])->count();
        $rejectedCandidates = Candidate::whereIn('status', ['Rớt', 'Từ chối'])->count();

        // Thống kê theo phòng ban
        $departmentStats = Department::withCount('employees')
            ->get()
            ->map(function ($dept) {
                return [
                    'name' => $dept->name,
                    'count' => $dept->employees_count
                ];
            });

        return view('dashboard', [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'onLeaveEmployees' => $onLeaveEmployees,
            'resignedEmployees' => $resignedEmployees,
            'totalApplications' => $totalApplications,
            'totalInterviews' => $totalInterviews,
            'hiredCandidates' => $hiredCandidates,
            'rejectedCandidates' => $rejectedCandidates,
            'departmentStats' => $departmentStats,
        ]);
    }
}