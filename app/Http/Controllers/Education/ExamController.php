<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Course;
use App\Models\Question;
use App\Models\Skill;
use App\Models\ExamAttempt;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamController extends Controller
{
    use AuthorizesRequests;

    // แสดงฟอร์มสร้างแบบทดสอบ
    public function create($courseId = null)
    {
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $this->authorize('manageContent', $course);
        }
        
        $lessons = Lesson::where('l_c_id', $courseId)->get();
        $skills = Skill::orderBy('category')->orderBy('name')->get();
        $skillsByCategory = $skills->groupBy(function ($s) { return $s->category ?: 'ทั่วไป'; });
        return view('education.exams.create', compact('lessons', 'courseId', 'skillsByCategory'));
    }

    // บันทึกแบบทดสอบ
    public function store(Request $request)
    {
        // ตรวจสอบสิทธิ์
        if ($request->course_id) {
            $course = Course::findOrFail($request->course_id);
            $this->authorize('manageContent', $course);
        }

        // Parse skills from comma-separated string to array
        $skillsArray = !empty($request->skills) ? explode(',', $request->skills) : [];
        $request->merge(['skills' => $skillsArray]);

        // Count incoming questions to cap threshold
        $totalIncomingQuestions = is_array($request->questions) ? count($request->questions) : 0;

        $request->validate([
            'e_name' => 'required|string|max:255',
            'e_l_id' => 'nullable|exists:lessons,l_id',
            'e_description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.q_question' => 'required|string',
            'questions.*.q_answer1' => 'required|string',
            'questions.*.q_answer2' => 'required|string', 
            'questions.*.q_answer3' => 'required|string',
            'questions.*.q_answer4' => 'required|string',
            'questions.*.q_correct_answer' => 'required|integer|min:1|max:4',
            'pass_threshold' => 'required|integer|min:1|max:' . max(1, $totalIncomingQuestions),
            'skills'       => 'required|array|min:1|max:10',
            'skills.*'     => 'string|exists:skills,name',
        ]);

        // สร้างแบบทดสอบ
        $exam = Exam::create([
            'e_name' => $request->e_name,
            'e_description' => $request->e_description,
            'e_skills' => $skillsArray,
            'e_l_id' => $request->e_l_id,
            'e_c_id' => $request->course_id,
            'e_index' => 0,
            'pass_threshold' => (int)$request->pass_threshold,
        ]);

        // บันทึกคำถาม
        if ($request->has('questions') && is_array($request->questions)) {
            foreach ($request->questions as $questionData) {
                \App\Models\Question::create([
                    'q_question' => $questionData['q_question'],
                    'q_answer1' => $questionData['q_answer1'],
                    'q_answer2' => $questionData['q_answer2'],
                    'q_answer3' => $questionData['q_answer3'],
                    'q_answer4' => $questionData['q_answer4'],
                    'q_correct_answer' => (int)$questionData['q_correct_answer'],
                    'q_e_id' => $exam->e_id,
                ]);
            }
        }

                return redirect()->route('courses.show', ['id' => $request->course_id])
                ->with('success', 'บันทึกแบบทดสอบและคำถามเรียบร้อยแล้ว');
    }

    // สร้างบทเรียนใหม่ (สำหรับ AJAX)
    public function storeLesson(Request $request)
    {
        // ตรวจสอบสิทธิ์
        if ($request->l_c_id) {
            $course = Course::findOrFail($request->l_c_id);
            $this->authorize('manageContent', $course);
        }

        $request->validate([
            'l_name' => 'required|string|max:50',
            'l_c_id' => 'nullable|exists:courses,c_id',
        ]);

        $lesson = Lesson::create([
            'l_name' => $request->l_name,
            'l_description' => null,
            'l_status' => 'draft',
            'l_index' => 0,
            'l_c_id' => $request->l_c_id,
        ]);

        return response()->json([
            'l_id' => $lesson->l_id,
            'l_name' => $lesson->l_name,
        ]);
    }

    // แก้ไขแบบทดสอบ
    public function edit($id)
    {
        $exam = Exam::findOrFail($id);

        $courseId = $exam->e_c_id;
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $this->authorize('manageContent', $course);
        }

        $lessons = Lesson::where('l_c_id', $courseId)->get();
        $skills = Skill::orderBy('category')->orderBy('name')->get();
        $skillsByCategory = $skills->groupBy(function ($s) { return $s->category ?: 'ทั่วไป'; });

        return view('education.exams.edit', compact('exam', 'lessons', 'courseId', 'skillsByCategory'));
    }

    // อัพเดทแบบทดสอบ
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        
        // ตรวจสอบสิทธิ์
        $courseId = $exam->e_c_id;
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $this->authorize('manageContent', $course);
        }

        // Parse skills from comma-separated string to array
        $skillsArray = !empty($request->skills) ? explode(',', $request->skills) : [];
        $request->merge(['skills' => $skillsArray]);

        $request->validate([
            'e_name' => 'required|string|max:255',
            'e_l_id' => 'nullable|exists:lessons,l_id',
            'e_description' => 'nullable|string',
            'pass_threshold' => 'required|integer|min:1|max:' . max(1, $exam->questions()->count()),
            'skills'       => 'required|array|min:1|max:10',
            'skills.*'     => 'string|exists:skills,name',
        ]);

        $exam->update([
            'e_name' => $request->e_name,
            'e_description' => $request->e_description,
            'e_l_id' => $request->e_l_id ?: null,
            'pass_threshold' => (int)$request->pass_threshold,
            'e_skills' => $skillsArray,
        ]);

                return redirect()->route('courses.show', ['id' => $courseId])
                 ->with('success', 'อัพเดทแบบทดสอบเรียบร้อยแล้ว');
    }

    // ดูรายละเอียดแบบทดสอบ
    public function show($id)
    {
        $exam = Exam::with(['course', 'lesson', 'questions'])->findOrFail($id);

        // ตรวจสอบสิทธิ์
        $course = Course::findOrFail($exam->e_c_id);
        $this->authorize('view', $course);

        return view('education.exams.show', compact('exam'));
    }

    // ลบแบบทดสอบ
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);

        $courseId = $exam->e_c_id;
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $this->authorize('manageContent', $course);
        }

        $exam->delete();

        return redirect()->route('courses.show', ['id' => $courseId])
                        ->with('success', 'ลบแบบทดสอบเรียบร้อยแล้ว');
    }

    // สำหรับ jobber ทำแบบทดสอบ
    public function take($id)
    {
        $exam = Exam::with(['questions', 'course'])->findOrFail($id);

        // ตรวจสอบว่า jobber สมัครคอร์สนี้แล้วหรือไม่
        $course = $exam->course;
        $isEnrolled = \App\Models\CourseMember::where('cm_c_id', $course->c_id)
                     ->where('cm_u_id', auth()->id())
                     ->exists();
        
        if (!$isEnrolled) {
            return redirect()->route('courses.view', $course->c_id)
                           ->with('error', 'คุณต้องสมัครเข้าเรียนก่อนทำแบบทดสอบ');
        }

        // If this user already passed and did not request retake, show last result with retake button
        $retake = request()->boolean('retake');
        if (!$retake) {
            $latestPassed = \App\Models\ExamAttempt::where('user_id', auth()->id())
                ->where('exam_id', $exam->e_id)
                ->where('passed', true)
                ->orderByDesc('id')
                ->first();
            if ($latestPassed) {
                $score = (int)$latestPassed->score;
                $totalQuestions = (int)$latestPassed->total_questions;
                $required = (int)($latestPassed->required ?? ($exam->pass_threshold ?? (int)ceil(max(1, $totalQuestions) * 0.6)));
                $passed = (bool)$latestPassed->passed;
                $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
                return view('education.exams.result', compact('exam', 'score', 'totalQuestions', 'percentage', 'required', 'passed'));
            }
        }

        return view('education.exams.take', compact('exam'));
    }

    public function submit(Request $request, $id)
    {
        $exam = Exam::with(['questions', 'course'])->findOrFail($id);
        
        // ตรวจสอบว่า jobber สมัครคอร์สนี้แล้วหรือไม่
        $course = $exam->course;
        $isEnrolled = \App\Models\CourseMember::where('cm_c_id', $course->c_id)
                     ->where('cm_u_id', auth()->id())
                     ->exists();
        
        if (!$isEnrolled) {
            return redirect()->route('courses.view', $course->c_id)
                        ->with('error', 'คุณไม่มีสิทธิ์เข้าถึง');
        }
        
        $answers = $request->input('answers', []);
        $score = 0;
        $totalQuestions = $exam->questions->count();
        
        // คำนวณคะแนน
        foreach ($exam->questions as $question) {
            $userAnswer = $answers[$question->q_id] ?? null;
            if ($userAnswer && (int)$userAnswer === (int)$question->q_correct_answer) {
                $score++;
            }
        }
        
        $required = $exam->pass_threshold ?? (int)ceil($totalQuestions * 0.6);
        $passed = $score >= $required;

        $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;

        // Save exam attempt
        ExamAttempt::create([
            'user_id' => auth()->id(),
            'exam_id' => $exam->e_id,
            'score' => $score,
            'total_questions' => $totalQuestions,
            'required' => $required,
            'passed' => $passed,
            'percentage' => number_format($percentage, 2, '.', ''),
            'answers' => $answers,
        ]);
        
        return view('education.exams.result', compact('exam', 'score', 'totalQuestions', 'percentage', 'required', 'passed'));
    }
}

