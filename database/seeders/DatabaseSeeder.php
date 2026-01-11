<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\MasterSkillGroupSeeder;
use Database\Seeders\MasterSkillSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // เรียก Seeder ของ Master Skill Groups ก่อน
        $this->call([
            MasterSkillGroupSeeder::class,
            MasterSkillSeeder::class,
            MasterSkillGroupSkillSeeder::class,
        ]);
    }
}
