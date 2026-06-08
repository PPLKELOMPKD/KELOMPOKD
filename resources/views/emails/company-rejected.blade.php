<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun Perusahaan Ditolak</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f0f4f8; color: #334155; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: #dc2626; color: #fff; padding: 34px 42px; text-align: center; }
        .brand { font-size: 28px; font-weight: 800; letter-spacing: 2px; }
        .body { padding: 40px 42px; }
        h1 { margin: 0 0 14px; font-size: 22px; color: #1e293b; }
        p { margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #64748b; }
        .reason-box { background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 6px; padding: 18px; margin: 24px 0; color: #991b1b; }
        .btn-wrap { margin: 30px 0; text-align: center; }
        .btn { display: inline-block; background: #dc2626; color: #fff !important; text-decoration: none; padding: 15px 34px; border-radius: 8px; font-weight: 700; }
        .footer { border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 24px 42px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header"><div class="brand">SIKARA</div></div>
    <div class="body">
        <h1>Pendaftaran Akun Perusahaan Ditolak</h1>
        <p>Halo {{ $company->name }},</p>
        <p>Mohon maaf, pendaftaran perusahaan Anda ditolak oleh Administrator SIKARA.</p>
        <div class="reason-box">
            <strong>Alasan Penolakan:</strong><br>
            {{ $reason }}
        </div>
        <p>Silakan daftar kembali dengan menyertakan dokumen legalitas yang valid dan lengkap.</p>
        <div class="btn-wrap">
            <a href="{{ $url }}" class="btn">Daftar Kembali</a>
        </div>
    </div>
    <div class="footer">
        <strong>SIKARA</strong><br>
        Email ini dikirim otomatis, mohon tidak membalas email ini.
    </div>
</div>
</body>
</html>
