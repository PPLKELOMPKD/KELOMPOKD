<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Event - {{ $companyName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #ffffff;
            padding: 30px 36px;
        }

        /* ── Header ── */
        .header-table {
            width: 100%;
            border-bottom: 3px solid #10B981;
            margin-bottom: 18px;
            padding-bottom: 12px;
        }
        .header-logo {
            font-size: 22px;
            font-weight: 900;
            color: #10B981;
            letter-spacing: -0.5px;
        }
        .header-logo span { color: #0f172a; }
        .report-title {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 3px;
        }
        .report-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .header-meta {
            text-align: right;
            color: #64748b;
            font-size: 9px;
            line-height: 1.8;
        }
        .header-meta strong { color: #0f172a; font-size: 9px; }

        /* ── Summary Cards ── */
        .summary-label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .card-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .card-inner {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
        }
        .card-inner .c-label {
            font-size: 7.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .card-inner .c-value {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 4px;
        }
        .card-green  { border-top: 3px solid #10B981; }
        .card-blue   { border-top: 3px solid #3B82F6; }
        .card-purple { border-top: 3px solid #8B5CF6; }
        .card-orange { border-top: 3px solid #F59E0B; }

        /* ── Section Title ── */
        .section-title {
            font-size: 10px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Data Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table thead tr { background: #0f172a; color: #ffffff; }
        .data-table thead th {
            padding: 9px 10px;
            text-align: left;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .data-table thead th.center { text-align: center; }
        .data-table thead th.right  { text-align: right; }
        .data-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .data-table tbody tr.even { background: #f8fafc; }
        .data-table tbody td {
            padding: 8px 10px;
            font-size: 9px;
            color: #334155;
            vertical-align: middle;
        }
        .data-table tbody td.center { text-align: center; }
        .data-table tbody td.right  { text-align: right; }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: 700;
        }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-gray   { background: #f1f5f9; color: #475569; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }

        /* ── Footer ── */
        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top;">
                <div class="header-logo">SIKARA<span>.</span></div>
                <div class="report-title">Laporan Event</div>
                <div class="report-subtitle">{{ $companyName }}</div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="header-meta">
                    <strong>Periode:</strong> {{ str_replace('-', ' ', $monthLabel) }}<br>
                    <strong>Dibuat:</strong> {{ $generatedAt }}<br>
                    <strong>Platform:</strong> SIKARA – Sistem Informasi Karier
                </div>
            </td>
        </tr>
    </table>

    {{-- Summary Cards --}}
    <div class="summary-label">Ringkasan</div>
    <table class="card-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="padding-right: 6px;">
                <div class="card-inner card-green">
                    <div class="c-label">Total Event</div>
                    <div class="c-value">{{ $totalEvents }}</div>
                </div>
            </td>
            <td style="padding-right: 6px;">
                <div class="card-inner card-blue">
                    <div class="c-label">Event Published</div>
                    <div class="c-value">{{ $publishedEvents }}</div>
                </div>
            </td>
            <td style="padding-right: 6px;">
                <div class="card-inner card-purple">
                    <div class="c-label">Total Pendaftar</div>
                    <div class="c-value">{{ $totalRegistered }}</div>
                </div>
            </td>
            <td>
                <div class="card-inner card-orange">
                    <div class="c-label">Total Hadir</div>
                    <div class="c-value">{{ $totalAttended }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Detail Event --}}
    <div class="section-title">Detail Event</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 24px;">No</th>
                <th>Judul Event</th>
                <th>Kategori</th>
                <th class="center">Tipe</th>
                <th class="center">Tanggal</th>
                <th>Lokasi</th>
                <th class="center">Status</th>
                <th class="center">Maks.</th>
                <th class="center">Daftar</th>
                <th class="center">Hadir</th>
                <th class="center">Batal</th>
                <th class="right">Rating</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $index => $event)
            @php
                $badgeClass = match(strtolower($event['status'])) {
                    'published' => 'badge-green',
                    'draft'     => 'badge-gray',
                    'completed' => 'badge-blue',
                    default     => 'badge-gray',
                };
            @endphp
            <tr class="{{ $index % 2 === 1 ? 'even' : '' }}">
                <td class="center">{{ $index + 1 }}</td>
                <td><strong>{{ $event['title'] }}</strong></td>
                <td>{{ $event['category'] }}</td>
                <td class="center">{{ $event['type'] }}</td>
                <td class="center">{{ $event['date'] }}</td>
                <td>{{ $event['location'] }}</td>
                <td class="center">
                    <span class="badge {{ $badgeClass }}">{{ $event['status'] }}</span>
                </td>
                <td class="center">{{ $event['max_participants'] }}</td>
                <td class="center"><strong>{{ $event['total_registered'] }}</strong></td>
                <td class="center" style="color: #059669; font-weight: 700;">{{ $event['attended_count'] }}</td>
                <td class="center" style="color: #dc2626; font-weight: 700;">{{ $event['cancelled_count'] }}</td>
                <td class="right">
                    @if($event['avg_rating'] !== '-')
                        <span style="color: #F59E0B; font-weight: 700;">&#9733; {{ $event['avg_rating'] }}</span>
                    @else
                        <span style="color: #cbd5e1;">&#8211;</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" style="text-align: center; padding: 20px; color: #94a3b8;">
                    Belum ada data event untuk periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Laporan ini dibuat secara otomatis oleh sistem SIKARA &bull; {{ $generatedAt }} &bull; Dokumen ini bersifat rahasia dan hanya untuk internal perusahaan.
    </div>

</body>
</html>
