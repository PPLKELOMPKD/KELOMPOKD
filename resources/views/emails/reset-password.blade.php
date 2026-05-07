<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Reset Password – SIKARA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            color: #334155;
            line-height: 1.6;
        }
        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            padding: 40px 48px;
            text-align: center;
        }
        .header .logo-text {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
        }
        .header .logo-sub {
            font-size: 12px;
            color: rgba(255,255,255,0.75);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .icon-wrap {
            margin: 32px auto 0;
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .body {
            padding: 48px;
        }
        .body h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .body p {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 16px;
        }
        .btn-wrap {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .divider {
            height: 1px;
            background: #e2e8f0;
            margin: 32px 0;
        }
        .note {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
            padding: 16px 20px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
        }
        .link-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 12px;
            color: #94a3b8;
            word-break: break-all;
            margin-bottom: 24px;
        }
        .link-box a {
            color: #2563eb;
            text-decoration: none;
        }
        .footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 28px 48px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .footer strong {
            color: #64748b;
        }
        @media (max-width: 640px) {
            .wrapper { margin: 0; border-radius: 0; }
            .header { padding: 32px 24px; }
            .body { padding: 32px 24px; }
            .footer { padding: 24px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="logo-text">SIKARA</div>
        <div class="logo-sub">Sistem Informasi Karir</div>
        <div class="icon-wrap">
            <!-- Lock icon (inline SVG for email compatibility) -->
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="5" y="11" width="14" height="10" rx="2" fill="white" fill-opacity="0.9"/>
                <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="white" stroke-width="2" stroke-linecap="round"/>
                <circle cx="12" cy="16" r="1.5" fill="#2563eb"/>
            </svg>
        </div>
    </div>

    <!-- Body -->
    <div class="body">
        <h1>Reset Password Akun Anda</h1>
        <p>
            Kami menerima permintaan untuk mereset password akun SIKARA Anda.
            Klik tombol di bawah ini untuk membuat password baru.
        </p>

        <div class="btn-wrap">
            <a href="{{ $url }}" class="btn">🔑 &nbsp; Reset Password Sekarang</a>
        </div>

        <div class="note">
            ⏱ &nbsp;<strong>Link ini akan kedaluwarsa dalam {{ $expiry }} menit.</strong>
            Segera gunakan sebelum waktu habis.
        </div>

        <p style="font-size:14px;">
            Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:
        </p>
        <div class="link-box">
            <a href="{{ $url }}">{{ $url }}</a>
        </div>

        <div class="divider"></div>

        <p style="font-size:13px; color:#94a3b8;">
            Jika Anda <strong>tidak</strong> meminta reset password, abaikan email ini — password Anda tidak akan berubah.
            Jika ada aktivitas mencurigakan, segera hubungi tim kami.
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>SIKARA</strong> – Sistem Informasi Karir</p>
        <p style="margin-top:6px;">
            © {{ date('Y') }} SIKARA. Semua hak dilindungi.
        </p>
        <p style="margin-top:12px; font-size:11px;">
            Email ini dikirim secara otomatis, mohon tidak membalas email ini.
        </p>
    </div>

</div>
</body>
</html>
