<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseMember;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Models\EducationProfile;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonController extends Controller
{
    public function show($id)
    {
        $course = Course::where('c_id', $id)->firstOrFail();

        // ดึงสมาชิกคอร์สพร้อมข้อมูลผู้ใช้
        $members = CourseMember::where('cm_c_id', $id)
            ->with(['user.profile'])
            ->orderByDesc('created_at')
            ->get();

        // ข้อมูลมหาวิทยาลัยที่เปิดสอน (ผู้สร้างคอร์ส)
        $universityProfile = null;
        if (!empty($course->c_create_by_id)) {
            $universityProfile = EducationProfile::where('e_u_id', $course->c_create_by_id)->first();
        }

        // ผู้ที่ถูกออกใบประกาศแล้วในคอร์สนี้
        $issuedUserIds = Certificate::where('cer_c_id', $course->c_id)->pluck('cer_u_id')->toArray();

        // Build per-user pass summary for this course
        $examIds = Exam::where('e_c_id', $id)->pluck('e_id');
        $courseExamTotal = $examIds->count();
        $memberIds = $members->pluck('cm_u_id')->unique();

        $passedByUser = collect();
        if ($courseExamTotal > 0 && $memberIds->isNotEmpty()) {
            $passedByUser = ExamAttempt::whereIn('exam_id', $examIds)
                ->whereIn('user_id', $memberIds)
                ->where('passed', true)
                ->select('user_id', \DB::raw('COUNT(DISTINCT exam_id) as passed_count'))
                ->groupBy('user_id')
                ->pluck('passed_count', 'user_id');
        }

        $reportRows = $members->map(function ($m) use ($passedByUser, $courseExamTotal) {
            $uid = $m->cm_u_id;
            $name = optional($m->user->profile)->up_name ?? ($m->user->email ?? ('User #'.$uid));
            $passed = (int)($passedByUser[$uid] ?? 0);
            $percentage = $courseExamTotal > 0 ? round(($passed / $courseExamTotal) * 100, 1) : 0.0;
            return (object) [
                'user_id' => $uid,
                'name' => $name,
                'passed' => $passed,
                'total' => $courseExamTotal,
                'percentage' => $percentage,
            ];
        });

        $summaryByUser = $reportRows->keyBy('user_id');

        return view('education.courses.person', compact('course', 'id', 'members', 'universityProfile', 'issuedUserIds', 'reportRows', 'courseExamTotal', 'summaryByUser'));
    }

    /** ออกประกาศนียบัตรให้ผู้เรียนรายบุคคล */
    public function issueCertificate($id, $userId)
    {
        $course = Course::where('c_id', $id)->firstOrFail();

        // ตรวจสอบสิทธิ์: เจ้าของคอร์ส (education) หรือ admin
        if (!Auth::check() || !in_array(Auth::user()->role, ['education', 'admin']) ||
            (Auth::user()->role === 'education' && $course->c_create_by_id !== Auth::id())) {
            abort(403, 'คุณไม่มีสิทธิ์ออกประกาศนียบัตรในคอร์สนี้');
        }

        // ต้องเป็นสมาชิกในคอร์ส
        $member = CourseMember::where('cm_c_id', $id)->where('cm_u_id', $userId)->firstOrFail();

        // กันออกซ้ำ
        $exists = Certificate::where('cer_c_id', $course->c_id)
            ->where('cer_u_id', $userId)
            ->exists();
        if ($exists) {
            return back()->with('success', 'ได้ออกประกาศนียบัตรให้ผู้เรียนรายนี้แล้ว');
        }

        // ข้อมูลสถาบันผู้ออก
        $eduProfile = EducationProfile::where('e_u_id', Auth::id())->first();
        $institute = $eduProfile->e_name ?? 'สถาบันการศึกษา';

        // สร้างรหัสอ้างอิงง่ายๆ
        $rand = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        $ref = sprintf('CER-%s-%s-%s', $course->c_id, now()->format('Ymd'), $rand);

        // สร้างรูปภาพใบประกาศและบันทึกลง storage (fallback เป็น placeholder หาก GD ใช้ไม่ได้)
        $studentName = optional(\App\Models\UserProfile::where('up_u_id', $userId)->first())->up_name
            ?? optional(\App\Models\User::find($userId))->email
            ?? ('User#'.$userId);
        $imageRelPath = $this->makeCertificatePng([
            'student'   => $studentName,
            'course'    => (string) ($course->c_name ?? ''),
            'institute' => (string) $institute,
            'ref'       => (string) $ref,
        ]) ?? 'https://via.placeholder.com/800x600.png?text=Certificate';

        Certificate::create([
            'cer_u_id'           => (int) $userId,
            'cer_c_id'           => (int) $course->c_id,
            'cer_name'           => 'ประกาศนียบัตร: ' . ($course->c_name ?? 'ไม่ระบุชื่อคอร์ส'),
            'cer_institute_name' => $institute,
            'cer_ref_number'     => $ref,
            'cer_image_path'     => $imageRelPath,
            'cer_publiced'       => true,
            'cer_from_lesson'    => true,
        ]);

        return back()->with('success', 'ออกประกาศนียบัตรให้ผู้เรียนเรียบร้อยแล้ว');
    }

    /** ออกประกาศนียบัตรให้ผู้เรียนทุกคนที่ยังไม่มีใบประกาศ */
    public function issueCertificatesAll($id)
    {
        $course = Course::where('c_id', $id)->firstOrFail();

        // ตรวจสอบสิทธิ์: เจ้าของคอร์ส (education) หรือ admin
        if (!Auth::check() || !in_array(Auth::user()->role, ['education', 'admin']) ||
            (Auth::user()->role === 'education' && $course->c_create_by_id !== Auth::id())) {
            abort(403, 'คุณไม่มีสิทธิ์ออกประกาศนียบัตรในคอร์สนี้');
        }

        $members = CourseMember::where('cm_c_id', $id)->get();

        $eduProfile = EducationProfile::where('e_u_id', $course->c_create_by_id)->first();
        $institute = $eduProfile->e_name ?? 'สถาบันการศึกษา';

        $created = 0;
        foreach ($members as $m) {
            $already = Certificate::where('cer_c_id', $course->c_id)
                ->where('cer_u_id', $m->cm_u_id)
                ->exists();
            if ($already) continue;

            $rand = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
            $ref = sprintf('CER-%s-%s-%s', $course->c_id, now()->format('Ymd'), $rand);
            $studentName = optional(\App\Models\UserProfile::where('up_u_id', $m->cm_u_id)->first())->up_name
                ?? optional(\App\Models\User::find($m->cm_u_id))->email
                ?? ('User#'.$m->cm_u_id);
            $imageRelPath = $this->makeCertificatePng([
                'student'   => $studentName,
                'course'    => (string) ($course->c_name ?? ''),
                'institute' => (string) $institute,
                'ref'       => (string) $ref,
            ]) ?? 'https://via.placeholder.com/800x600.png?text=Certificate';

            Certificate::create([
                'cer_u_id'           => (int) $m->cm_u_id,
                'cer_c_id'           => (int) $course->c_id,
                'cer_name'           => 'ประกาศนียบัตร: ' . ($course->c_name ?? 'ไม่ระบุชื่อคอร์ส'),
                'cer_institute_name' => $institute,
                'cer_ref_number'     => $ref,
                'cer_image_path'     => $imageRelPath,
                'cer_publiced'       => true,
                'cer_from_lesson'    => true,
            ]);
            $created++;
        }

        if ($created === 0) {
            return back()->with('success', 'สมาชิกทุกคนมีประกาศนียบัตรแล้ว');
        }
        return back()->with('success', 'ออกประกาศนียบัตรให้จำนวน ' . $created . ' คนเรียบร้อยแล้ว');
    }

    /**
     * สร้างไฟล์ PNG ใบประกาศอย่างง่ายและคืนค่าเป็น relative path ใน disk `public` (หรือ null ถ้าสร้างไม่ได้)
     * หมายเหตุ: เพื่อความเข้ากันได้ กรณีไม่มีฟอนต์ TTF เราจะเรนเดอร์เฉพาะตัวอักษรอังกฤษ (ASCII) ด้วย GD built-in font
     */
    private function makeCertificatePng(array $data): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            // Fallback: ดาวน์โหลด placeholder แล้วเซฟเป็นไฟล์ภายใน storage
            try {
                $dir = 'certificates';
                Storage::disk('public')->makeDirectory($dir);
                $ref = isset($data['ref']) ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $data['ref']) : Str::random(6);
                $filename = $dir . '/' . strtolower($ref) . '_' . Str::random(5) . '.png';
                $content = @file_get_contents('https://via.placeholder.com/800x600.png?text=Certificate');
                if ($content !== false) {
                    Storage::disk('public')->put($filename, $content);
                    return $filename;
                }
            } catch (\Throwable $e) {}
            return null;
        }

        $width = 1200; $height = 850;
        $im = imagecreatetruecolor($width, $height);
        if (!$im) return null;

        // Colors
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 20, 20, 20);
        $gold  = imagecolorallocate($im, 212, 175, 55);
        $blue  = imagecolorallocate($im, 59, 130, 246);

        // Background
        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        // Border
        imagesetthickness($im, 6);
        imagerectangle($im, 20, 20, $width - 20, $height - 20, $gold);

        // Header
        imagestring($im, 5, (int)($width/2 - 60), 60, 'Certificate', $blue);

        // Helper: ASCII-safe text
        $ascii = function ($text, $fallback = 'N/A') {
            $t = (string) $text;
            return preg_match('/^[\x20-\x7E]+$/', $t) ? $t : $fallback;
        };

        // Fields (ASCII only to avoid font issues)
        $course    = $ascii($data['course'] ?? '', 'Course');
        $student   = $ascii($data['student'] ?? '', 'Student');
        $institute = $ascii($data['institute'] ?? '', 'Institute');
        $ref       = $ascii($data['ref'] ?? '', 'REF');

        $y = 160; $line = 36; $xLabel = 120; $xValue = 280;
        imagestring($im, 4, $xLabel, $y,           'Course:',    $black); imagestring($im, 4, $xValue, $y,           $course,    $black);
        imagestring($im, 4, $xLabel, $y += $line,  'Student:',   $black); imagestring($im, 4, $xValue, $y,           $student,   $black);
        imagestring($im, 4, $xLabel, $y += $line,  'Institute:', $black); imagestring($im, 4, $xValue, $y,           $institute, $black);
        imagestring($im, 4, $xLabel, $y += $line,  'Ref:',       $black); imagestring($im, 4, $xValue, $y,           $ref,       $black);

        // Footer date
        $date = date('Y-m-d');
        imagestring($im, 3, $width - 220, $height - 60, 'Issued: ' . $date, $black);

        // Ensure directory
        $dir = 'certificates';
        try { Storage::disk('public')->makeDirectory($dir); } catch (\Throwable $e) {}

        $filename = $dir . '/' . strtolower($ref) . '_' . Str::random(5) . '.png';
        $fullPath = Storage::disk('public')->path($filename);
        imagepng($im, $fullPath);
        imagedestroy($im);

        return $filename;
    }
}
