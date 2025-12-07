<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Course;
use App\Models\User;
use App\Models\Skill;
use App\Models\Media;
use App\Models\Exam;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseMember;
use Illuminate\Support\Facades\DB;
use App\Models\ExamAttempt;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // ถ้ามี owner (user id) ให้กรองเฉพาะคอร์สของสถาบันนั้น
        $ownerId = (int) ($request->query('owner') ?? 0);

        $query = Course::query();
        if ($ownerId > 0) {
            // สิทธิ์: admin ดูของใครก็ได้, education ดูได้เฉพาะของตัวเอง
            if (!(auth()->user()?->role === 'admin' || auth()->id() === $ownerId)) {
                abort(403);
            }
            $query->where('c_create_by_id', $ownerId);
        }

        // ดึงข้อมูลคอร์สจากฐานข้อมูล
        $coursesFromDB = $query->get();

        // แปลงข้อมูลจาก DB ให้ตรงตามที่ View ต้องการ
        $allCourses = $coursesFromDB->map(function ($course) {
            return [
                'c_id'         => $course->c_id,
                'title'        => $course->c_name,
                'participants' => $course->participants ?? 0, // ใช้ accessor จาก model
                'status'       => $this->mapStatus($course->c_status),
                'image'        => $course->c_image,
            ];
        })->toArray();

        $perPage = 7;
        $page    = $request->get('page', 1);

        // Slice array ตามหน้า
        $coursesSlice = array_slice($allCourses, ($page - 1) * $perPage, $perPage);

        // สร้าง LengthAwarePaginator
        $pagedData = new LengthAwarePaginator(
            $coursesSlice,
            count($allCourses),
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('education.courses.index', compact('pagedData'));
    }

    /** รายการคอร์สสาธารณะ (สำหรับผู้ใช้ที่ล็อกอินทุก role) */
    public function catalog(Request $request)
    {
        $q = $request->string('q')->toString();
        $tab = $request->string('tab')->toString() ?: 'all'; // all | my

        $base = Course::query()->with('skills');

        if ($tab === 'my' && Auth::check()) {
            // คอร์สที่สมัครแล้วของผู้ใช้ (ไม่กรองสถานะ เพื่อให้เห็นคอร์สแม้ถูกปิด)
            $base->whereIn('c_id', \App\Models\CourseMember::where('cm_u_id', Auth::id())->pluck('cm_c_id'));
        } else {
            // คอร์สที่เปิดอยู่
            $base->where('c_status', 'open');
        }

        $query = $base->when($q, function ($qq) use ($q) {
            $qq->where(function ($w) use ($q) {
                $w->where('c_name', 'ilike', "%{$q}%")
                  ->orWhere('c_description', 'ilike', "%{$q}%")
                  ->orWhere('c_code', 'ilike', "%{$q}%");
            });
        })->orderByDesc('c_id');

        $courses = $query->paginate(12)->withQueryString();

        // สำหรับปุ่มสมัคร: หา course ที่ผู้ใช้ jobber สมัครอยู่
        $enrolledIds = [];
        if (Auth::check()) {
            $enrolledIds = CourseMember::where('cm_u_id', Auth::id())
                ->pluck('cm_c_id')
                ->toArray();
        }

        return view('courses.index', compact('courses', 'enrolledIds', 'q', 'tab'));
    }

    // Helper: แปลงสถานะ DB → ภาษาไทย
    private function mapStatus($dbStatus)
    {
        $statusMap = [
            'open'   => 'เผยแพร่',
            'draft'  => 'ฉบับร่าง',
            'closed' => 'ไม่เผยแพร่',
            'pending' => 'รออนุมัติ',
        ];

        return $statusMap[$dbStatus] ?? 'ไม่ทราบสถานะ';
    }

    public function create()
    {
        $skills = Skill::orderBy('name', 'asc')->get(); // เรียง A-Z
        return view('education.courses.create', compact('skills'));
    }

    public function store(Request $request)
    {
        // แปลง skill จาก string → array
        $skillsArray = !empty($request->skills) ? explode(',', $request->skills) : [];
        $request->merge(['skills' => $skillsArray]);

        // Validate ฟิลด์
        $request->validate([
            'c_name'       => 'required|string|max:50',
            'c_description'=> 'nullable|string|max:200',
            'skills'       => 'required|array|min:1|max:5',
            'skills.*'     => 'string|exists:skills,name',
            'c_status'     => 'required|in:open,draft',
            'c_image'      => 'nullable|image|max:2048',
            'c_code'       => 'required|string|max:20|unique:courses,c_code',
        ]);

        // จัดการไฟล์ภาพ
        $imagePath = null;
        if ($request->hasFile('c_image')) {
            $imagePath = $request->file('c_image')->store('courses', 'public');
        }

        // Skill ที่เลือก
        $skillNames = $request->skills;

        // สร้าง Course
        $course = Course::create([
            'c_name'        => $request->c_name,
            'c_description' => $request->c_description,
            'c_code'        => $request->c_code, // ใช้จากฟอร์ม
            'c_status'      => $request->c_status,
            'c_image'       => $imagePath,
            'c_create_by_id'=> Auth::id(),
            'c_create_at'   => now()->toDateString(),
            'c_end_at'      => now()->toDateString(),
        ]);

        // เชื่อม Skills
        $skillIds = Skill::whereIn('name', $skillNames)->pluck('id')->toArray();
        $course->skills()->sync($skillIds);

        return redirect()->route('courses.index')->with('success', 'สร้างคอร์สสำเร็จ');
    }

    public function show($id)
    {
        // ดึงคอร์สตาม id
       $course = Course::with('skills')->where('c_id', $id)->firstOrFail();

        // ดึงบทเรียนพร้อมสื่อและไฟล์
        $lessons = \App\Models\Lesson::with(['medias.files', 'exams'])
            ->where('l_c_id', $id)
            ->orderBy('l_index')
            ->get();

        // ดึงสื่อที่ไม่มีบทเรียน
        $soloMedias = Media::with('files')
            ->whereNull('m_l_id')
            ->where('m_c_id', $id)
            ->get();

        // เพิ่มแบบทดสอบที่ไม่อยู่ในบทเรียนใด (ใช้ชื่อฟิลด์ตาม DB)
        $soloExams = Exam::with('questions')
            ->whereNull('e_l_id')
            ->where('e_c_id', $id)
            ->orderBy('e_index')
            ->get();

        $isEnrolled = false;
        if (Auth::check()) {
            $isEnrolled = CourseMember::where('cm_c_id', $id)
                ->where('cm_u_id', Auth::id())
                ->exists();
        }

        // Build per-user pass summary for this course (education/admin use)
        $examIds = Exam::where('e_c_id', $id)->pluck('e_id');
        $courseExamTotal = $examIds->count();

        $memberIds = CourseMember::where('cm_c_id', $id)->pluck('cm_u_id')->unique();
        $passedByUser = collect();
        if ($courseExamTotal > 0 && $memberIds->isNotEmpty()) {
            $passedByUser = ExamAttempt::whereIn('exam_id', $examIds)
                ->whereIn('user_id', $memberIds)
                ->where('passed', true)
                ->select('user_id', DB::raw('COUNT(DISTINCT exam_id) as passed_count'))
                ->groupBy('user_id')
                ->pluck('passed_count', 'user_id');
        }

        $users = \App\Models\User::with('profile')->whereIn('id', $memberIds)->get()->keyBy('id');
        $reportRows = $memberIds->map(function ($uid) use ($users, $passedByUser, $courseExamTotal) {
            $u = $users->get($uid);
            $name = $u && $u->profile ? $u->profile->up_name : ("User #$uid");
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

        return view('education.courses.show', compact('course', 'lessons', 'soloMedias', 'soloExams', 'isEnrolled', 'reportRows', 'courseExamTotal'));
    }
    /** แสดงคอร์สสำหรับผู้ใช้ทั่วไป (เช่น Jobber) */
    public function publicShow($id)
    {
        // ตรวจสิทธิ์เข้าดู: ต้องสมัครก่อน (ยกเว้นบทบาท education/admin)
        $isEnrolled = false;
        if (Auth::check()) {
            $isEnrolled = CourseMember::where('cm_c_id', $id)
                ->where('cm_u_id', Auth::id())
                ->exists();

            $role = Auth::user()->role;
            if (!in_array($role, ['education', 'admin']) && !$isEnrolled) {
                return redirect()->route('courses.catalog')
                    ->with('error', 'กรุณาสมัครคอร์ส');
            }
        }

        // โหลดข้อมูลคอร์สและเนื้อหา เมื่อผ่านการตรวจสิทธิ์แล้ว
        $course = Course::with('skills')->where('c_id', $id)->firstOrFail();

        $lessons = \App\Models\Lesson::with(['medias.files'])
            ->where('l_c_id', $id)
            ->orderBy('l_index')
            ->get();

        $soloMedias = Media::with('files')
            ->whereNull('m_l_id')
            ->where('m_c_id', $id)
            ->get();

        $soloExams = Exam::with('questions')
            ->whereNull('e_l_id')
            ->where('e_c_id', $id)
            ->orderBy('e_index')
            ->get();

        $examCount = DB::table('exams')
            ->join('lessons', 'exams.e_l_id', '=', 'lessons.l_id')
            ->where('lessons.l_c_id', $id)
            ->count();

        // Provide default summary vars for view (education/admin block will check role)
        $courseExamTotal = Exam::where('e_c_id', $id)->count();
        $reportRows = collect();

        return view('education.courses.show', compact('course', 'lessons', 'soloMedias', 'soloExams', 'isEnrolled', 'examCount', 'reportRows', 'courseExamTotal'));
    }
    public function destroy($id)
    {
    $course = Course::findOrFail($id);

    // ลบรูปภาพถ้ามี
    if ($course->c_image && Storage::disk('public')->exists($course->c_image)) {
        Storage::disk('public')->delete($course->c_image);
    }

    // ลบคอร์ส
    $course->delete();

    return redirect()->back()->with('success', 'ลบคอร์สเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
    $course = Course::findOrFail($id);

    $skillsArray = !empty($request->skills) ? explode(',', $request->skills) : [];
    $request->merge(['skills' => $skillsArray]);

    // validate
    $request->validate([
        'c_name'       => 'required|string|max:50',
        'c_description'=> 'nullable|string|max:200',
        'skills'       => 'required|array|min:1|max:5',
        'skills.*'     => 'string|exists:skills,name',
        'c_status'     => 'required|in:closed,draft,open,pending',
        'c_image'      => 'nullable|image|max:2048',
    ]);

    // อัปเดตข้อมูลทั่วไป
    $course->c_name = $request->c_name;
    $course->c_description = $request->c_description;
    $course->c_status = $request->c_status;

    // อัปโหลดรูปภาพใหม่ถ้ามี
    if ($request->hasFile('c_image')) {
        // ลบรูปเก่า
        if ($course->c_image && Storage::disk('public')->exists($course->c_image)) {
            Storage::disk('public')->delete($course->c_image);
        }

        $path = $request->file('c_image')->store('courses', 'public');
        $course->c_image = $path;
    }

    $course->save();

    // อัปเดต skills ใน pivot table course_skill
    if (!empty($skillsArray)) {
        $skillIds = Skill::whereIn('name', $skillsArray)->pluck('id')->toArray();
        $course->skills()->sync($skillIds);
    } else {
        $course->skills()->sync([]);
    }

    return redirect()->route('courses.show', ['id' => $course->c_id])
                     ->with('success', 'แก้ไขคอร์สเรียบร้อยแล้ว');

    }
    public function edit($id)
    {
    $course = Course::with('skills')->findOrFail($id); // โหลด skills ด้วย
    $skills = Skill::orderBy('name', 'asc')->get(); // ดึงทักษะทั้งหมดเรียง A-Z
    return view('education.courses.edit', compact('course', 'skills'));
    }

    /** สมัครเข้าเรียน (เฉพาะ Jobber) */
    public function enroll(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        if ($course->c_status !== 'open') {
            return back()->with('error', 'คอร์สนี้ยังไม่เปิดรับสมัคร');
        }
        $data = $request->validate([
            'code' => ['required','string'],
        ]);
        if (!hash_equals($course->c_code, $data['code'])) {
            return back()->withInput()->with('error', 'รหัสคอร์สไม่ถูกต้อง');
        }
        $userId = Auth::id();
        // ป้องกันสมัครซ้ำ
        CourseMember::firstOrCreate([
            'cm_c_id' => $course->c_id,
            'cm_u_id' => $userId,
        ], [
            'cm_passed' => false,
        ]);

        return redirect()->route('courses.view', ['id' => $course->c_id])
            ->with('success', 'สมัครเข้าเรียนเรียบร้อยแล้ว');
    }

    /** ยกเลิกการสมัคร (เฉพาะ Jobber) */
    public function unenroll(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $userId = Auth::id();
        CourseMember::where('cm_c_id', $course->c_id)->where('cm_u_id', $userId)->delete();
        return back()->with('success', 'ยกเลิกการสมัครเรียบร้อยแล้ว');
    }
}
