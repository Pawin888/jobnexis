<?php

namespace Tests\Unit;

use App\Models\Recruitment;
use App\Models\RecruitmentLanguage;
use App\Models\RecruitmentSkill;
use App\Models\Resume;
use App\Models\ResumeEducation;
use App\Models\ResumeLanguage;
use App\Models\ResumeSkill;
use App\Models\ResumeWorkExperience;
use App\Models\MasterSkill;
use App\Services\JobMatchingService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class JobMatchingServiceTest extends TestCase
{
    public function test_score_single_recruitment_returns_breakdown_and_skill_counts(): void
    {
        $service = new JobMatchingService();

        $recruitment = new Recruitment([
            'rc_title' => 'Backend Developer',
            'rc_description' => 'Laravel PHP API',
            'rc_requirements' => 'PHP Laravel PostgreSQL',
            'rc_location_text' => 'Bangkok',
            'rc_experience_level' => '1_3',
            'rc_education_level' => 'bachelor',
            'rc_type' => 'full-time',
            'rc_work_mode' => 'onsite,hybrid',
        ]);

        $requiredSkillA = new RecruitmentSkill([
            'master_skill_id' => 10,
            'proficiency_level' => 'advanced',
            'is_required' => true,
        ]);
        $requiredSkillA->setRelation('skill', new MasterSkill(['id' => 10, 'name' => 'Laravel']));

        $requiredSkillB = new RecruitmentSkill([
            'master_skill_id' => 11,
            'proficiency_level' => 'intermediate',
            'is_required' => true,
        ]);
        $requiredSkillB->setRelation('skill', new MasterSkill(['id' => 11, 'name' => 'PostgreSQL']));

        $optionalSkill = new RecruitmentSkill([
            'master_skill_id' => 12,
            'proficiency_level' => 'beginner',
            'is_required' => false,
        ]);
        $optionalSkill->setRelation('skill', new MasterSkill(['id' => 12, 'name' => 'Redis']));

        $recruitment->setRelation('recruitmentSkills', new Collection([
            $requiredSkillA,
            $requiredSkillB,
            $optionalSkill,
        ]));

        $recruitment->setRelation('languages', new Collection([
            new RecruitmentLanguage([
                'language' => 'English',
                'proficiency' => 'conversational',
            ]),
        ]));

        $resume = new Resume([
            'summary' => 'Laravel API Developer',
            'preferred_location' => 'Bangkok',
        ]);

        $resumeSkillA = new ResumeSkill([
            'skill_id' => 10,
            'proficiency_level' => 'expert',
        ]);
        $resumeSkillA->setRelation('skill', new MasterSkill(['id' => 10, 'name' => 'Laravel']));

        $resumeSkillB = new ResumeSkill([
            'skill_id' => 12,
            'proficiency_level' => 'advanced',
        ]);
        $resumeSkillB->setRelation('skill', new MasterSkill(['id' => 12, 'name' => 'Redis']));

        $resume->setRelation('resumeSkills', new Collection([
            $resumeSkillA,
            $resumeSkillB,
        ]));

        $resume->setRelation('languages', new Collection([
            new ResumeLanguage([
                'language' => 'English',
                'level' => 'fluent',
            ]),
        ]));

        $resume->setRelation('workExperiences', new Collection([
            new ResumeWorkExperience([
                'start_date' => now()->subYears(2)->toDateString(),
                'end_date' => now()->toDateString(),
                'is_current' => false,
                'job_title' => 'PHP Developer',
            ]),
        ]));

        $resume->setRelation('educations', new Collection([
            new ResumeEducation([
                'education_level' => 'bachelor',
            ]),
        ]));

        $meta = $service->scoreSingleRecruitment($recruitment, $resume);

        $this->assertArrayHasKey('total_score', $meta);
        $this->assertArrayHasKey('breakdown', $meta);
        $this->assertArrayHasKey('embedding_similarity', $meta);

        $this->assertSame(2, $meta['required_skills_total']);
        $this->assertSame(1, $meta['required_skills_matched']);
        $this->assertSame(1, $meta['optional_skills_total']);
        $this->assertSame(1, $meta['optional_skills_matched']);

        $this->assertGreaterThanOrEqual(0, $meta['total_score']);
        $this->assertLessThanOrEqual(100, $meta['total_score']);

        $this->assertSame(100, $meta['breakdown']['language']);
        $this->assertSame(100, $meta['breakdown']['location']);
        $this->assertTrue($meta['criteria_defined']['skill_match']);
        $this->assertTrue($meta['criteria_defined']['language']);
        $this->assertTrue($meta['criteria_defined']['experience']);
        $this->assertTrue($meta['criteria_defined']['education']);
        $this->assertTrue($meta['criteria_defined']['location']);
    }

    public function test_scoring_without_resume_returns_empty_meta(): void
    {
        $service = new JobMatchingService();

        $recruitment = new Recruitment([
            'rc_title' => 'Frontend Developer',
        ]);
        $recruitment->setRelation('recruitmentSkills', new Collection());
        $recruitment->setRelation('languages', new Collection());

        $meta = $service->scoreSingleRecruitment($recruitment, null);

        $this->assertSame(0, $meta['total_score']);
        $this->assertSame(0, $meta['required_skills_total']);
        $this->assertSame(0, $meta['optional_skills_total']);
        $this->assertSame(0, $meta['embedding_similarity']);
        $this->assertFalse($meta['criteria_defined']['skill_match']);
    }

    public function test_job_without_requirements_does_not_get_perfect_breakdown_scores(): void
    {
        $service = new JobMatchingService();

        $recruitment = new Recruitment([
            'rc_title' => 'General Position',
            'rc_experience_level' => 'unspecified',
            'rc_education_level' => 'unspecified',
            'rc_gender' => 'unspecified',
            'rc_location_text' => '',
        ]);
        $recruitment->setRelation('recruitmentSkills', new Collection());
        $recruitment->setRelation('languages', new Collection());

        $resume = new Resume([
            'summary' => 'Any profile',
            'preferred_location' => 'Bangkok',
        ]);
        $resume->setRelation('resumeSkills', new Collection());
        $resume->setRelation('languages', new Collection());
        $resume->setRelation('workExperiences', new Collection());
        $resume->setRelation('educations', new Collection());

        $meta = $service->scoreSingleRecruitment($recruitment, $resume);

        $this->assertSame(0, $meta['total_score']);
        $this->assertSame(0, $meta['breakdown']['skill_match']);
        $this->assertSame(0, $meta['breakdown']['skill_level']);
        $this->assertSame(0, $meta['breakdown']['language']);
        $this->assertSame(0, $meta['breakdown']['experience']);
        $this->assertSame(0, $meta['breakdown']['education']);
        $this->assertSame(0, $meta['breakdown']['location']);
        $this->assertSame(0, $meta['breakdown']['gender']);
        $this->assertFalse($meta['criteria_defined']['skill_match']);
        $this->assertFalse($meta['criteria_defined']['skill_level']);
        $this->assertFalse($meta['criteria_defined']['language']);
        $this->assertFalse($meta['criteria_defined']['experience']);
        $this->assertFalse($meta['criteria_defined']['education']);
        $this->assertFalse($meta['criteria_defined']['location']);
        $this->assertFalse($meta['criteria_defined']['gender']);
    }

    public function test_unrestricted_requirements_are_defined_and_full_match(): void
    {
        $service = new JobMatchingService();

        $recruitment = new Recruitment([
            'rc_title' => 'Open Criteria Position',
            'rc_experience_level' => 'no_experience',
            'rc_education_level' => 'any',
            'rc_gender' => 'any',
            'rc_location_text' => '',
        ]);
        $recruitment->setRelation('recruitmentSkills', new Collection());
        $recruitment->setRelation('languages', new Collection());

        $resume = new Resume([
            'summary' => 'General profile',
            'preferred_location' => 'Bangkok',
            'gender' => 'female',
        ]);
        $resume->setRelation('resumeSkills', new Collection());
        $resume->setRelation('languages', new Collection());
        $resume->setRelation('workExperiences', new Collection());
        $resume->setRelation('educations', new Collection());

        $meta = $service->scoreSingleRecruitment($recruitment, $resume);

        $this->assertSame(100, $meta['breakdown']['experience']);
        $this->assertSame(100, $meta['breakdown']['education']);
        $this->assertSame(100, $meta['breakdown']['gender']);
        $this->assertTrue($meta['criteria_defined']['experience']);
        $this->assertTrue($meta['criteria_defined']['education']);
        $this->assertTrue($meta['criteria_defined']['gender']);
    }

    public function test_specific_gender_requirement_matches_only_same_gender(): void
    {
        $service = new JobMatchingService();

        $recruitment = new Recruitment([
            'rc_title' => 'Female-only Role',
            'rc_gender' => 'female',
            'rc_experience_level' => 'unspecified',
            'rc_education_level' => 'unspecified',
            'rc_location_text' => '',
        ]);
        $recruitment->setRelation('recruitmentSkills', new Collection());
        $recruitment->setRelation('languages', new Collection());

        $resumeFemale = new Resume([
            'summary' => 'Profile A',
            'gender' => 'female',
        ]);
        $resumeFemale->setRelation('resumeSkills', new Collection());
        $resumeFemale->setRelation('languages', new Collection());
        $resumeFemale->setRelation('workExperiences', new Collection());
        $resumeFemale->setRelation('educations', new Collection());

        $resumeMale = new Resume([
            'summary' => 'Profile B',
            'gender' => 'male',
        ]);
        $resumeMale->setRelation('resumeSkills', new Collection());
        $resumeMale->setRelation('languages', new Collection());
        $resumeMale->setRelation('workExperiences', new Collection());
        $resumeMale->setRelation('educations', new Collection());

        $metaFemale = $service->scoreSingleRecruitment($recruitment, $resumeFemale);
        $metaMale = $service->scoreSingleRecruitment($recruitment, $resumeMale);

        $this->assertSame(100, $metaFemale['breakdown']['gender']);
        $this->assertSame(0, $metaMale['breakdown']['gender']);
        $this->assertTrue($metaFemale['criteria_defined']['gender']);
        $this->assertTrue($metaMale['criteria_defined']['gender']);
    }

    public function test_recommend_recruitments_respects_limit_and_returns_collection(): void
    {
        $service = new JobMatchingService();

        $recA = new Recruitment([
            'rc_id' => 1,
            'rc_type' => 'full-time',
            'rc_work_mode' => 'onsite',
        ]);
        $recA->setAttribute('matching_meta', [
            'total_score' => 82,
            'embedding_similarity' => 0.75,
        ]);

        $recB = new Recruitment([
            'rc_id' => 2,
            'rc_type' => 'intern',
            'rc_work_mode' => 'remote',
        ]);
        $recB->setAttribute('matching_meta', [
            'total_score' => 65,
            'embedding_similarity' => 0.40,
        ]);

        $recC = new Recruitment([
            'rc_id' => 3,
            'rc_type' => 'full-time',
            'rc_work_mode' => 'hybrid',
        ]);
        $recC->setAttribute('matching_meta', [
            'total_score' => 70,
            'embedding_similarity' => 0.60,
        ]);

        $scored = collect([$recA, $recB, $recC]);
        $top = collect([$recA]);

        $recommended = $service->recommendRecruitments($scored, 2, $top);

        $this->assertInstanceOf(Collection::class, $recommended);
        $this->assertCount(2, $recommended);
        $this->assertContainsOnlyInstancesOf(Recruitment::class, $recommended->all());
    }
}
