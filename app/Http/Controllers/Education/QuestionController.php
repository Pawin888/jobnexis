<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Exam;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuestionController extends Controller
{
    use AuthorizesRequests;

    // หน้าจัดการคำถาม
    public function manage($exam_id)
    {
        $exam = Exam::with(['questions', 'course'])->findOrFail($exam_id);
        
        // ตรวจสอบสิทธิ์
        if ($exam->e_c_id) {
            $course = Course::findOrFail($exam->e_c_id);
            $this->authorize('manageContent', $course);
        }

        return view('education.questions.manage', compact('exam'));
    }

    // เพิ่มคำถามใหม่
    public function store(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,e_id',
            'questions' => 'required|array|min:1',
            'questions.*.q_question' => 'required|string',
            'questions.*.q_answer1' => 'required|string',
            'questions.*.q_answer2' => 'required|string',
            'questions.*.q_answer3' => 'required|string',
            'questions.*.q_answer4' => 'required|string',
            'questions.*.q_correct_answer' => 'required|integer|min:1|max:4',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        
        // ตรวจสอบสิทธิ์
        if ($exam->e_c_id) {
            $course = Course::findOrFail($exam->e_c_id);
            $this->authorize('manageContent', $course);
        }

        // บันทึกคำถาม
        foreach ($request->questions as $questionData) {
            Question::create([
                'q_question' => $questionData['q_question'],
                'q_answer1' => $questionData['q_answer1'],
                'q_answer2' => $questionData['q_answer2'],
                'q_answer3' => $questionData['q_answer3'],
                'q_answer4' => $questionData['q_answer4'],
                'q_correct_answer' => (int)$questionData['q_correct_answer'],
                'q_e_id' => $exam->e_id,
            ]);
        }

        return redirect()->route('questions.manage', $exam->e_id)
                        ->with('success', 'เพิ่มคำถามเรียบร้อยแล้ว');
    }

    // แก้ไขคำถาม
    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $exam = Exam::findOrFail($question->q_e_id);
        
        // ตรวจสอบสิทธิ์
        if ($exam->e_c_id) {
            $course = Course::findOrFail($exam->e_c_id);
            $this->authorize('manageContent', $course);
        }

        $request->validate([
            'q_question' => 'required|string',
            'q_answer1' => 'required|string',
            'q_answer2' => 'required|string',
            'q_answer3' => 'required|string',
            'q_answer4' => 'required|string',
            'q_correct_answer' => 'required|integer|min:1|max:4',
        ]);

        $question->update([
            'q_question' => $request->q_question,
            'q_answer1' => $request->q_answer1,
            'q_answer2' => $request->q_answer2,
            'q_answer3' => $request->q_answer3,
            'q_answer4' => $request->q_answer4,
            'q_correct_answer' => (int)$request->q_correct_answer,
        ]);

        return redirect()->route('questions.manage', $exam->e_id)
                        ->with('success', 'แก้ไขคำถามเรียบร้อยแล้ว');
    }

    // ลบคำถาม
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $exam = Exam::findOrFail($question->q_e_id);
        
        // ตรวจสอบสิทธิ์
        if ($exam->e_c_id) {
            $course = Course::findOrFail($exam->e_c_id);
            $this->authorize('manageContent', $course);
        }

        $question->delete();

        return redirect()->route('questions.manage', $exam->e_id)
                        ->with('success', 'ลบคำถามเรียบร้อยแล้ว');
    }

    public function updateBatch(Request $request)
    {
        try {
            // Debug ข้อมูลที่ส่งมา
            \Log::info('Request data:', $request->all());
            
            $exam = Exam::findOrFail($request->exam_id);
            
            // ตรวจสอบสิทธิ์
            if ($exam->e_c_id) {
                $course = Course::findOrFail($exam->e_c_id);
                $this->authorize('manageContent', $course);
            }

            $newCount = 0;
            $updateCount = 0;

            // จัดการคำถามใหม่
            if ($request->has('new_questions') && is_array($request->new_questions)) {
                foreach ($request->new_questions as $index => $questionData) {
                    \Log::info("Processing new question {$index}:", $questionData);
                    
                    // ตรวจสอบว่าข้อมูลครบถ้วนและไม่ใช่ string ว่าง
                    if (is_array($questionData) && 
                        !empty(trim($questionData['q_question'] ?? '')) && 
                        !empty(trim($questionData['q_answer1'] ?? '')) && 
                        !empty(trim($questionData['q_answer2'] ?? '')) && 
                        !empty(trim($questionData['q_answer3'] ?? '')) && 
                        !empty(trim($questionData['q_answer4'] ?? '')) && 
                        !empty($questionData['q_correct_answer'] ?? '')) {
                        
                        Question::create([
                            'q_question' => trim($questionData['q_question']),
                            'q_answer1' => trim($questionData['q_answer1']),
                            'q_answer2' => trim($questionData['q_answer2']),
                            'q_answer3' => trim($questionData['q_answer3']),
                            'q_answer4' => trim($questionData['q_answer4']),
                            'q_correct_answer' => (int)$questionData['q_correct_answer'],
                            'q_e_id' => $exam->e_id,
                        ]);
                        $newCount++;
                    }
                }
            }

            // จัดการคำถามที่แก้ไข
            if ($request->has('questions') && is_array($request->questions)) {
                foreach ($request->questions as $questionKey => $questionData) {
                    \Log::info("Processing question key: {$questionKey}", is_array($questionData) ? $questionData : ['data' => $questionData]);
                    
                    // ข้าม key ที่ไม่ใช่ตัวเลขหรือข้อมูลที่ไม่ถูกต้อง
                    if (!is_numeric($questionKey) || !is_array($questionData)) {
                        \Log::warning("Skipping invalid key or data: {$questionKey}");
                        continue;
                    }
                    
                    if (!isset($questionData['q_id']) || !is_numeric($questionData['q_id'])) {
                        \Log::warning("Missing or invalid q_id for key: {$questionKey}");
                        continue;
                    }
                    
                    $questionId = (int)$questionData['q_id'];
                    
                    // ตรวจสอบว่าคำถามมีอยู่จริงและเป็นของ exam นี้
                    $question = Question::where('q_id', $questionId)
                                       ->where('q_e_id', $exam->e_id)
                                       ->first();
                                       
                    if ($question && !empty(trim($questionData['q_question'] ?? ''))) {
                        $question->update([
                            'q_question' => trim($questionData['q_question']),
                            'q_answer1' => trim($questionData['q_answer1'] ?? ''),
                            'q_answer2' => trim($questionData['q_answer2'] ?? ''),
                            'q_answer3' => trim($questionData['q_answer3'] ?? ''),
                            'q_answer4' => trim($questionData['q_answer4'] ?? ''),
                            'q_correct_answer' => (int)($questionData['q_correct_answer'] ?? 1),
                        ]);
                        $updateCount++;
                        \Log::info("Updated question ID: {$questionId}");
                    } else {
                        \Log::warning("Question not found or invalid data for ID: {$questionId}");
                    }
                }
            }

            // สร้างข้อความแจ้งผลลัพธ์
            $messages = [];
            if ($newCount > 0) {
                $messages[] = "เพิ่มคำถามใหม่ {$newCount} ข้อ";
            }
            if ($updateCount > 0) {
                $messages[] = "แก้ไขคำถาม {$updateCount} ข้อ";
            }
            
            $message = count($messages) > 0 ? implode(' และ ', $messages) . ' เรียบร้อยแล้ว' : 'ไม่มีการเปลี่ยนแปลงข้อมูล';

            return redirect()->route('questions.manage', $exam->e_id)
                            ->with('success', $message);
                            
        } catch (\Exception $e) {
            \Log::error('Error in updateBatch: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                            ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage())
                            ->withInput();
        }
    }
}