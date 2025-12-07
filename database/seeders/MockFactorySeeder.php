<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MockFactorySeeder extends Seeder
{
    public function run(): void
    {
        // === ตั้งค่า ===
        $jobberCount   = 40;
        $providerCount = 40;
        $startId       = 1001; // ทำให้รันซ้ำได้แบบ deterministic
        $now           = Carbon::now()->toDateTimeString();
        $pwHash        = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // "password"

        // ข้อมูลสุ่มแบบไทย ๆ
        $thaiMale = ["อนันต์","กิตติ","พงศ์ภพ","ธนายุทธ","ชยพล","พัฒน์","นที","อรุณ","ภูมิ","วศิน","ปริญญ์","ณัฐ","ศุภกร","ปรเมศ","ภูวเดช"];
        $thaiFemale = ["ชลิดา","พิมพ์","ณัฐชา","กนกวรรณ","สุชาดา","วลัย","อริสา","ศิรินันท์","วราลี","ปรียา","ธนิดา","อรณิชา","มินตรา","พัชราภา","ปาลิดา"];
        $prefixes = [ ["นาย","ชาย"], ["นางสาว","หญิง"], ["นาง","หญิง"] ];
        $provinces = ["กรุงเทพมหานคร","เชียงใหม่","ชลบุรี","ขอนแก่น","นครราชสีมา","ภูเก็ต","สงขลา","สุราษฎร์ธานี","พิษณุโลก","อุบลราชธานี","นครศรีธรรมราช","นครปฐม","ระยอง","สมุทรปราการ","นนทบุรี"];
        $institutes = ["กระทรวงแรงงาน","มหาวิทยาลัยเชียงใหม่","จุฬาลงกรณ์มหาวิทยาลัย","ธรรมศาสตร์","ม.เกษตรศาสตร์","TESA","Google","Microsoft","AWS","LINE Thailand"];
        $certNames = ["Data Analytics","Cyber Security Basics","Frontend Web Dev","ภาษาอังกฤษ B2","Digital Marketing","Product Management","Kubernetes Fundamentals","Laravel Pro","React Mastery"];
        $eduNames = ["ปริญญาตรี","ปริญญาโท","ประกาศนียบัตรวิชาชีพ","มัธยมศึกษาตอนปลาย","หลักสูตรระยะสั้น"];
        $companies = ["ลาน่าเทค","อันดามัน โซลูชัน","สยามดิจิทัล","โคราชซิสเต็ม","ระยองซัพพลาย","นอร์ธสตาร์","บียอนด์โค้ด","วันทูไฟว์","บิ๊กแคท","อัลฟ่าเบส","ไบเทคโซลูชั่น","ไทยคราฟท์","ซันไรส์","คอฟฟี่คลับ","สไมล์สตูดิโอ"];
        $streets = ["ถ.พระราม 9","ถ.ลาดพร้าว","ถ.สุขุมวิท","ถ.วิภาวดีรังสิต","ถ.มิตรภาพ","ถ.ช้างคลาน","ถ.เจริญกรุง","ถ.ศรีจันทร์","ถ.บางนา-ตราด","ถ.ประชาชื่น"];
        $companyTypes = ["บริษัทจำกัด","ห้างหุ้นส่วนจำกัด","บริษัทมหาชนจำกัด","กิจการเจ้าของคนเดียว","สตาร์ทอัพ"];

        // ฟังก์ชันช่วย
        $randPhone = fn() => '0'.collect(range(1,9))->map(fn() => random_int(0,9))->implode('');
        $randDate  = function(int $y1, int $y2) {
            $y = random_int($y1, $y2);
            $m = random_int(1, 12);
            $d = random_int(1, 28);
            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        };
        $randTs    = fn() => Carbon::now()->subDays(random_int(0, 900))->format('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            // --- เตรียม id ตามแบบ deterministic ---
            $users = [];
            for ($i=0; $i<$jobberCount; $i++) {
                $uid = $startId + $i;
                $users[] = [
                    'id' => $uid,
                    'email' => sprintf('jobber%02d@example.com', $i+1),
                    'email_verified_at' => $randTs(),
                    'password' => $pwHash,
                    'remember_token' => Str::random(60),
                    'created_at' => $randTs(),
                    'updated_at' => $randTs(),
                    'role' => 'jobber',
                    'is_banned' => (bool) (random_int(1,100) <= 8),
                ];
            }
            for ($i=0; $i<$providerCount; $i++) {
                $uid = $startId + $jobberCount + $i;
                $users[] = [
                    'id' => $uid,
                    'email' => sprintf('provider%02d@example.com', $i+1),
                    'email_verified_at' => $randTs(),
                    'password' => $pwHash,
                    'remember_token' => Str::random(60),
                    'created_at' => $randTs(),
                    'updated_at' => $randTs(),
                    'role' => 'provider',
                    'is_banned' => (bool) (random_int(1,100) <= 5),
                ];
            }

            // upsert users (เพื่อรันซ้ำได้)
            DB::table('users')->upsert(
                $users,
                ['id'],
                ['email','email_verified_at','password','remember_token','created_at','updated_at','role','is_banned']
            );

            // === JOBBER side ===
            $upRows = [];
            $cerRows = [];
            $eduRows = [];
            $weRows = [];

            $up_id = 1; $cer_id = 1; $ed_id = 1; $we_id = 1;

            for ($i=0; $i<$jobberCount; $i++) {
                $uid = $startId + $i;

                // profile
                [$prefix, $gender] = $prefixes[array_rand($prefixes)];
                $first = $gender === 'ชาย' ? $thaiMale[array_rand($thaiMale)] : $thaiFemale[array_rand($thaiFemale)];
                $upRows[] = [
                    'up_id'        => $up_id++,
                    'up_u_id'      => $uid,
                    'up_prefix'    => $prefix,
                    'up_name'=> $first,
                    'up_phone'     => $randPhone(),
                    'up_gender'    => $gender,
                    'up_birth_date'  => $randDate(1975, 2005),
                    'up_city'  => $provinces[array_rand($provinces)],
                    'created_at'   => $randTs(),
                    'updated_at'   => $randTs(),
                ];

                // certificates 1–4
                $nCert = random_int(1,4);
                for ($k=0; $k<$nCert; $k++) {
                    $img = 'certificates/'.Str::random(8).'.jpg';
                    $cerRows[] = [
                        'cer_id'              => $cer_id++,
                        'cer_u_id'            => $uid,
                        'cer_name'            => $certNames[array_rand($certNames)],
                        'cer_institute_name'  => $institutes[array_rand($institutes)],
                        'cer_ref_number'      => 'CER-'.random_int(100000, 999999),
                        'cer_image_path'      => '/storage/'.$img,
                        'cer_publiced'        => (bool) random_int(0,1),
                        'created_at'          => $randTs(),
                        'updated_at'          => $randTs(),
                    ];
                }

                // educations 1–2 (ed_end_date ต้องไม่ null)
                $nEdu = random_int(1,2);
                for ($k=0; $k<$nEdu; $k++) {
                    $start = $randDate(1998, 2022);
                    // end 1–4 ปีถัดมา และไม่ย้อนก่อน start
                    [$y,$m,$d] = array_map('intval', explode('-', $start));
                    $endY = $y + random_int(1,4);
                    $endM = min($m + random_int(0, 11), 12);
                    $endD = min($d + random_int(0, 27), 28);
                    $end  = sprintf('%04d-%02d-%02d', max($endY,$y+1), $endM, $endD);
                    if ($end < $start) $end = $start;

                    $eduRows[] = [
                        'ed_id'         => $ed_id++,
                        'ed_u_id'       => $uid,
                        'ed_name'       => $eduNames[array_rand($eduNames)],
                        'ed_start_date' => $start,
                        'ed_end_date'   => $end, // ไม่เป็น null
                        'created_at'    => $randTs(),
                        'updated_at'    => $randTs(),
                    ];
                }

                // work experiences 0–3
                $nWe = random_int(0,3);
                for ($k=0; $k<$nWe; $k++) {
                    $ws = $randDate(2010, 2023);
                    $we = (random_int(1,10) <= 3) ? null : $randDate(2011, 2025);
                    if ($we !== null && $we < $ws) $we = $ws;
                    $weRows[] = [
                        'we_id'            => $we_id++,
                        'we_u_id'          => $uid,
                        'we_company_name'  => $companies[array_rand($companies)],
                        'we_start_date'    => $ws,
                        'we_end_date'      => $we,
                        'created_at'       => $randTs(),
                        'updated_at'       => $randTs(),
                    ];
                }
            }

            // upsert โปรไฟล์/ตารางย่อย
            if (Schema::hasTable('user_profiles')) {
                DB::table('user_profiles')->upsert(
                    $upRows,
                    ['up_id'],
                    ['up_u_id','up_prefix','up_name','up_phone','up_gender','up_birth_date','up_city','created_at','updated_at']
                );
            }
            if (Schema::hasTable('certificates')) {
                DB::table('certificates')->upsert(
                    $cerRows,
                    ['cer_id'],
                    ['cer_u_id','cer_name','cer_institute_name','cer_ref_number','cer_image_path','cer_publiced','created_at','updated_at']
                );
            }
            if (Schema::hasTable('educations')) {
                $eduCols = Schema::getColumnListing('educations');
                $updates = ['ed_u_id','ed_name','ed_start_date','created_at','updated_at'];
                if (in_array('ed_end_date', $eduCols)) $updates[] = 'ed_end_date';
                DB::table('educations')->upsert($eduRows, ['ed_id'], $updates);
            }
            if (Schema::hasTable('work_experiences')) {
                DB::table('work_experiences')->upsert(
                    $weRows,
                    ['we_id'],
                    ['we_u_id','we_company_name','we_start_date','we_end_date','created_at','updated_at']
                );
            }

            // === PROVIDER side: companies_profiles ===
            $coRows = [];
            $co_id = 1;
            $hasCoUserId = Schema::hasColumn('companies_profiles','co_user_id');
            $hasCoUId    = Schema::hasColumn('companies_profiles','co_u_id');

            for ($i=0; $i<$providerCount; $i++) {
                $uid   = $startId + $jobberCount + $i;
                $name  = $companies[array_rand($companies)].' #'.random_int(1,99);
                $prov  = $provinces[array_rand($provinces)];
                $img1  = 'companies/'.Str::random(8).'_profile.jpg';
                $img2  = 'companies/'.Str::random(8).'_banner.jpg';

                $row = [
                    'co_id'           => $co_id++,
                    'co_name'         => $name,
                    'co_email'        => 'contact@'.Str::slug(explode(' ', $name)[0]).'.co.th',
                    'co_phone'        => $randPhone(),
                    'co_birthday'     => $randDate(1990, 2020),
                    'co_type'         => $companyTypes[array_rand($companyTypes)],
                    'co_number'       => (string) random_int(1000000000, 9999999999),
                    'co_jobber_amount'=> random_int(1, 500),
                    'co_address'      => random_int(1,199).'/'.random_int(1,99).' '.$streets[array_rand($streets)],
                    'co_province'     => $prov,
                    'co_details'      => 'บริษัทจำลองสำหรับทดสอบระบบ',
                    'co_profile_img'  => $img1,
                    'co_banner_img'   => $img2,
                    'created_at'      => $randTs(),
                    'updated_at'      => $randTs(),
                ];
                // รองรับทั้งสองชื่อคอลัมน์
                if ($hasCoUserId) $row['co_user_id'] = $uid;
                if ($hasCoUId)    $row['co_u_id']    = $uid;

                $coRows[] = $row;
            }

            if (Schema::hasTable('companies_profiles')) {
                $updateCols = ['co_name','co_email','co_phone','co_birthday','co_type','co_number','co_jobber_amount','co_address','co_province','co_details','co_profile_img','co_banner_img','created_at','updated_at'];
                if ($hasCoUserId) $updateCols[] = 'co_user_id';
                if ($hasCoUId)    $updateCols[] = 'co_u_id';

                DB::table('companies_profiles')->upsert(
                    $coRows,
                    ['co_id'],
                    $updateCols
                );
            }

            DB::commit();
            $this->command->info('✅ MockFactorySeeder: jobber 40 + provider 40 พร้อมโปรไฟล์ครบแล้ว');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error($e->getMessage());
            throw $e;
        }
    }
}
