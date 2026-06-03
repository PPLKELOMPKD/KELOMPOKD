<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelamar Baru SIKARA</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f0f4f8; color: #334155; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: #1e3a5f; color: #fff; padding: 34px 42px; text-align: center; }
        .brand { font-size: 28px; font-weight: 800; letter-spacing: 2px; }
        .body { padding: 40px 42px; }
        h1 { margin: 0 0 14px; font-size: 22px; color: #1e293b; }
        p { margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #64748b; }
        .summary { background: #f8fafc; border-radius: 8px; padding: 18px; margin: 24px 0; }
        .summary div { margin-bottom: 8px; font-size: 14px; }
        .label { color: #64748b; font-weight: 700; }
        .btn-wrap { margin: 30px 0; text-align: center; }
        .btn { display: inline-block; background: #2563eb; color: #fff !important; text-decoration: none; padding: 15px 34px; border-radius: 8px; font-weight: 700; }
        .footer { border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 24px 42px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header"><div class="brand">SIKARA</div></div>
    <div class="body">
        <h1>Pelamar Baru Masuk</h1>
        <p>Ada mahasiswa yang baru melamar lowongan perusahaan Anda di SIKARA.</p>
        <div class="summary">
            <div><span class="label">Nama:</span> {{ $student?->name ?? 'Pelamar' }}</div>
            <div><span class="label">Email:</span> {{ $student?->email ?? '-' }}</div>
            <div><span class="label">Posisi:</span> {{ $internship?->title ?? 'Lowongan magang' }}</div>
            <div><span class="label">Tanggal:</span> {{ optional($application->created_at)->format('d M Y, H:i') }}</div>
        </div>
        <div class="btn-wrap">
            <a href="{{ $url }}" class="btn">Lihat Pelamar</a>
        </div>
    </div>
    <div class="footer">
        <strong>SIKARA</strong><br>
        Email ini dikirim otomatis, mohon tidak membalas email ini.
    </div>
</div>
</body>
</html>
