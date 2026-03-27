<?php

namespace Database\Seeders;

use App\Models\CustomJobRole;
use App\Models\CustomRoleSkillWeight;
use App\Models\CustomSkill;
use Illuminate\Database\Seeder;

class CustomRoleSkillTaxonomySeeder extends Seeder
{
    private array $skillsByName = [];

    /**
     * Seed role-skill taxonomy mappings.
     */
    public function run(): void
    {
        $this->skillsByName = CustomSkill::query()->pluck('id', 'name')->toArray();

        $groupCore = [
            'Engineering & Technology' => [
                ['Git', 'understanding', true],
                ['REST API', 'applying', true],
                ['SQL', 'applying', true],
                ['Docker', 'understanding', false],
                ['Thai Language Communication', 'understanding', false],
                ['Business English Communication', 'understanding', false],
            ],
            'Business & Management' => [
                ['Excel', 'applying', true],
                ['Power BI', 'understanding', false],
                ['Project Management', 'applying', false],
                ['Thai Language Communication', 'understanding', true],
                ['Business English Communication', 'understanding', false],
            ],
            'Finance & Accounting' => [
                ['Excel', 'applying', true],
                ['Accounting (TFRS)', 'analyzing', true],
                ['Tax Calculation', 'analyzing', true],
                ['Thai VAT Filing (PP30)', 'applying', true],
                ['Auditing', 'understanding', false],
            ],
            'Sales, Marketing & E-commerce' => [
                ['SEO', 'understanding', false],
                ['Google Ads', 'applying', false],
                ['Facebook Ads', 'applying', false],
                ['Google Analytics (GA4)', 'analyzing', true],
                ['Thai Language Communication', 'understanding', true],
            ],
            'Human Resources' => [
                ['Thai Labor Law Compliance', 'analyzing', true],
                ['Social Security Fund (SSF) Process', 'applying', true],
                ['Excel', 'applying', true],
                ['Thai Language Communication', 'understanding', true],
            ],
            'Healthcare & Medical' => [
                ['Patient Care Techniques', 'applying', true],
                ['Thai Language Communication', 'understanding', true],
                ['ICD-10 Coding', 'understanding', false],
            ],
            'Education & Research' => [
                ['Instructional Design', 'analyzing', true],
                ['Moodle', 'applying', false],
                ['E-learning Development', 'applying', false],
                ['Thai Language Communication', 'understanding', true],
            ],
            'Legal & Compliance' => [
                ['Contract Drafting', 'analyzing', true],
                ['PDPA Compliance', 'analyzing', true],
                ['Thai Labor Law Compliance', 'understanding', false],
            ],
            'Creative, Media & Design' => [
                ['Adobe Photoshop', 'applying', false],
                ['Adobe Illustrator', 'applying', false],
                ['Figma', 'applying', true],
                ['UI Design', 'analyzing', false],
                ['UX Design', 'analyzing', false],
            ],
            'Service & Hospitality' => [
                ['Reservation Systems', 'applying', false],
                ['Opera PMS', 'applying', false],
                ['HACCP', 'understanding', false],
                ['Thai Language Communication', 'understanding', true],
            ],
            'Logistics & Supply Chain' => [
                ['Warehouse Management System', 'applying', true],
                ['Import/Export Documentation', 'applying', true],
                ['Customs Clearance', 'understanding', false],
                ['Inventory Control', 'applying', true],
            ],
            'Transportation & Mobility' => [
                ['Route Planning', 'applying', true],
                ['Thai Language Communication', 'understanding', true],
                ['Safety Management', 'understanding', false],
            ],
            'Manufacturing & Production' => [
                ['Lean Manufacturing', 'analyzing', true],
                ['Six Sigma', 'understanding', false],
                ['Quality Control', 'applying', true],
                ['Quality Assurance', 'applying', true],
                ['ISO 9001', 'understanding', true],
            ],
            'Construction & Real Estate' => [
                ['AutoCAD', 'applying', true],
                ['BOQ (Bill of Quantity)', 'analyzing', true],
                ['MS Project', 'applying', false],
                ['Revit', 'understanding', false],
            ],
            'Government & Public Sector' => [
                ['Government e-GP Procurement', 'understanding', false],
                ['Policy Analysis', 'analyzing', false],
                ['Thai Language Communication', 'understanding', true],
            ],
            'Security & Safety' => [
                ['Risk Assessment', 'analyzing', true],
                ['Incident Response', 'applying', true],
                ['Network Security', 'understanding', false],
            ],
            'Science & Laboratory' => [
                ['Laboratory Analysis', 'applying', true],
                ['Quality Control', 'applying', true],
                ['GMP', 'understanding', false],
            ],
            'Environment & Energy' => [
                ['GIS', 'applying', false],
                ['Carbon Footprint Reporting', 'analyzing', true],
                ['Environmental Impact Assessment', 'analyzing', false],
            ],
            'Freelance & Entrepreneurship' => [
                ['Thai Language Communication', 'understanding', true],
                ['Business English Communication', 'understanding', false],
                ['Google Analytics (GA4)', 'understanding', false],
                ['PromptPay & QR Payment Integration', 'applying', false],
            ],
            'Labor & General Work' => [
                ['Safety Management', 'understanding', true],
                ['Thai Language Communication', 'remembering', true],
                ['Quality Control', 'remembering', false],
            ],
            'Agriculture & Natural Resources' => [
                ['Soil Analysis', 'applying', true],
                ['Irrigation System Design', 'understanding', false],
                ['Good Agricultural Practices (GAP)', 'understanding', true],
                ['GIS', 'understanding', false],
                ['Thai Language Communication', 'understanding', true],
            ],
        ];

        $roleSpecific = [
            'Software Engineer' => [
                ['Java', 'applying', false], ['Python', 'applying', false], ['SQL', 'applying', true],
                ['REST API', 'analyzing', true], ['Docker', 'understanding', false],
            ],
            'Full Stack Developer' => [
                ['JavaScript', 'creating', true], ['TypeScript', 'analyzing', true], ['React', 'applying', true],
                ['Node.js', 'applying', true], ['MySQL', 'applying', true],
            ],
            'Frontend Developer' => [
                ['HTML', 'creating', true], ['CSS', 'creating', true], ['JavaScript', 'creating', true],
                ['React', 'analyzing', true], ['UI Design', 'analyzing', false],
            ],
            'Backend Developer' => [
                ['Node.js', 'applying', true], ['Laravel', 'applying', false], ['REST API', 'creating', true],
                ['PostgreSQL', 'applying', true], ['Docker', 'understanding', false],
            ],
            'Data Scientist' => [
                ['Python', 'creating', true], ['Pandas', 'creating', true], ['SQL', 'analyzing', true], ['Tableau', 'applying', false],
            ],
            'Data Engineer' => [
                ['Python', 'applying', true], ['SQL', 'creating', true], ['ETL Pipeline Design', 'creating', true], ['PostgreSQL', 'analyzing', false],
            ],
            'AI Engineer' => [
                ['Python', 'creating', true], ['Machine Learning Model Deployment', 'creating', true], ['AWS', 'applying', false],
            ],
            'Machine Learning Engineer' => [
                ['Python', 'creating', true], ['Pandas', 'analyzing', true], ['AWS', 'applying', false],
            ],
            'Cybersecurity Analyst' => [
                ['Network Security', 'creating', true], ['Firewall Configuration', 'analyzing', true], ['Vulnerability Assessment', 'creating', true], ['SIEM', 'applying', false],
            ],
            'Cloud Engineer' => [
                ['AWS', 'creating', true], ['Docker', 'analyzing', true], ['GitHub Actions', 'applying', false],
            ],
            'QA Engineer' => [
                ['Quality Assurance', 'creating', true], ['REST API', 'analyzing', false], ['SQL', 'understanding', false],
            ],
            'Business Analyst' => [
                ['Excel', 'creating', true], ['Power BI', 'analyzing', true], ['SQL', 'understanding', false],
            ],
            'Project Manager' => [
                ['MS Project', 'analyzing', true], ['Excel', 'applying', true], ['Risk Assessment', 'analyzing', false],
            ],
            'Product Manager' => [
                ['Google Analytics (GA4)', 'analyzing', false], ['UX Design', 'understanding', false], ['SQL', 'understanding', false],
            ],
            'Accountant' => [
                ['Accounting (TFRS)', 'creating', true], ['Tax Calculation', 'analyzing', true], ['Thai VAT Filing (PP30)', 'creating', true], ['Excel', 'creating', true],
            ],
            'Financial Analyst' => [
                ['Financial Analysis', 'creating', true], ['Excel', 'creating', true], ['Power BI', 'analyzing', false], ['Tableau', 'understanding', false],
            ],
            'Auditor' => [
                ['Auditing', 'creating', true], ['Accounting (TFRS)', 'analyzing', true], ['Excel', 'analyzing', false],
            ],
            'Tax Consultant' => [
                ['Tax Calculation', 'creating', true], ['Thai VAT Filing (PP30)', 'creating', true], ['e-Withholding Tax', 'analyzing', true],
            ],
            'Digital Marketing Specialist' => [
                ['Google Ads', 'creating', true], ['Facebook Ads', 'creating', true], ['Google Analytics (GA4)', 'analyzing', true],
            ],
            'SEO Specialist' => [
                ['SEO', 'creating', true], ['Google Analytics (GA4)', 'analyzing', true], ['WordPress', 'applying', false],
            ],
            'SEM Specialist' => [
                ['Google Ads', 'creating', true], ['Google Analytics (GA4)', 'analyzing', true],
            ],
            'E-commerce Manager' => [
                ['Shopee Seller Center', 'creating', true], ['Lazada Seller Center', 'creating', true], ['Google Analytics (GA4)', 'analyzing', true], ['Inventory Control', 'analyzing', false],
            ],
            'Recruiter' => [
                ['Thai Labor Law Compliance', 'understanding', true], ['Thai Language Communication', 'creating', true],
            ],
            'Talent Acquisition' => [
                ['Thai Labor Law Compliance', 'understanding', true], ['Business English Communication', 'analyzing', false],
            ],
            'Doctor' => [
                ['Patient Care Techniques', 'creating', true], ['ICD-10 Coding', 'analyzing', true],
            ],
            'Nurse' => [
                ['Nursing Procedures', 'creating', true], ['Patient Care Techniques', 'creating', true],
            ],
            'Pharmacist' => [
                ['Patient Care Techniques', 'analyzing', true], ['Thai FDA Compliance', 'understanding', true],
            ],
            'Medical Technologist' => [
                ['Laboratory Analysis', 'creating', true], ['Quality Control', 'analyzing', true],
            ],
            'Teacher' => [
                ['Instructional Design', 'analyzing', true], ['Moodle', 'applying', false],
            ],
            'Researcher' => [
                ['Laboratory Analysis', 'analyzing', false], ['Pandas', 'applying', false], ['Tableau', 'understanding', false],
            ],
            'Lawyer' => [
                ['Contract Drafting', 'creating', true], ['PDPA Compliance', 'analyzing', true],
            ],
            'Compliance Officer' => [
                ['PDPA Compliance', 'creating', true], ['Thai Labor Law Compliance', 'analyzing', false],
            ],
            'Graphic Designer' => [
                ['Adobe Photoshop', 'creating', true], ['Adobe Illustrator', 'creating', true],
            ],
            'UI Designer' => [
                ['UI Design', 'creating', true], ['Figma', 'creating', true],
            ],
            'UX Designer' => [
                ['UX Design', 'creating', true], ['Figma', 'analyzing', true],
            ],
            'Video Editor' => [
                ['Adobe Premiere Pro', 'creating', true],
            ],
            'Hotel Manager' => [
                ['Opera PMS', 'analyzing', true], ['Reservation Systems', 'analyzing', true],
            ],
            'Chef' => [
                ['HACCP', 'creating', true],
            ],
            'Supply Chain Manager' => [
                ['Inventory Control', 'creating', true], ['Warehouse Management System', 'analyzing', true],
            ],
            'Logistics Coordinator' => [
                ['Import/Export Documentation', 'applying', true], ['Customs Clearance', 'understanding', false],
            ],
            'Procurement Officer' => [
                ['ERP Systems', 'analyzing', false], ['Government e-GP Procurement', 'understanding', false],
            ],
            'Driver' => [
                ['Route Planning', 'applying', true],
            ],
            'Transport Manager' => [
                ['Route Planning', 'analyzing', true], ['Inventory Control', 'understanding', false],
            ],
            'Production Engineer' => [
                ['Lean Manufacturing', 'creating', true], ['Production Planning', 'creating', true], ['ISO 9001', 'analyzing', true],
            ],
            'Quality Control' => [
                ['Quality Control', 'creating', true], ['GMP', 'analyzing', true],
            ],
            'Quality Assurance' => [
                ['Quality Assurance', 'creating', true], ['ISO 9001', 'creating', true],
            ],
            'Maintenance Engineer' => [
                ['Electrical Circuit Design', 'analyzing', true], ['PLC Programming', 'applying', true],
            ],
            'Civil Engineer' => [
                ['AutoCAD', 'creating', true], ['BOQ (Bill of Quantity)', 'analyzing', true], ['MS Project', 'understanding', false],
            ],
            'Architect' => [
                ['AutoCAD', 'creating', true], ['Revit', 'creating', true], ['SketchUp', 'analyzing', true],
            ],
            'Site Engineer' => [
                ['AutoCAD', 'applying', true], ['MS Project', 'applying', false], ['BOQ (Bill of Quantity)', 'understanding', true],
            ],
            'Government Officer' => [
                ['Government e-GP Procurement', 'understanding', false], ['Thai Language Communication', 'creating', true],
            ],
            'Policy Analyst' => [
                ['Policy Analysis', 'creating', true], ['Data Visualization', 'understanding', false],
            ],
            'Security Guard' => [
                ['Safety Management', 'applying', true], ['Incident Response', 'applying', true],
            ],
            'Safety Officer' => [
                ['Safety Management', 'creating', true], ['Risk Assessment', 'creating', true],
            ],
            'Lab Technician' => [
                ['Laboratory Analysis', 'creating', true], ['Quality Control', 'analyzing', true],
            ],
            'Scientist' => [
                ['Laboratory Analysis', 'creating', true], ['Data Visualization', 'analyzing', false],
            ],
            'Environmental Engineer' => [
                ['GIS', 'applying', false], ['Carbon Footprint Reporting', 'creating', true], ['Environmental Impact Assessment', 'analyzing', true],
            ],
            'Sustainability Officer' => [
                ['Carbon Footprint Reporting', 'creating', true], ['PDPA Compliance', 'understanding', false],
            ],
            'Freelancer' => [
                ['Thai Language Communication', 'creating', true], ['PromptPay & QR Payment Integration', 'applying', false],
            ],
            'Startup Founder' => [
                ['Business English Communication', 'analyzing', false], ['Financial Analysis', 'analyzing', false], ['Google Analytics (GA4)', 'understanding', false],
            ],
            'Online Seller' => [
                ['Shopee Seller Center', 'creating', true], ['Lazada Seller Center', 'creating', true], ['PromptPay & QR Payment Integration', 'applying', true],
            ],
            'Factory Worker' => [
                ['Safety Management', 'understanding', true], ['Quality Control', 'remembering', false],
            ],
            'Construction Worker' => [
                ['Safety Management', 'understanding', true],
            ],
            'Farmer' => [
                ['Soil Analysis', 'applying', true], ['Good Agricultural Practices (GAP)', 'analyzing', true],
            ],
            'Livestock Farmer' => [
                ['Good Agricultural Practices (GAP)', 'analyzing', true], ['HACCP', 'understanding', false],
            ],
            'Aquaculture Farmer' => [
                ['Water Quality Monitoring', 'analyzing', true], ['Good Agricultural Practices (GAP)', 'understanding', true],
            ],
            'Agricultural Technician' => [
                ['Soil Analysis', 'applying', true], ['Irrigation System Design', 'understanding', false],
            ],
            'Agronomist' => [
                ['Soil Analysis', 'creating', true], ['GIS', 'applying', false],
            ],
            'Agricultural Engineer' => [
                ['Irrigation System Design', 'creating', true], ['AutoCAD', 'applying', false],
            ],
            'Farm Manager' => [
                ['Production Planning', 'analyzing', true], ['Inventory Control', 'analyzing', true],
            ],
            'Forestry Officer' => [
                ['GIS', 'applying', true], ['Environmental Impact Assessment', 'analyzing', true],
            ],
            'Environmental Officer' => [
                ['Environmental Impact Assessment', 'creating', true], ['Carbon Footprint Reporting', 'analyzing', false],
            ],
            'Irrigation Engineer' => [
                ['Irrigation System Design', 'creating', true], ['AutoCAD', 'applying', false],
            ],
            'Agri-business Manager' => [
                ['Financial Analysis', 'analyzing', true], ['Supply Chain Planning', 'analyzing', false],
            ],
            'Agri Sales Representative' => [
                ['Thai Language Communication', 'creating', true], ['Salesforce', 'understanding', false],
            ],
            'Agri Supply Chain Officer' => [
                ['Supply Chain Planning', 'analyzing', true], ['Inventory Control', 'analyzing', true],
            ],
            'Food Processing Specialist' => [
                ['HACCP', 'creating', true], ['GMP', 'creating', true], ['Quality Control', 'analyzing', true],
            ],
        ];

        $fallbackSkillAdds = [
            ['Project Management', 'analyzing', false],
            ['Policy Analysis', 'analyzing', false],
            ['Risk Assessment', 'analyzing', false],
            ['Safety Management', 'understanding', false],
            ['Environmental Impact Assessment', 'analyzing', false],
            ['Supply Chain Planning', 'analyzing', false],
            ['Machine Learning Model Deployment', 'applying', false],
            ['Water Quality Monitoring', 'analyzing', false],
        ];

        foreach ($fallbackSkillAdds as [$skillName]) {
            $this->ensureSkillExists($skillName);
        }

        $roles = CustomJobRole::query()->with('group')->get();

        foreach ($roles as $role) {
            $groupName = $role->group?->name;

            if ($groupName && isset($groupCore[$groupName])) {
                foreach ($groupCore[$groupName] as [$skill, $level, $required]) {
                    $this->syncRoleSkill((int) $role->id, $skill, $level, $required);
                }
            }

            if (isset($roleSpecific[$role->name])) {
                foreach ($roleSpecific[$role->name] as [$skill, $level, $required]) {
                    $this->syncRoleSkill((int) $role->id, $skill, $level, $required);
                }
            }
        }
    }

    private function ensureSkillExists(string $skillName): int
    {
        if (isset($this->skillsByName[$skillName])) {
            return (int) $this->skillsByName[$skillName];
        }

        $skill = CustomSkill::query()->firstOrCreate(
            ['name' => $skillName],
            [
                'slug' => str($skillName)->slug(),
                'description' => null,
            ]
        );

        $this->skillsByName[$skillName] = (int) $skill->id;
        return (int) $skill->id;
    }

    private function syncRoleSkill(int $roleId, string $skillName, string $taxonomyLevel, bool $required): void
    {
        $skillId = $this->ensureSkillExists($skillName);

        CustomRoleSkillWeight::query()->updateOrCreate(
            [
                'custom_job_role_id' => $roleId,
                'custom_skill_id' => $skillId,
            ],
            [
                'taxonomy_level' => $taxonomyLevel,
            ]
        );
    }
}
