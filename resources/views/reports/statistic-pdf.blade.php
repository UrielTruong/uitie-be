<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ── Report Header (vàng nhạt) ── */
        .report-header {
            background-color: #fefce8;
            border: 1px solid #ca8a04;
            padding: 12px 16px;
            text-align: center;
            margin-bottom: 0;
        }

        .report-header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a1a;
        }

        .report-header p {
            font-size: 9px;
            color: #555;
            margin-top: 4px;
        }

        /* ── Page Header (cam) ── */
        .page-header th {
            background-color: #f59e0b;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #d97706;
            font-weight: bold;
        }

        /* ── Details (trắng, xen kẽ xám nhạt) ── */
        .details td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .details tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        /* ── Section spacer ── */
        .section-gap {
            height: 16px;
        }

        /* ── Report Footer (cam) ── */
        .report-footer {
            background-color: #f59e0b;
            border: 1px solid #d97706;
            padding: 10px 16px;
            margin-top: 0;
        }

        .report-footer table td {
            border: none;
            color: #1a1a1a;
            padding: 2px 12px 2px 0;
        }

        .report-footer .label {
            font-weight: bold;
        }

        /* ── Page Footer (vàng nhạt) ── */
        .page-footer {
            background-color: #fefce8;
            border: 1px solid #ca8a04;
            padding: 6px 16px;
            font-size: 8px;
            color: #555;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    {{-- ═══════════════════════════════════════════════════════════
     REPORT HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <div class="report-header">
        <h1>BÁO CÁO THỐNG KÊ TỔNG QUAN HỆ THỐNG</h1>
        <p>Trường Đại học Công nghệ Thông tin &mdash; UIT</p>
        <p>Ngày xuất: {{ $generatedAt }}</p>
    </div>
    {{-- ═══════════════════════════════════════════════════════════
     BẢNG 1: THỐNG KÊ TỔNG QUAN
    ═══════════════════════════════════════════════════════════ --}}
    <table>
        <thead>
            <tr class="page-header">
                <th style="width: 8%">STT</th>
                <th>Chỉ số</th>
                <th style="width: 20%">Số lượng</th>
            </tr>
        </thead>
        <tbody>
            <tr class="details">
                <td class="text-center">1</td>
                <td>Người dùng đang hoạt động (Active)</td>
                <td class="font-bold">{{ $stats['users'] }}</td>
            </tr>
            <tr class="details">
                <td class="text-center">2</td>
                <td>Bài viết chờ duyệt (Pending)</td>
                <td class="font-bold">{{ $stats['posts'] }}</td>
            </tr>
            <tr class="details">
                <td class="text-center">3</td>
                <td>Báo cáo vi phạm chờ xử lý (Pending)</td>
                <td class="font-bold">{{ $stats['reports'] }}</td>
            </tr>
        </tbody>
    </table>
    {{-- ═══════════════════════════════════════════════════════════
     BẢNG 2: BÀI VIẾT THEO DANH MỤC
    ═══════════════════════════════════════════════════════════ --}}
    <div class="section-gap"></div>
    <table>
        <thead>
            <tr class="page-header">
                <th style="width: 8%">STT</th>
                <th>Danh mục</th>
                <th style="width: 20%">Số lượng bài viết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($postByCategory as $index => $row)
            <tr class="details">
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->category_name }}</td>
                <td class="font-bold">{{ $row->total }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center" style="padding: 20px; color: #888;">
                    Không có dữ liệu
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{-- ═══════════════════════════════════════════════════════════
     REPORT FOOTER
    ═══════════════════════════════════════════════════════════ --}}
    <div class="report-footer">
        <table>
            <tr>
                <td class="label">Người dùng hoạt động:</td>
                <td>{{ $stats['users'] }}</td>
                <td class="label">Bài viết chờ duyệt:</td>
                <td>{{ $stats['posts'] }}</td>
                <td class="label">Báo cáo chờ xử lý:</td>
                <td>{{ $stats['reports'] }}</td>
            </tr>
        </table>
    </div>
    {{-- ═══════════════════════════════════════════════════════════
     PAGE FOOTER
    ═══════════════════════════════════════════════════════════ --}}
    <div class="page-footer">
        <table>
            <tr>
                <td>Tài liệu được tạo tự động bởi hệ thống UITie</td>
                <td style="text-align: right">Xuất lúc: {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>
</body>

</html>