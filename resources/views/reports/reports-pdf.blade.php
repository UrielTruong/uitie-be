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

        /* Badge */
        .badge {
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-resolved {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-dismissed {
            background: #e5e7eb;
            color: #374151;
        }

        .badge-user {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-post {
            background: #ede9fe;
            color: #5b21b6;
        }
    </style>
</head>

<body>

    {{-- ═══════════════════════════════════════════════════════════
     REPORT HEADER
═══════════════════════════════════════════════════════════ --}}
    <div class="report-header">
        <h1>BÁO CÁO DANH SÁCH BÁO CÁO VI PHẠM</h1>
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
                <th style="width:16%">Người báo cáo</th>
                <th style="width:18%">Đối tượng bị báo cáo</th>
                <th style="width:8%">Loại</th>
                <th style="width:24%">Lý do</th>
                <th style="width:9%">Trạng thái</th>
                <th style="width:11%">Ngày tạo</th>
                <th style="width:10%">Xử lý bởi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
            <tr class="details">
                <td style="text-align:center">{{ $index + 1 }}</td>
                <td>{{ $report->reporter?->full_name ?? '—' }}</td>
                <td>
                    @if($report->target_type === 'User')
                    {{ $report->reportedUser?->full_name ?? '—' }}
                    @else
                    {{ \Illuminate\Support\Str::limit($report->reportedPost?->content, 60) ?? '—' }}
                    @endif
                </td>
                <td>
                    @php $typeClass = $report->target_type === 'User' ? 'badge-user' : 'badge-post'; @endphp
                    <span class="badge {{ $typeClass }}">{{ $report->target_type }}</span>
                </td>
                <td>{{ \Illuminate\Support\Str::limit($report->reason, 80) }}</td>
                <td>
                    @php
                    $statusClass = match($report->status) {
                    'Resolved' => 'badge-resolved',
                    'Dismissed' => 'badge-dismissed',
                    default => 'badge-pending',
                    };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $report->status }}</span>
                </td>
                <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $report->resolver?->full_name ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:20px; color:#888;">
                    Không có dữ liệu báo cáo
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
                <td class="label">Tổng số báo cáo:</td>
                <td>{{ $stats['total'] }}</td>
                <td class="label">Chờ xử lý:</td>
                <td>{{ $stats['pending'] }}</td>
                <td class="label">Đã xử lý:</td>
                <td>{{ $stats['resolved'] }}</td>
                <td class="label">Từ chối:</td>
                <td>{{ $stats['dismissed'] }}</td>
                <td class="label">User bị báo cáo:</td>
                <td>{{ $stats['userType'] }}</td>
                <td class="label">Bài viết bị báo cáo:</td>
                <td>{{ $stats['postType'] }}</td>
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