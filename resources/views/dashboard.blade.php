@extends('admin')
@section('title', 'Dashboard')
@section('content')

    <div class="flex gap-6 h-[calc(100vh-10rem)]" style="margin-left: -27px; padding-left: 5px;">
        <!-- Sidebar Stats -->
        <div class="w-80 bg-white rounded-lg shadow p-4 overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Thống Kê Nhanh</h3>
            <div class="space-y-3">
                <div class="p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600">Tổng Nhân Viên</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $totalEmployees }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                    <p class="text-xs text-gray-600">Đang Làm Việc</p>
                    <p class="text-2xl font-bold text-green-600">{{ $activeEmployees }}</p>
                </div>
                <div class="p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                    <p class="text-xs text-gray-600">Tạm Nghỉ</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $onLeaveEmployees }}</p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg border-l-4 border-red-500">
                    <p class="text-xs text-gray-600">Nghỉ Việc</p>
                    <p class="text-2xl font-bold text-red-600">{{ $resignedEmployees }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 bg-white rounded-lg shadow p-6 overflow-y-auto">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <button onclick="exportDashboardFile('pdf')" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">
                    🧾 Xuất PDF
                </button>
                @endif
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Employee Status Chart -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Trạng Thái Nhân Viên</h4>
                    <div style="height: 250px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Department Distribution -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Nhân Viên Theo Phòng Ban</h4>
                    <div style="height: 250px;">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recruitment Stats -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-800 mb-4">Thống Kê Tuyển Dụng</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-3 bg-white rounded border-l-4 border-blue-500">
                        <p class="text-xs text-gray-600">Hồ sơ nhận được</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $totalApplications }}</p>
                    </div>
                    <div class="p-3 bg-white rounded border-l-4 border-yellow-500">
                        <p class="text-xs text-gray-600">Đang phỏng vấn</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $totalInterviews }}</p>
                    </div>
                    <div class="p-3 bg-white rounded border-l-4 border-green-500">
                        <p class="text-xs text-gray-600">Đã nhận việc</p>
                        <p class="text-2xl font-bold text-green-600">{{ $hiredCandidates }}</p>
                    </div>
                    <div class="p-3 bg-white rounded border-l-4 border-red-500">
                        <p class="text-xs text-gray-600">Từ chối</p>
                        <p class="text-2xl font-bold text-red-600">{{ $rejectedCandidates }}</p>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- SheetJS for Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- jsPDF for PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
    <script>
    // Dùng font mặc định cho toàn bộ autotable (head/body/foot/column)
    function applyAutoTableDefaults() {
        if (window.jspdf && window.jspdf.autoTableSetDefaults) {
            window.jspdf.autoTableSetDefaults({
            styles:     { font: 'SerifVN', fontSize: 11 },
            headStyles: { font: 'SerifVN', fontStyle: 'normal' },
            bodyStyles: { font: 'SerifVN', fontStyle: 'normal' },
            footStyles: { font: 'SerifVN', fontStyle: 'normal' },
            columnStyles: {} // có thể set từng cột sau
            });
        }
    }

    // Chuẩn hoá Unicode về NFC để không bị tách dấu
    function vn(text) {
        if (text === null || text === undefined) return '';
        try { return text.toString().normalize('NFC'); }
        catch { return text.toString(); }
    }
    </script>

    <script>
    // Nạp 1 font serif có hỗ trợ tiếng Việt từ CDN và đăng ký vào jsPDF
    async function loadCDNFont(doc) {
        const sources = [
            "https://cdn.jsdelivr.net/gh/googlefonts/noto-fonts@main/hinted/ttf/NotoSerif/NotoSerif-Regular.ttf",
            "https://cdn.jsdelivr.net/gh/dejavu-fonts/dejavu-fonts-ttf@version_2_37/ttf/DejaVuSerif.ttf"
        ];
        let base64 = null, postName = "SerifVN";
        for (const url of sources) {
            try {
            const buf = await fetch(url, {mode:'cors'}).then(r => r.arrayBuffer());
            const bytes = new Uint8Array(buf);
            let bin = ""; for (let i=0;i<bytes.length;i++) bin += String.fromCharCode(bytes[i]);
            base64 = btoa(bin);
            break;
            } catch (e) {}
        }
        if (!base64) return alert("Không tải được font từ CDN. PDF có thể lỗi tiếng Việt.");

        doc.addFileToVFS(postName + ".ttf", base64);
        doc.addFont(postName + ".ttf", postName, "normal");
        doc.setFont(postName); // dùng font này cho toàn bộ PDF
    }
    </script>

    <script>
        // VẼ CHART
        // Status Chart (Pie)
        const statusCtx = document.getElementById('statusChart');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Đang làm', 'Tạm nghỉ', 'Nghỉ việc'],
                datasets: [{
                    data: [{{ $activeEmployees }}, {{ $onLeaveEmployees }}, {{ $resignedEmployees }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderColor: ['#059669', '#d97706', '#dc2626'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Department Chart (Bar)
        const departmentCtx = document.getElementById('departmentChart');
        const deptLabels = {!! json_encode($departmentStats->pluck('name')) !!};
        const deptCounts = {!! json_encode($departmentStats->pluck('count')) !!};
        
        new Chart(departmentCtx, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Số nhân viên',
                    data: deptCounts,
                    backgroundColor: '#3B82F6',
                    borderColor: '#1E40AF',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
    </script>

    <script>
    /**
     * Xuất dashboard HR ra file PDF
     */
    async function exportDashboardFile(type) {
        const fileName = `dashboard-nhan-su-{{ now()->format('Y-m-d') }}`;

        if (type === 'pdf') {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Nạp font Unicode
            await loadCDNFont(doc);
            doc.setFont("SerifVN");
            doc.setCharSpace(0);
            doc.setLineHeightFactor(1.15);

            const vn = (t) => {
                if (t === null || t === undefined) return '';
                try { t = t.toString().normalize('NFC'); } catch {}
                return t.replace(/\u00A0/g, ' ');
            };

            // Tiêu đề
            doc.setFontSize(18);
            doc.text(vn("BÁO CÁO THỐNG KÊ NHÂN SỰ"), 14, 20);
            doc.setFontSize(11);
            doc.text(vn("Ngày: " + new Date().toLocaleDateString('vi-VN')), 14, 28);

            // Thống kê chung
            doc.setFontSize(14);
            doc.text(vn("1) THỐNG KÊ CHUNG"), 14, 40);

            const statsData = [
                ['Tổng Nhân Viên', '{{ $totalEmployees }}'],
                ['Đang Làm Việc', '{{ $activeEmployees }}'],
                ['Tạm Nghỉ', '{{ $onLeaveEmployees }}'],
                ['Nghỉ Việc', '{{ $resignedEmployees }}']
            ];

            doc.autoTable({
                startY: 45,
                styles: { font: 'SerifVN', fontSize: 11 },
                headStyles: { font: 'SerifVN', fillColor: [16, 185, 129], textColor: [255, 255, 255] },
                bodyStyles: { font: 'SerifVN' },
                head: [['Chỉ số', 'Giá trị']],
                body: statsData,
                theme: 'grid'
            });

            let finalY = doc.lastAutoTable.finalY + 10;
            doc.setFontSize(14);
            doc.text(vn("2) NHÂN VIÊN THEO PHÒNG BAN"), 14, finalY);

            const deptData = [
                @forelse($departmentStats as $dept)
                ['{{ $dept['name'] }}', '{{ $dept['count'] }}'],
                @empty
                @endforelse
            ];

            doc.autoTable({
                startY: finalY + 5,
                styles: { font: 'SerifVN', fontSize: 11 },
                headStyles: { font: 'SerifVN', fillColor: [59, 130, 246], textColor: [255, 255, 255] },
                bodyStyles: { font: 'SerifVN' },
                head: [['Phòng Ban', 'Số Nhân Viên']],
                body: deptData,
                theme: 'striped'
            });

            finalY = doc.lastAutoTable.finalY + 10;
            doc.setFontSize(14);
            doc.text(vn("3) THỐNG KÊ TUYỂN DỤNG"), 14, finalY);

            const recruitData = [
                ['Hồ sơ nhận được', '{{ $totalApplications }}'],
                ['Đang phỏng vấn', '{{ $totalInterviews }}'],
                ['Đã nhận việc', '{{ $hiredCandidates }}'],
                ['Từ chối', '{{ $rejectedCandidates }}']
            ];

            doc.autoTable({
                startY: finalY + 5,
                styles: { font: 'SerifVN', fontSize: 11 },
                headStyles: { font: 'SerifVN', fillColor: [16, 185, 129], textColor: [255, 255, 255] },
                bodyStyles: { font: 'SerifVN' },
                head: [['Trạng Thái', 'Số Lượng']],
                body: recruitData,
                theme: 'grid'
            });

            doc.save(`${fileName}.pdf`);
        }
    }
    </script>

    </main>

@endsection