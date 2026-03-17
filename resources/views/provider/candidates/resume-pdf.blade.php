<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Resume - {{ $resume->first_name }} {{ $resume->last_name }}</title>
    <style>
        @font-face {
            font-family: 'Noto Sans Thai PDF';
            font-style: normal;
            font-weight: 400;
            src: url('{{ resource_path('fonts/NotoSansThai-Regular.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'Noto Sans Thai PDF';
            font-style: normal;
            font-weight: 700;
            src: url('{{ resource_path('fonts/NotoSansThai-Bold.ttf') }}') format('truetype');
        }
        body { font-family: 'Noto Sans Thai PDF', DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 28px; }
        h1 { font-size: 20px; margin: 0 0 4px 0; }
        h2 { font-size: 14px; margin: 18px 0 8px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        p { margin: 4px 0; line-height: 1.5; }
        .muted { color: #6b7280; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { vertical-align: top; padding: 4px 8px 4px 0; }
        .badge { display: inline-block; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 999px; padding: 2px 8px; margin: 2px 4px 2px 0; font-size: 11px; }
        .item { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>{{ trim(($resume->first_name ?? '').' '.($resume->middle_name ?? '').' '.($resume->last_name ?? '')) }}</h1>

    <table class="grid">
        <tr>
            <td width="50%"><strong>Email:</strong> {{ $resume->email ?? $resume->user->email ?? '-' }}</td>
            <td width="50%"><strong>Phone:</strong> {{ $resume->phone ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>วันเกิด:</strong> {{ $resume->birth_date ? \Carbon\Carbon::parse($resume->birth_date)->format('d/m/Y') : '-' }}</td>
            <td><strong>เพศ:</strong> {{ ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'][$resume->gender] ?? '-' }}</td>
        </tr>
    </table>

    @if($resume->summary)
        <h2>เกี่ยวกับตัวคุณ</h2>
        <p>{{ $resume->summary }}</p>
    @endif

    @if($resume->workExperiences->count())
        <h2>ประสบการณ์ทำงาน</h2>
        @foreach($resume->workExperiences as $work)
            <div class="item">
                <p><strong>{{ $work->job_title }}</strong> - {{ $work->company_name }}</p>
                <p class="muted">{{ $work->start_date }} - {{ $work->is_current ? 'ปัจจุบัน' : ($work->end_date ?? '-') }}</p>
                @if($work->description)
                    <p>{{ $work->description }}</p>
                @endif
            </div>
        @endforeach
    @endif

    @if($resume->educations->count())
        <h2>การศึกษา</h2>
        @foreach($resume->educations as $edu)
            <div class="item">
                <p><strong>{{ $edu->field_of_study }}</strong> - {{ $edu->institution }}</p>
                <p class="muted">{{ $edu->education_level }} | {{ $edu->start_year }} - {{ $edu->end_year ?? 'ปัจจุบัน' }}</p>
            </div>
        @endforeach
    @endif

    @if($resume->resumeSkills->count())
        <h2>ทักษะ</h2>
        @foreach($resume->resumeSkills as $skill)
            <span class="badge">
                {{ $skill->skillGroup->name ?? '-' }} - {{ $skill->skill->name ?? '-' }}
                ({{ ['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง', 'expert' => 'ผู้เชี่ยวชาญ'][$skill->proficiency_level] ?? $skill->proficiency_level }})
            </span>
        @endforeach
    @endif

    @if($resume->languages->count())
        <h2>ภาษา</h2>
        @foreach($resume->languages as $lang)
            <span class="badge">
                {{ $lang->language }} ({{ ['basic' => 'พื้นฐาน', 'conversational' => 'สนทนาได้', 'fluent' => 'คล่องแคล่ว', 'native' => 'เจ้าของภาษา'][$lang->level] ?? $lang->level }})
            </span>
        @endforeach
    @endif
</body>
</html>
