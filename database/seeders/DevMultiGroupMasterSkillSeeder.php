<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevMultiGroupMasterSkillSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $groups = $this->devGroups();
        $skills = $this->devSkills();

        $this->upsertGroups($groups, $now);
        $this->upsertSkills($skills, $now);

        $groupIds = DB::table('master_skill_groups')
            ->whereIn('esco_uri', array_column($groups, 'esco_uri'))
            ->pluck('id', 'esco_uri');

        $skillIds = DB::table('master_skills')
            ->whereIn('esco_uri', array_column($skills, 'esco_uri'))
            ->pluck('id', 'esco_uri');

        $relations = $this->devSkillGroupRelations();

        // ทำให้ข้อมูล deterministic เมื่อรันซ้ำ: ล้าง relation ของทักษะ dev แล้วค่อยใส่ตามชุดด้านบน
        $devSkillIds = $skillIds->values()->all();
        if (!empty($devSkillIds)) {
            DB::table('master_skill_group_skill')
                ->whereIn('master_skill_id', $devSkillIds)
                ->delete();
        }

        foreach ($relations as $relation) {
            $skillId = $skillIds[$relation['skill_uri']] ?? null;
            if (!$skillId) {
                continue;
            }

            foreach ($relation['group_uris'] as $groupUri) {
                $groupId = $groupIds[$groupUri] ?? null;
                if (!$groupId) {
                    continue;
                }

                DB::table('master_skill_group_skill')->updateOrInsert(
                    [
                        'master_skill_id' => $skillId,
                        'master_skill_group_id' => $groupId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        $this->command?->info('DevMultiGroupMasterSkillSeeder: seeded demo multi-group skills for dev environment.');
    }

    private function devGroups(): array
    {
        return [
            [
                'esco_uri' => 'urn:jobnexis:dev:skill-group:digital-skills',
                'esco_code' => 'DEV-GRP-DS',
                'name' => 'Digital Skills (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: กลุ่มทักษะดิจิทัล',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill-group:business-intelligence',
                'esco_code' => 'DEV-GRP-BI',
                'name' => 'Business Intelligence (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: กลุ่มทักษะวิเคราะห์ธุรกิจ',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill-group:management-skills',
                'esco_code' => 'DEV-GRP-MGMT',
                'name' => 'Management Skills (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: กลุ่มทักษะการจัดการ',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill-group:communication-skills',
                'esco_code' => 'DEV-GRP-COMM',
                'name' => 'Communication Skills (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: กลุ่มทักษะการสื่อสาร',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill-group:leadership-skills',
                'esco_code' => 'DEV-GRP-LEAD',
                'name' => 'Leadership Skills (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: กลุ่มทักษะภาวะผู้นำ',
            ],
        ];
    }

    private function devSkills(): array
    {
        return [
            [
                'esco_uri' => 'urn:jobnexis:dev:skill:data-analysis',
                'esco_code' => 'DEV-SKL-DATA-ANALYSIS',
                'name' => 'Data Analysis (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: ทักษะวิเคราะห์ข้อมูล (อยู่ 5 กลุ่ม)',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill:project-management',
                'esco_code' => 'DEV-SKL-PROJECT-MGMT',
                'name' => 'Project Management (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: ทักษะบริหารโครงการ (อยู่ 4 กลุ่ม)',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill:ux-writing',
                'esco_code' => 'DEV-SKL-UX-WRITING',
                'name' => 'UX Writing (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: ทักษะการเขียนเชิง UX (อยู่ 3 กลุ่ม)',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill:stakeholder-communication',
                'esco_code' => 'DEV-SKL-STAKEHOLDER-COMM',
                'name' => 'Stakeholder Communication (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: การสื่อสารกับผู้มีส่วนได้ส่วนเสีย (อยู่ 2 กลุ่ม)',
            ],
            [
                'esco_uri' => 'urn:jobnexis:dev:skill:strategic-planning',
                'esco_code' => 'DEV-SKL-STRATEGIC-PLAN',
                'name' => 'Strategic Planning (Dev Demo)',
                'description' => 'ชุดข้อมูลทดสอบสำหรับพัฒนา: การวางแผนเชิงกลยุทธ์ (อยู่ 1 กลุ่ม)',
            ],
        ];
    }

    private function devSkillGroupRelations(): array
    {
        return [
            [
                'skill_uri' => 'urn:jobnexis:dev:skill:data-analysis',
                'group_uris' => [
                    'urn:jobnexis:dev:skill-group:digital-skills',
                    'urn:jobnexis:dev:skill-group:business-intelligence',
                    'urn:jobnexis:dev:skill-group:management-skills',
                    'urn:jobnexis:dev:skill-group:communication-skills',
                    'urn:jobnexis:dev:skill-group:leadership-skills',
                ],
            ],
            [
                'skill_uri' => 'urn:jobnexis:dev:skill:project-management',
                'group_uris' => [
                    'urn:jobnexis:dev:skill-group:management-skills',
                    'urn:jobnexis:dev:skill-group:business-intelligence',
                    'urn:jobnexis:dev:skill-group:communication-skills',
                    'urn:jobnexis:dev:skill-group:leadership-skills',
                ],
            ],
            [
                'skill_uri' => 'urn:jobnexis:dev:skill:ux-writing',
                'group_uris' => [
                    'urn:jobnexis:dev:skill-group:digital-skills',
                    'urn:jobnexis:dev:skill-group:communication-skills',
                    'urn:jobnexis:dev:skill-group:business-intelligence',
                ],
            ],
            [
                'skill_uri' => 'urn:jobnexis:dev:skill:stakeholder-communication',
                'group_uris' => [
                    'urn:jobnexis:dev:skill-group:communication-skills',
                    'urn:jobnexis:dev:skill-group:leadership-skills',
                ],
            ],
            [
                'skill_uri' => 'urn:jobnexis:dev:skill:strategic-planning',
                'group_uris' => [
                    'urn:jobnexis:dev:skill-group:management-skills',
                ],
            ],
        ];
    }

    private function upsertGroups(array $groups, $now): void
    {
        foreach ($groups as $group) {
            DB::table('master_skill_groups')->updateOrInsert(
                ['esco_uri' => $group['esco_uri']],
                [
                    'esco_code' => $group['esco_code'],
                    'name' => $group['name'],
                    'description' => $group['description'],
                    'source' => 'DEV_DEMO',
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function upsertSkills(array $skills, $now): void
    {
        foreach ($skills as $skill) {
            DB::table('master_skills')->updateOrInsert(
                ['esco_uri' => $skill['esco_uri']],
                [
                    'esco_code' => $skill['esco_code'],
                    'name' => $skill['name'],
                    'description' => $skill['description'],
                    'level' => 'intermediate',
                    'source' => 'DEV_DEMO',
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
