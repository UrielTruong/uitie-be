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

        .badge-accepted {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-public {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-private {
            background: #e5e7eb;
            color: #374151;
        }

        /* Truncate nội dung dài */
        .content-cell {
            max-width: 200px;
            overflow: hidden;
        }
    </style>
</head>

<body>

    {{-- ═══════════════════════════════════════════════════════════
     REPORT HEADER
═══════════════════════════════════════════════════════════ --}}
    <div class="report-header">
        <h1>BÁO CÁO DANH SÁCH BÀI VIẾT</h1>
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
                <th style="width:15%">Tác giả</th>
                <th style="width:35%">Nội dung</th>
                <th style="width:12%">Danh mục</th>
                <th style="width:10%">Hiển thị</th>
                <th style="width:10%">Trạng thái</th>
                <th style="width:14%">Ngày tạo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $index => $post)
            <tr class="details">
                <td style="text-align:center">{{ $index + 1 }}</td>
                <td>{{ $post->user?->full_name ?? '—' }}</td>
                <td class="content-cell">{{ \Illuminate\Support\Str::limit($post->content, 120) }}</td>
                <td>{{ $post->category?->category_name ?? '—' }}</td>
                <td>
                    @php $visClass = $post->visibility === 'Public' ? 'badge-public' : 'badge-private'; @endphp
                    <span class="badge {{ $visClass }}">{{ $post->visibility }}</span>
                </td>
                <td>
                    @php
                    $statusClass = match($post->status) {
                    'Accepted' => 'badge-accepted',
                    'Pending' => 'badge-pending',
                    default => 'badge-rejected',
                    };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $post->status }}</span>
                </td>
                <td>{{ $post->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:20px; color:#888;">
                    Không có dữ liệu bài viết
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
                <td class="label">Tổng số bài viết:</td>
                <td>{{ $stats['total'] }}</td>
                <td class="label">Đã duyệt:</td>
                <td>{{ $stats['accepted'] }}</td>
                <td class="label">Chờ duyệt:</td>
                <td>{{ $stats['pending'] }}</td>
                <td class="label">Từ chối:</td>
                <td>{{ $stats['rejected'] }}</td>
                <td class="label">Công khai:</td>
                <td>{{ $stats['public'] }}</td>
                <td class="label">Riêng tư:</td>
                <td>{{ $stats['private'] }}</td>
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