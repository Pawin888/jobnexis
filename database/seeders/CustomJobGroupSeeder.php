<?php

namespace Database\Seeders;

use App\Models\CustomJobGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomJobGroupSeeder extends Seeder
{
    /**
     * Seed custom job groups from the provided category list.
     */
    public function run(): void
    {
        $categories = [
            ['en' => 'Agriculture & Natural Resources', 'th' => 'เกษตรกรรมและทรัพยากรธรรมชาติ'],
            ['en' => 'Manufacturing & Production', 'th' => 'การผลิตและอุตสาหกรรม'],
            ['en' => 'Engineering & Technology', 'th' => 'วิศวกรรมและเทคโนโลยี'],
            ['en' => 'Construction & Real Estate', 'th' => 'ก่อสร้างและอสังหาริมทรัพย์'],
            ['en' => 'Business & Management', 'th' => 'ธุรกิจและการบริหาร'],
            ['en' => 'Finance & Accounting', 'th' => 'การเงินและบัญชี'],
            ['en' => 'Sales, Marketing & E-commerce', 'th' => 'การขาย การตลาด และอีคอมเมิร์ซ'],
            ['en' => 'Human Resources', 'th' => 'ทรัพยากรบุคคล'],
            ['en' => 'Healthcare & Medical', 'th' => 'สุขภาพและการแพทย์'],
            ['en' => 'Education & Research', 'th' => 'การศึกษาและวิจัย'],
            ['en' => 'Legal & Compliance', 'th' => 'กฎหมายและการกำกับดูแล'],
            ['en' => 'Creative, Media & Design', 'th' => 'สร้างสรรค์ สื่อ และการออกแบบ'],
            ['en' => 'Service & Hospitality', 'th' => 'บริการและการโรงแรม'],
            ['en' => 'Logistics & Supply Chain', 'th' => 'โลจิสติกส์และซัพพลายเชน'],
            ['en' => 'Transportation & Mobility', 'th' => 'การขนส่ง'],
            ['en' => 'Government & Public Sector', 'th' => 'ภาครัฐและราชการ'],
            ['en' => 'Security & Safety', 'th' => 'ความปลอดภัย'],
            ['en' => 'Science & Laboratory', 'th' => 'วิทยาศาสตร์และห้องปฏิบัติการ'],
            ['en' => 'Environment & Energy', 'th' => 'สิ่งแวดล้อมและพลังงาน'],
            ['en' => 'Freelance & Entrepreneurship', 'th' => 'อิสระและผู้ประกอบการ'],
            ['en' => 'Labor & General Work', 'th' => 'แรงงานทั่วไป'],
        ];

        foreach ($categories as $item) {
            CustomJobGroup::updateOrCreate(
                ['slug' => Str::slug($item['en'])],
                [
                    'name' => $item['en'],
                    'description' => $item['th'],
                ]
            );
        }
    }
}
