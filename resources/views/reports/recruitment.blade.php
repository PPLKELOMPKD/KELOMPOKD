<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekrutmen - {{ $companyName }}</title>
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
            border-bottom: 3px solid #2563EB;
            margin-bottom: 18px;
            padding-bottom: 12px;
        }
        .header-logo {
            font-size: 22px;
            font-weight: 900;
            color: #2563EB;
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

        /* ── Summary Cards (pakai table HTML murni) ── */
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
        .card-table td {
            width: 20%;
            padding: 4px;
            vertical-align: top;
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
        .card-blue   { border-top: 3px solid #2563EB; }
        .card-purple { border-top: 3px solid #8B5CF6; }
        .card-green  { border-top: 3px solid #10B981; }
        .card-red    { border-top: 3px solid #EF4444; }
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
        .data-table thead tr {
            background: #1e293b;
            color: #ffffff;
        }
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
        .badge-red    { background: #fee2e2; color: #991b1b; }

        /* ── Progress bar (pakai table trick agar Dompdf aman) ── */
        .progress-outer {
            background: #e2e8f0;
            border-radius: 3px;
            height: 7px;
            width: 100%;
        }
        .progress-inner {
            height: 7px;
            border-radius: 3px;
        }

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
                <div class="report-title">Laporan Rekrutmen</div>
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
                <div class="card-inner card-blue">
                    <div class="c-label">Total Pelamar</div>
                    <div class="c-value">{{ $totalApplicants }}</div>
                </div>
            </td>
            <td style="padding-right: 6px;">
                <div class="card-inner card-purple">
                    <div class="c-label">Wawancara</div>
                    <div class="c-value">{{ $interviewApplicants }}</div>
                </div>
            </td>
            <td style="padding-right: 6px;">
                <div class="card-inner card-green">
                    <div class="c-label">Diterima</div>
                    <div class="c-value">{{ $acceptedApplicants }}</div>
                </div>
            </td>
            <td style="padding-right: 6px;">
                <div class="card-inner card-red">
                    <div class="c-label">Tidak Lolos</div>
                    <div class="c-value">{{ $rejectedApplicants }}</div>
                </div>
            </td>
            <td>
                <div class="card-inner card-orange">
                    <div class="c-label">Diproses</div>
                    <div class="c-value">{{ $processedApplicants }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Detail per Lowongan --}}
    <div class="section-title">Detail Per Lowongan Magang</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama Lowongan</th>
                <th class="center">Status</th>
                <th class="center">Kuota</th>
                <th class="center">Pelamar</th>
                <th class="center">Wawancara</th>
                <th class="center">Diterima</th>
                <th class="center">Tidak Lolos</th>
                <th class="center" style="width: 80px;">Keterisian</th>
                <th class="right" style="width: 40px;">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($internshipsData as $index => $item)
            @php
                $pct = min(100, $item['quota_percentage']);
                $barColor = $pct >= 100 ? '#10B981' : ($pct >= 50 ? '#3B82F6' : '#6366F1');
                $pctColor = $pct >= 100 ? '#059669' : '#334155';
            @endphp
            <tr class="{{ $index % 2 === 1 ? 'even' : '' }}">
                <td class="center">{{ $index + 1 }}</td>
                <td><strong>{{ $item['title'] }}</strong></td>
                <td class="center">
                    <span class="badge {{ $item['status'] === 'Aktif' ? 'badge-green' : 'badge-red' }}">
                        {{ $item['status'] }}
                    </span>
                </td>
                <td class="center">{{ $item['quota'] }}</td>
                <td class="center"><strong>{{ $item['applicants_count'] }}</strong></td>
                <td class="center">{{ $item['wawancara_count'] }}</td>
                <td class="center" style="color: #059669; font-weight: 700;">{{ $item['lolos_count'] }}</td>
                <td class="center" style="color: #dc2626; font-weight: 700;">{{ $item['tidak_lolos_count'] }}</td>
                <td class="center">
                    <div class="progress-outer">
                        <div class="progress-inner" style="width: {{ $pct }}%; background: {{ $barColor }};"></div>
                    </div>
                </td>
                <td class="right" style="font-weight: 700; color: {{ $pctColor }};">
                    {{ $item['quota_percentage'] }}%
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #94a3b8;">
                    Belum ada data lowongan magang untuk periode ini.
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
