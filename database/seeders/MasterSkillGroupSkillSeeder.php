<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSkillGroupSkillSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/data/esco/broaderRelationsSkillPillar_en.csv');

        if (!file_exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file); // skip header

        while (($row = fgetcsv($file)) !== false) {

            $skillUri = $row[1] ?? null;   // conceptUri
            $groupUri = $row[4] ?? null;   // broaderUri

            if (!$skillUri || !$groupUri) {
                continue;
            }

            $skill = DB::table('master_skills')
                ->where('esco_uri', $skillUri)
                ->first();

            $group = DB::table('master_skill_groups')
                ->where('esco_uri', $groupUri)
                ->first();

            if (!$skill || !$group) {
                continue;
            }

            DB::table('master_skill_group_skill')->updateOrInsert(
                [
                    'master_skill_id' => $skill->id,
                    'master_skill_group_id' => $group->id,
                ],
                []
            );
        }

        fclose($file);
    }
}
