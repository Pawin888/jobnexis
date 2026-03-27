<?php

namespace Database\Seeders;

use App\Models\CustomJobGroup;
use App\Models\CustomJobRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomJobRoleSeeder extends Seeder
{
    /**
     * Seed custom job roles grouped by custom job groups.
     */
    public function run(): void
    {
        $rolesByGroup = [
            'Agriculture & Natural Resources' => [
                'Farmer',
                'Livestock Farmer',
                'Aquaculture Farmer',
                'Agricultural Technician',
                'Agronomist',
                'Agricultural Engineer',
                'Farm Manager',
                'Forestry Officer',
                'Environmental Officer',
                'Irrigation Engineer',
                'Agri-business Manager',
                'Agri Sales Representative',
                'Agri Supply Chain Officer',
                'Food Processing Specialist',
            ],
            'Engineering & Technology' => [
                'Software Engineer',
                'Full Stack Developer',
                'Frontend Developer',
                'Backend Developer',
                'Mobile Developer',
                'DevOps Engineer',
                'Site Reliability Engineer',
                'Data Engineer',
                'Data Scientist',
                'AI Engineer',
                'Machine Learning Engineer',
                'Cybersecurity Analyst',
                'System Administrator',
                'Network Engineer',
                'Cloud Engineer',
                'QA Engineer',
                'Game Developer',
                'Embedded Engineer',
                'IT Support',
                'Database Administrator',
            ],
            'Business & Management' => [
                'Business Analyst',
                'Project Manager',
                'Product Manager',
                'Operations Manager',
                'Strategy Manager',
                'Consultant',
                'Entrepreneur',
                'General Manager',
                'Executive Officer',
                'Scrum Master',
            ],
            'Finance & Accounting' => [
                'Accountant',
                'Financial Analyst',
                'Auditor',
                'Tax Consultant',
                'Investment Analyst',
                'Risk Analyst',
                'Credit Analyst',
                'Finance Manager',
                'Controller',
                'Treasurer',
            ],
            'Sales, Marketing & E-commerce' => [
                'Sales Executive',
                'Sales Manager',
                'Digital Marketing Specialist',
                'SEO Specialist',
                'SEM Specialist',
                'Content Creator',
                'Social Media Manager',
                'Brand Manager',
                'Marketing Manager',
                'E-commerce Manager',
                'Affiliate Marketer',
                'Growth Hacker',
            ],
            'Human Resources' => [
                'HR Generalist',
                'HR Business Partner',
                'Recruiter',
                'Talent Acquisition',
                'HR Manager',
                'Compensation Specialist',
                'Training Officer',
                'Organization Development',
                'Payroll Officer',
            ],
            'Healthcare & Medical' => [
                'Doctor',
                'Nurse',
                'Pharmacist',
                'Dentist',
                'Medical Technologist',
                'Radiologist',
                'Physiotherapist',
                'Public Health Officer',
                'Caregiver',
                'Nutritionist',
            ],
            'Education & Research' => [
                'Teacher',
                'Lecturer',
                'Researcher',
                'Tutor',
                'Instructional Designer',
                'Academic Officer',
                'Curriculum Developer',
            ],
            'Legal & Compliance' => [
                'Lawyer',
                'Legal Officer',
                'Compliance Officer',
                'Notary',
                'Contract Specialist',
            ],
            'Creative, Media & Design' => [
                'Graphic Designer',
                'UX Designer',
                'UI Designer',
                'Product Designer',
                'Video Editor',
                'Photographer',
                'Animator',
                'Art Director',
                'Creative Director',
                'Copywriter',
            ],
            'Service & Hospitality' => [
                'Hotel Manager',
                'Receptionist',
                'Chef',
                'Barista',
                'Waiter',
                'Tour Guide',
                'Event Planner',
                'Customer Service',
                'Call Center Agent',
            ],
            'Logistics & Supply Chain' => [
                'Supply Chain Manager',
                'Logistics Coordinator',
                'Warehouse Manager',
                'Procurement Officer',
                'Inventory Controller',
                'Shipping Officer',
                'Import Export Specialist',
            ],
            'Transportation & Mobility' => [
                'Driver',
                'Pilot',
                'Flight Attendant',
                'Delivery Rider',
                'Transport Manager',
            ],
            'Manufacturing & Production' => [
                'Production Engineer',
                'Factory Manager',
                'Quality Control',
                'Quality Assurance',
                'Technician',
                'Maintenance Engineer',
                'Machine Operator',
                'Industrial Engineer',
            ],
            'Construction & Real Estate' => [
                'Civil Engineer',
                'Architect',
                'Site Engineer',
                'Construction Manager',
                'Surveyor',
                'Property Consultant',
                'Real Estate Agent',
            ],
            'Government & Public Sector' => [
                'Government Officer',
                'Policy Analyst',
                'Public Administrator',
                'State Enterprise Staff',
                'Military Officer',
                'Police Officer',
            ],
            'Security & Safety' => [
                'Security Guard',
                'Safety Officer',
                'Risk Officer',
                'Firefighter',
            ],
            'Science & Laboratory' => [
                'Lab Technician',
                'Scientist',
                'Chemist',
                'Biologist',
                'Research Assistant',
            ],
            'Environment & Energy' => [
                'Environmental Engineer',
                'Energy Analyst',
                'Sustainability Officer',
                'Renewable Energy Specialist',
            ],
            'Freelance & Entrepreneurship' => [
                'Freelancer',
                'Startup Founder',
                'Online Seller',
                'Content Creator',
                'Influencer',
            ],
            'Labor & General Work' => [
                'Factory Worker',
                'Cleaner',
                'Construction Worker',
                'Messenger',
                'Helper',
            ],
        ];

        foreach ($rolesByGroup as $groupName => $roles) {
            $group = CustomJobGroup::query()->where('name', $groupName)->first();

            if (!$group) {
                continue;
            }

            foreach ($roles as $roleName) {
                $slug = Str::slug($roleName);

                CustomJobRole::updateOrCreate(
                    [
                        'custom_job_group_id' => $group->id,
                        'slug' => $slug,
                    ],
                    [
                        'name' => $roleName,
                        'description' => null,
                    ]
                );
            }
        }
    }
}
