<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Penyelesaian - {{ $course->title }}</title>
    <style>
        @page {
            margin: 0;
            size: a4 landscape;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #1e293b;
        }

        .cert-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 30px;
            box-sizing: border-box;
            background: #fdfdfd;
        }

        /* Decorative Corners */
        .corner {
            position: absolute;
            width: 150px;
            height: 150px;
            border: 15px solid #1e1b4b;
            z-index: 10;
        }

        .top-left { top: 30px; left: 30px; border-right: none; border-bottom: none; }
        .top-right { top: 30px; right: 30px; border-left: none; border-bottom: none; }
        .bottom-left { bottom: 30px; left: 30px; border-right: none; border-top: none; }
        .bottom-right { bottom: 30px; right: 30px; border-left: none; border-top: none; }

        .main-border {
            border: 2px solid #c5a059;
            height: 100%;
            padding: 40px;
            box-sizing: border-box;
            position: relative;
            background: white;
        }

        .inner-content {
            border: 1px solid #e2e8f0;
            height: 100%;
            padding: 50px;
            box-sizing: border-box;
            text-align: center;
            background-image: radial-gradient(circle at center, #fcfcfc 0%, #ffffff 100%);
        }

        .brand-header {
            margin-bottom: 30px;
        }

        .sikara-logo {
            font-size: 24px;
            font-weight: 900;
            color: #2563eb;
            letter-spacing: 5px;
            text-transform: uppercase;
        }

        .cert-type {
            font-size: 64px;
            font-weight: 800;
            color: #1e1b4b;
            margin: 15px 0;
            letter-spacing: 10px;
            text-transform: uppercase;
        }

        .cert-subtitle {
            font-size: 20px;
            color: #c5a059;
            font-weight: 600;
            letter-spacing: 3px;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .presented-to {
            font-style: italic;
            font-size: 18px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .student-name {
            font-size: 52px;
            font-weight: 700;
            color: #1e1b4b;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 3px double #c5a059;
            display: inline-block;
            min-width: 60%;
        }

        .achievement-text {
            font-size: 18px;
            line-height: 1.8;
            color: #475569;
            max-width: 85%;
            margin: 0 auto 30px auto;
        }

        .course-name {
            font-size: 32px;
            font-weight: 800;
            color: #2563eb;
            display: block;
            margin-top: 10px;
        }

        .grade-badge {
            display: inline-block;
            background: #1e1b4b;
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 20px;
            margin-top: 20px;
            border: 2px solid #c5a059;
        }

        .signature-section {
            margin-top: 60px;
            width: 100%;
        }

        .sig-box {
            float: left;
            width: 33.33%;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #1e1b4b;
            width: 180px;
            margin: 0 auto 10px auto;
        }

        .sig-name {
            font-weight: 700;
            color: #1e1b4b;
            font-size: 16px;
        }

        .sig-title {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cert-id-box {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #94a3b8;
            font-family: monospace;
        }

        .medal-gold {
            position: absolute;
            bottom: 60px;
            right: 60px;
            width: 130px;
            height: 130px;
            background: #c5a059;
            border-radius: 50%;
            border: 5px double white;
            box-shadow: 0 0 0 5px #c5a059;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            font-weight: 900;
            font-size: 14px;
            transform: rotate(-10deg);
        }

        .medal-inner {
            border: 1px solid rgba(255,255,255,0.5);
            width: 110px;
            height: 110px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="cert-wrapper">
        <div class="corner top-left"></div>
        <div class="corner top-right"></div>
        <div class="corner bottom-left"></div>
        <div class="corner bottom-right"></div>

        <div class="main-border">
            <div class="inner-content">
                <div class="brand-header">
                    <div class="sikara-logo">SIKARA LMS</div>
                </div>

                <div class="cert-type">SERTIFIKAT</div>
                <div class="cert-subtitle">Penyelesaian Program Pelatihan</div>

                <div class="presented-to">Sertifikat ini diberikan dengan bangga kepada:</div>
                <div class="student-name">{{ $student->name }}</div>

                <div class="achievement-text">
                    Atas dedikasi dan keberhasilannya dalam menyelesaikan program pelatihan profesional yang diselenggarakan oleh <strong>{{ $provider_name }}</strong> melalui platform SIKARA dengan hasil yang memuaskan pada bidang:
                    <span class="course-name">{{ $course->title }}</span>
                </div>

                <div class="grade-badge">
                    NILAI AKHIR: {{ $grade }} / 100
                </div>

                <div class="signature-section clearfix">
                    <div class="sig-box">
                        <div class="sig-title">Tanggal Terbit</div>
                        <div class="sig-name" style="margin-top: 10px;">{{ $date }}</div>
                    </div>
                    <div class="sig-box">
                        <div style="height: 40px;"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">SIKARA ACADEMY</div>
                        <div class="sig-title">Sistem Verifikasi Pusat</div>
                    </div>
                    <div class="sig-box">
                        <div style="height: 40px;"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ $provider_name }}</div>
                        <div class="sig-title">Penyelenggara Program</div>
                    </div>
                </div>

                <div class="cert-id-box">
                    Verify this certificate at: sikara.id/verify/{{ $certificate_no }}<br>
                    ID: {{ $certificate_no }}
                </div>

                <div class="medal-gold">
                    <div class="medal-inner">
                        OFFICIAL<br>GRADUATE<br>2026
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>