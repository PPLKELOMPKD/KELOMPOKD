<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>CV {{ $user->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .header { border-bottom: 2px solid #111827; margin-bottom: 20px; padding-bottom: 10px; }
        .section { margin-bottom: 18px; }
        .label { font-weight: bold; width: 120px; display: inline-block; }
        .skill { margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $user->name }}</h1>
        <p>{{ $profile?->study_program }} - {{ $profile?->department }}</p>
        <p>{{ $user->email }} | {{ $profile?->phone }} | {{ $profile?->location }}</p>
    </div>

    <div class="section">
        <h2>Profil Singkat</h2>
        <p>{{ $profile?->bio }}</p>
    </div>

    <div class="section">
        <h2>Informasi Akademik</h2>
        <p><span class="label">NIM</span>{{ $profile?->nim }}</p>
        <p><span class="label">Program Studi</span>{{ $profile?->study_program }}</p>
        <p><span class="label">Jurusan</span>{{ $profile?->department }}</p>
        <p><span class="label">IPK</span>{{ $profile?->gpa }}</p>
    </div>

    <div class="section">
        <h2>Keterampilan</h2>
        @foreach($skills as $skill)
            <div class="skill">{{ $skill->name }} - {{ $skill->proficiency }}%</div>
        @endforeach
    </div>
</body>
</html>
