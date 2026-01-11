<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSkillSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('database/data/esco/skills_en.csv');

        if (!file_exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            $conceptUri  = $row[1] ?? null;  // conceptUri
            $name        = $row[4] ?? null;  // preferredLabel
            $description = $row[12] ?? null; // description

            if (!$conceptUri || !$name) {
                continue;
            }

            $escoCode = basename($conceptUri);

            DB::table('master_skills')->updateOrInsert(
                ['esco_uri' => $conceptUri],
                [
                    'esco_code'   => $escoCode,
                    'name'        => $name,
                    'description' => $description,
                    'level'       => 'intermediate',
                    'source'      => 'ESCO',
                    'is_active'   => true,
                ]
            );
        }

        fclose($file);
    }
}
