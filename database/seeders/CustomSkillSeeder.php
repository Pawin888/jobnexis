<?php

namespace Database\Seeders;

use App\Models\CustomSkill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomSkillSeeder extends Seeder
{
    /**
     * Seed custom skills for Thai labor market context.
     */
    public function run(): void
    {
        $skills = [
            // Programming / Software
            'Java', 'Python', 'JavaScript', 'TypeScript', 'C#', 'PHP', 'HTML', 'CSS',
            'React', 'Vue.js', 'Node.js', 'Laravel', '.NET', 'MySQL', 'PostgreSQL', 'MongoDB',
            'Docker', 'GitHub Actions', 'AWS', 'REST API', 'Git', 'Microservices', 'Redis', 'Linux Administration',
            // Data / BI
            'Excel', 'SQL', 'Power BI', 'Tableau', 'Pandas', 'Data Visualization', 'ETL Pipeline Design',
            // Security
            'Network Security', 'Firewall Configuration', 'Vulnerability Assessment', 'SIEM', 'Incident Response',
            // Engineering / Manufacturing / Construction
            'AutoCAD', 'SolidWorks', 'PLC Programming', 'CNC Operation', 'Electrical Circuit Design',
            'Lean Manufacturing', 'Six Sigma', 'Quality Control', 'Quality Assurance', 'GMP', 'ISO 9001',
            'Production Planning', 'Inventory Control', 'Revit', 'SketchUp', 'BOQ (Bill of Quantity)', 'MS Project',
            // Healthcare
            'Patient Care Techniques', 'ICD-10 Coding', 'Laboratory Analysis', 'Nursing Procedures',
            // Finance / Accounting / ERP
            'Financial Analysis', 'Accounting (TFRS)', 'Tax Calculation', 'SAP', 'ERP Systems', 'Auditing',
            'Thai VAT Filing (PP30)', 'e-Withholding Tax', 'e-Tax Invoice & e-Receipt',
            // Marketing / Creative / E-commerce
            'SEO', 'Google Ads', 'Facebook Ads', 'WordPress', 'Google Analytics (GA4)',
            'Adobe Photoshop', 'Adobe Illustrator', 'Adobe Premiere Pro', 'Figma', 'UI Design', 'UX Design',
            'Salesforce', 'Shopee Seller Center', 'Lazada Seller Center', 'LINE Official Account Marketing',
            'TikTok Ads',
            // Service / Logistics / Ops
            'POS Systems', 'Warehouse Management System', 'Import/Export Documentation', 'Customs Clearance',
            'Route Planning', 'Cold Chain Management',
            // Agri / Env / Education / Hospitality / Legal
            'GIS', 'Soil Analysis', 'Irrigation System Design', 'Moodle', 'Instructional Design',
            'E-learning Development', 'Opera PMS', 'Reservation Systems', 'HACCP', 'Contract Drafting',
            'PDPA Compliance', 'Thai FDA Compliance', 'Good Agricultural Practices (GAP)', 'Carbon Footprint Reporting',
            // Thailand-specific practical skills
            'Thai Language Communication', 'Business English Communication', 'PromptPay & QR Payment Integration',
            'Thai Labor Law Compliance', 'Social Security Fund (SSF) Process', 'Government e-GP Procurement',
            'BOI Promotion Process',
        ];

        foreach ($skills as $name) {
            CustomSkill::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => null,
                ]
            );
        }
    }
}
