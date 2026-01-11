<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSkillGroupSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/data/esco/skillGroups_en.csv');

        if (!file_exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            $conceptUri  = $row[1] ?? null;  // conceptUri
            $name        = $row[2] ?? null;  // preferredLabel
            $description = $row[9] ?? null;  // description
            $escoCode    = $row[10] ?? null; // code

            if (!$conceptUri || !$name) {
                continue;
            }

            DB::table('master_skill_groups')->updateOrInsert(
                ['esco_uri' => $conceptUri],
                [
                    'esco_code'   => $escoCode,
                    'name'        => $name,
                    'description' => $description,
                    'source'      => 'ESCO',
                    'is_active'   => true,
                ]
            );
        }

        fclose($file);
    }
}
