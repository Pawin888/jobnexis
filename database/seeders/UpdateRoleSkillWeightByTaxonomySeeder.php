<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomRoleSkillWeight;

class UpdateRoleSkillWeightByTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'remembering' => 1,
            'understanding' => 2,
            'applying' => 3,
            'analyzing' => 4,
            'evaluating' => 5,
            'creating' => 6,
        ];
        CustomRoleSkillWeight::all()->each(function($w) use ($map) {
            $w->weight = $map[$w->taxonomy_level] ?? 1;
            $w->save();
        });
    }
}
