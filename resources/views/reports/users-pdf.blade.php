<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <style>
        /* ── Base ── */
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
            vertical-align: top;
        }

        .details tr:nth-child(even) td {
            background-color: #f9fafb;
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

        /* Badge trạng thái */
        .badge {
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-locked {
            background: #e5e7eb;
            color: #374151;
        }
    </style>
</head>

<body>

    {{-- ═══════════════════════════════════════════════════════════
     REPORT HEADER
═══════════════════════════════════════════════════════════ --}}
    <div class="report-header">
        <h1>BÁO CÁO DANH SÁCH NGƯỜI DÙNG</h1>
        <p>Trường Đại học Công nghệ Thông tin &mdash; UIT</p>
        <p>
            Ngày xuất: {{ $generatedAt }}
            @if(count(array_filter($filters)))
            &nbsp;&bull;&nbsp; Bộ lọc:
            @foreach(array_filter($filters) as $key => $value)
            <strong>{{ $key }}</strong>: {{ $value }}{{ !$loop->last ? ', ' : '' }}
            @endforeach
            @endif
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
     PAGE HEADER + DETAILS
═══════════════════════════════════════════════════════════ --}}
    <table>
        <thead>
            <tr class="page-header">
                <th style="width:4%">STT</th>
                <th style="width:20%">Họ và tên</th>
                <th style="width:16%">Email</th>
                <th style="width:9%">MSSV</th>
                <th style="width:10%">Khoa</th>
                <th style="width:10%">Lớp</th>
                <th style="width:8%">Khoá</th>
                <th style="width:10%">Số điện thoại</th>
                <th style="width:7%">Vai trò</th>
                <th style="width:6%">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
            <tr class="details">
                <td style="text-align:center">{{ $index + 1 }}</td>
                <td>{{ $user->full_name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->mssv ?? '—' }}</td>
                <td>{{ $user->faculty ?? '—' }}</td>
                <td>{{ $user->class_name ?? '—' }}</td>
                <td>{{ $user->academic_year ?? '—' }}</td>
                <td>{{ $user->phone_number ?? '—' }}</td>
                <td>{{ $user->role }}</td>
                <td>
                    @php
                    $badgeClass = match($user->status) {
                    'Active' => 'badge-active',
                    'Inactive' => 'badge-inactive',
                    default => 'badge-locked',
                    };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $user->status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; padding:20px; color:#888;">
                    Không có dữ liệu người dùng
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
                <td class="label">Tổng số người dùng:</td>
                <td>{{ $stats['total'] }}</td>
                <td class="label">Đang hoạt động:</td>
                <td>{{ $stats['active'] }}</td>
                <td class="label">Không hoạt động:</td>
                <td>{{ $stats['inactive'] }}</td>
                <td class="label">Bị khoá:</td>
                <td>{{ $stats['locked'] }}</td>
                <td class="label">Sinh viên:</td>
                <td>{{ $stats['student'] }}</td>
                <td class="label">Quản trị viên:</td>
                <td>{{ $stats['admin'] }}</td>
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
                <td style="text-align:right">Xuất lúc: {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

</body>

</html>