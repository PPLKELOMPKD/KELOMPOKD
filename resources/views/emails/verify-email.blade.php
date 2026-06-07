<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email SIKARA</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f0f4f8; color: #334155; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .header { background: #1e3a5f; color: #fff; padding: 36px 44px; text-align: center; }
        .brand { font-size: 28px; font-weight: 800; letter-spacing: 2px; }
        .sub { margin-top: 6px; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; opacity: .8; }
        .body { padding: 42px 44px; }
        h1 { margin: 0 0 14px; font-size: 22px; color: #1e293b; }
        p { margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #64748b; }
        .btn-wrap { margin: 30px 0; text-align: center; }
        .btn { display: inline-block; background: #2563eb; color: #fff !important; text-decoration: none; padding: 15px 34px; border-radius: 8px; font-weight: 700; }
        .note { background: #f8fafc; border-left: 4px solid #2563eb; padding: 14px 18px; font-size: 13px; color: #64748b; }
        .link { word-break: break-all; font-size: 12px; color: #2563eb; }
        .footer { border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 24px 44px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="brand">SIKARA</div>
        <div class="sub">Sistem Informasi Karir</div>
    </div>
    <div class="body">
        <h1>Verifikasi Email Anda</h1>
        <p>Halo {{ $user->name }}, terima kasih sudah mendaftar di SIKARA. Klik tombol berikut untuk mengaktifkan akun Anda.</p>
        <div class="btn-wrap">
            <a href="{{ $url }}" class="btn">Verifikasi Email</a>
        </div>
        <div class="note">Link verifikasi ini berlaku selama {{ $expiry }} menit.</div>
        <p style="margin-top: 22px; font-size: 13px;">Jika tombol tidak berfungsi, buka link berikut:</p>
        <a href="{{ $url }}" class="link">{{ $url }}</a>
    </div>
    <div class="footer">
        <strong>SIKARA</strong><br>
        Email ini dikirim otomatis, mohon tidak membalas email ini.
    </div>
</div>
</body>
</html>
