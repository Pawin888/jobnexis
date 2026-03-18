<?php

namespace App\Services;

use App\Models\Recruitment;
use App\Models\Resume;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class JobMatchingService
{
    private const WEIGHTS = [
        'skill_match' => 0.45,
        'skill_level' => 0.20,
        'language' => 0.10,
        'experience' => 0.10,
        'education' => 0.05,
        'location' => 0.05,
        'gender' => 0.05,
    ];

    private const SKILL_LEVEL = [
        'beginner' => 1,
        'intermediate' => 2,
        'advanced' => 3,
        'expert' => 4,
    ];

    private const LANGUAGE_LEVEL = [
        'basic' => 1,
        'conversational' => 2,
        'fluent' => 3,
        'native' => 4,
    ];

    private const EXPERIENCE_YEARS = [
        'no_experience' => 0,
        '0_1' => 1,
        '1_3' => 3,
        '3_5' => 5,
        'more_5' => 6,
    ];

    private const EDUCATION_LEVEL = [
        'any' => 0,
        'below_bachelor' => 1,
        'bachelor' => 2,
        'master' => 3,
        'doctorate' => 4,
    ];

    public function scoreRecruitmentsForResume(Collection $recruitments, ?Resume $resume): Collection
    {
        if (!$resume) {
            return $recruitments->map(function (Recruitment $rec) {
                $rec->setAttribute('matching_meta', $this->emptyMeta());
                return $rec;
            });
        }

        $profile = $this->buildResumeProfile($resume);

        return $recruitments->map(function (Recruitment $rec) use ($profile) {
            $meta = $this->buildMatchMeta($rec, $profile);
            $rec->setAttribute('matching_meta', $meta);
            return $rec;
        });
    }

    public function scoreSingleRecruitment(Recruitment $recruitment, ?Resume $resume): array
    {
        if (!$resume) {
            return $this->emptyMeta();
        }

        $profile = $this->buildResumeProfile($resume);
        return $this->buildMatchMeta($recruitment, $profile);
    }

    public function recommendRecruitments(Collection $scoredRecruitments, int $limit = 6, ?Collection $topMatches = null): Collection
    {
        $topMatches = $topMatches ?: $scoredRecruitments
            ->sortByDesc(fn (Recruitment $rec) => (int) data_get($rec, 'matching_meta.total_score', 0))
            ->take(5)
            ->values();

        $seedTypes = $topMatches
            ->flatMap(fn (Recruitment $rec) => (array) ($rec->type_values ?? []))
            ->filter()
            ->unique()
            ->values();

        $seedWorkModes = $topMatches
            ->flatMap(fn (Recruitment $rec) => (array) ($rec->work_mode_values ?? []))
            ->filter()
            ->unique()
            ->values();

        return $scoredRecruitments
            ->sortByDesc(function (Recruitment $rec) {
                $meta = $rec->matching_meta ?? [];
                $total = (float) ($meta['total_score'] ?? 0);
                $embed = (float) ($meta['embedding_similarity'] ?? 0);
                return $total * 0.75 + $embed * 25;
            })
            ->sortByDesc(function (Recruitment $rec) use ($seedTypes, $seedWorkModes) {
                $typeOverlap = collect((array) ($rec->type_values ?? []))
                    ->intersect($seedTypes)
                    ->count();
                $modeOverlap = collect((array) ($rec->work_mode_values ?? []))
                    ->intersect($seedWorkModes)
                    ->count();

                $meta = $rec->matching_meta ?? [];
                $total = (float) ($meta['total_score'] ?? 0);
                $embed = (float) ($meta['embedding_similarity'] ?? 0);

                // Category and work mode boost to keep recommendations in nearby intent clusters.
                $categoryBoost = min(1.0, ($typeOverlap * 0.2) + ($modeOverlap * 0.3));

                return ($total * 0.65) + ($embed * 25) + ($categoryBoost * 10);
            })
            ->values()
            ->take($limit);
    }

    private function buildMatchMeta(Recruitment $rec, array $profile): array
    {
        $recruitmentSkills = collect($rec->recruitmentSkills ?? []);
        $requiredLanguages = collect($rec->languages ?? []);

        $requiredSkills = $recruitmentSkills
            ->filter(fn ($row) => data_get($row, 'is_required', true))
            ->values();
        $optionalSkills = $recruitmentSkills
            ->filter(fn ($row) => !data_get($row, 'is_required', true))
            ->values();

        $requiredCoverage = $this->skillCoverage($requiredSkills, $profile['skills']);
        $optionalCoverage = $this->skillCoverage($optionalSkills, $profile['skills']);

        if ($requiredSkills->isNotEmpty() && $optionalSkills->isNotEmpty()) {
            $skillMatchScore = ($requiredCoverage * 0.7) + ($optionalCoverage * 0.3);
        } elseif ($requiredSkills->isNotEmpty()) {
            $skillMatchScore = $requiredCoverage;
        } elseif ($optionalSkills->isNotEmpty()) {
            $skillMatchScore = $optionalCoverage;
        } else {
            // งานที่ไม่ระบุ skill requirement ไม่ควรได้ 100% ในหมวดนี้
            $skillMatchScore = 0.0;
        }

        $skillLevelScore = $recruitmentSkills->isNotEmpty()
            ? $this->skillLevelScore($recruitmentSkills, $profile['skills'])
            : 0.0;

        $languageScore = $requiredLanguages->isNotEmpty()
            ? $this->languageScore($requiredLanguages, $profile['languages'])
            : 0.0;

        $experienceLevel = (string) ($rec->rc_experience_level ?? '');
        $hasExperienceRequirement = $experienceLevel !== '' && $experienceLevel !== 'unspecified';
        $experienceScore = $hasExperienceRequirement
            ? $this->experienceScore($experienceLevel, $profile['experience_years'])
            : 0.0;

        $educationLevel = (string) ($rec->rc_education_level ?? '');
        $hasEducationRequirement = $educationLevel !== '' && $educationLevel !== 'unspecified';
        $educationScore = $hasEducationRequirement
            ? $this->educationScore($educationLevel, $profile['education_level'])
            : 0.0;

        $jobLocation = (string) ($rec->rc_location_text ?? '');
        $hasLocationRequirement = trim($jobLocation) !== '';
        $locationScore = $hasLocationRequirement
            ? $this->locationScore($jobLocation, (string) ($profile['preferred_location'] ?? ''))
            : 0.0;

        $genderRequirement = (string) ($rec->rc_gender ?? '');
        $hasGenderRequirement = $genderRequirement !== '' && $genderRequirement !== 'unspecified';
        $genderScore = $hasGenderRequirement
            ? $this->genderScore($genderRequirement, (string) ($profile['gender'] ?? ''))
            : 0.0;

        $weighted = (
            ($skillMatchScore * self::WEIGHTS['skill_match']) +
            ($skillLevelScore * self::WEIGHTS['skill_level']) +
            ($languageScore * self::WEIGHTS['language']) +
            ($experienceScore * self::WEIGHTS['experience']) +
            ($educationScore * self::WEIGHTS['education']) +
            ($locationScore * self::WEIGHTS['location']) +
            ($genderScore * self::WEIGHTS['gender'])
        ) * 100;

        $embeddingSimilarity = $this->cosineSimilarity(
            $profile['vector_text'],
            $this->jobVectorText($rec)
        );

        return [
            'total_score' => (int) round($weighted),
            'breakdown' => [
                'skill_match' => (int) round($skillMatchScore * 100),
                'skill_level' => (int) round($skillLevelScore * 100),
                'language' => (int) round($languageScore * 100),
                'experience' => (int) round($experienceScore * 100),
                'education' => (int) round($educationScore * 100),
                'location' => (int) round($locationScore * 100),
                'gender' => (int) round($genderScore * 100),
            ],
            'criteria_defined' => [
                'skill_match' => $requiredSkills->isNotEmpty() || $optionalSkills->isNotEmpty(),
                'skill_level' => $recruitmentSkills->isNotEmpty(),
                'language' => $requiredLanguages->isNotEmpty(),
                'experience' => $hasExperienceRequirement,
                'education' => $hasEducationRequirement,
                'location' => $hasLocationRequirement,
                'gender' => $hasGenderRequirement,
            ],
            'required_skills_total' => $requiredSkills->count(),
            'required_skills_matched' => $this->matchedSkillCount($requiredSkills, $profile['skills']),
            'optional_skills_total' => $optionalSkills->count(),
            'optional_skills_matched' => $this->matchedSkillCount($optionalSkills, $profile['skills']),
            'embedding_similarity' => round($embeddingSimilarity, 4),
        ];
    }

    private function skillCoverage(Collection $required, array $resumeSkills): float
    {
        if ($required->isEmpty()) {
            return 0.0;
        }

        $matched = $this->matchedSkillCount($required, $resumeSkills);
        return $matched / $required->count();
    }

    private function matchedSkillCount(Collection $skills, array $resumeSkills): int
    {
        return $skills->filter(function ($row) use ($resumeSkills) {
            $skillId = (int) ($row->master_skill_id ?? 0);
            return isset($resumeSkills[$skillId]);
        })->count();
    }

    private function skillLevelScore(Collection $recruitmentSkills, array $resumeSkills): float
    {
        if ($recruitmentSkills->isEmpty()) {
            return 0.0;
        }

        $sum = 0.0;

        foreach ($recruitmentSkills as $row) {
            $skillId = (int) ($row->master_skill_id ?? 0);
            $requiredLevel = self::SKILL_LEVEL[(string) ($row->proficiency_level ?? '')] ?? 1;
            $candidateLevel = $resumeSkills[$skillId] ?? 0;

            if ($candidateLevel <= 0) {
                continue;
            }

            $sum += min(1.0, $candidateLevel / $requiredLevel);
        }

        return $sum / max(1, $recruitmentSkills->count());
    }

    private function languageScore(Collection $requiredLanguages, array $resumeLanguages): float
    {
        if ($requiredLanguages->isEmpty()) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($requiredLanguages as $lang) {
            $name = mb_strtolower(trim((string) ($lang->language ?? '')));
            $requiredLevel = self::LANGUAGE_LEVEL[(string) ($lang->proficiency ?? 'basic')] ?? 1;
            $candidateLevel = $resumeLanguages[$name] ?? 0;

            if ($candidateLevel <= 0) {
                continue;
            }

            $sum += min(1.0, $candidateLevel / $requiredLevel);
        }

        return $sum / max(1, $requiredLanguages->count());
    }

    private function experienceScore(string $requiredLevel, float $resumeYears): float
    {
        $requiredYears = self::EXPERIENCE_YEARS[$requiredLevel] ?? 0;

        if ($requiredYears <= 0) {
            return 1.0;
        }

        return min(1.0, $resumeYears / $requiredYears);
    }

    private function educationScore(string $requiredLevel, int $resumeLevel): float
    {
        $required = self::EDUCATION_LEVEL[$requiredLevel] ?? 0;

        if ($required <= 0) {
            return 1.0;
        }

        if ($resumeLevel <= 0) {
            return 0.0;
        }

        return min(1.0, $resumeLevel / $required);
    }

    private function locationScore(string $jobLocation, string $resumeLocation): float
    {
        $job = mb_strtolower(trim($jobLocation));
        $resume = mb_strtolower(trim($resumeLocation));

        if ($job === '') {
            return 1.0;
        }

        if ($resume === '') {
            return 0.0;
        }

        if (Str::contains($resume, $job) || Str::contains($job, $resume)) {
            return 1.0;
        }

        $jobTokens = collect(preg_split('/\s+/u', $job, -1, PREG_SPLIT_NO_EMPTY))->unique();
        $resumeTokens = collect(preg_split('/\s+/u', $resume, -1, PREG_SPLIT_NO_EMPTY))->unique();

        if ($jobTokens->isEmpty() || $resumeTokens->isEmpty()) {
            return 0.0;
        }

        $overlap = $jobTokens->intersect($resumeTokens)->count();
        return $overlap / max(1, $jobTokens->count());
    }

    private function genderScore(string $requiredGender, string $resumeGender): float
    {
        $required = $this->normalizeGender($requiredGender);
        $candidate = $this->normalizeGender($resumeGender);

        if ($required === 'any') {
            return 1.0;
        }

        if (!in_array($required, ['male', 'female'], true)) {
            return 0.0;
        }

        if (!in_array($candidate, ['male', 'female'], true)) {
            return 0.0;
        }

        return $required === $candidate ? 1.0 : 0.0;
    }

    private function normalizeGender(string $value): string
    {
        $normalized = mb_strtolower(trim($value));

        return match ($normalized) {
            'm', 'male', 'man', 'ชาย' => 'male',
            'f', 'female', 'woman', 'หญิง' => 'female',
            'any', 'all', 'ไม่จำกัดเพศ' => 'any',
            default => $normalized,
        };
    }

    private function buildResumeProfile(Resume $resume): array
    {
        $skills = collect($resume->resumeSkills ?? [])->mapWithKeys(function ($skill) {
            $id = (int) ($skill->skill_id ?? 0);
            $level = self::SKILL_LEVEL[(string) ($skill->proficiency_level ?? '')] ?? 1;
            return $id > 0 ? [$id => $level] : [];
        })->all();

        $languages = collect($resume->languages ?? [])->mapWithKeys(function ($lang) {
            $name = mb_strtolower(trim((string) ($lang->language ?? '')));
            $level = self::LANGUAGE_LEVEL[(string) ($lang->level ?? 'basic')] ?? 1;
            return $name !== '' ? [$name => $level] : [];
        })->all();

        $experienceYears = collect($resume->workExperiences ?? [])->sum(function ($work) {
            if (empty($work->start_date)) {
                return 0;
            }

            try {
                $start = \Illuminate\Support\Carbon::parse($work->start_date);
                $end = !empty($work->is_current)
                    ? now()
                    : (!empty($work->end_date) ? \Illuminate\Support\Carbon::parse($work->end_date) : now());
                return max(0, $start->diffInMonths($end)) / 12;
            } catch (\Throwable $e) {
                return 0;
            }
        });

        $educationLevel = collect($resume->educations ?? [])->map(function ($edu) {
            $raw = mb_strtolower((string) ($edu->education_level ?? ''));
            if ($raw === '') {
                return 0;
            }

            if (Str::contains($raw, ['doctor', 'เอก'])) {
                return 4;
            }
            if (Str::contains($raw, ['master', 'โท'])) {
                return 3;
            }
            if (Str::contains($raw, ['bachelor', 'ตรี'])) {
                return 2;
            }
            return 1;
        })->max() ?? 0;

        $vectorText = trim(implode(' ', [
            (string) ($resume->summary ?? ''),
            (string) ($resume->preferred_location ?? ''),
            collect($resume->resumeSkills ?? [])->map(fn ($s) => data_get($s, 'skill.name', ''))->implode(' '),
            collect($resume->workExperiences ?? [])->pluck('job_title')->implode(' '),
        ]));

        return [
            'skills' => $skills,
            'languages' => $languages,
            'experience_years' => (float) $experienceYears,
            'education_level' => (int) $educationLevel,
            'preferred_location' => (string) ($resume->preferred_location ?? ''),
            'gender' => (string) ($resume->gender ?? ''),
            'vector_text' => $vectorText,
        ];
    }

    private function jobVectorText(Recruitment $rec): string
    {
        return trim(implode(' ', [
            (string) ($rec->rc_title ?? ''),
            (string) ($rec->rc_description ?? ''),
            (string) ($rec->rc_requirements ?? ''),
            (string) ($rec->rc_location_text ?? ''),
            collect($rec->recruitmentSkills ?? [])->map(fn ($s) => data_get($s, 'skill.name', ''))->implode(' '),
            collect($rec->languages ?? [])->pluck('language')->implode(' '),
        ]));
    }

    private function cosineSimilarity(string $leftText, string $rightText): float
    {
        $leftVec = $this->textToVector($leftText);
        $rightVec = $this->textToVector($rightText);

        if (empty($leftVec) || empty($rightVec)) {
            return 0.0;
        }

        $dot = 0.0;
        foreach ($leftVec as $token => $weight) {
            $dot += $weight * ($rightVec[$token] ?? 0.0);
        }

        $leftNorm = sqrt(array_sum(array_map(fn ($v) => $v * $v, $leftVec)));
        $rightNorm = sqrt(array_sum(array_map(fn ($v) => $v * $v, $rightVec)));

        if ($leftNorm == 0.0 || $rightNorm == 0.0) {
            return 0.0;
        }

        return max(0.0, min(1.0, $dot / ($leftNorm * $rightNorm)));
    }

    private function textToVector(string $text): array
    {
        $normalized = mb_strtolower(strip_tags($text));
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        if (!$tokens) {
            return [];
        }

        $vector = [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) < 2) {
                continue;
            }
            $vector[$token] = ($vector[$token] ?? 0) + 1;
        }

        return $vector;
    }

    private function emptyMeta(): array
    {
        return [
            'total_score' => 0,
            'breakdown' => [
                'skill_match' => 0,
                'skill_level' => 0,
                'language' => 0,
                'experience' => 0,
                'education' => 0,
                'location' => 0,
                'gender' => 0,
            ],
            'criteria_defined' => [
                'skill_match' => false,
                'skill_level' => false,
                'language' => false,
                'experience' => false,
                'education' => false,
                'location' => false,
                'gender' => false,
            ],
            'required_skills_total' => 0,
            'required_skills_matched' => 0,
            'optional_skills_total' => 0,
            'optional_skills_matched' => 0,
            'embedding_similarity' => 0,
        ];
    }
}