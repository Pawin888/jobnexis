<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;

class LessonController extends Controller
{
    // แก้ไข update method
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'l_name' => 'required|string|max:255',
            ]);

            $lesson = Lesson::findOrFail($id);
            $lesson->l_name = $request->l_name;
            $lesson->save();

            \Log::info('Lesson updated successfully', ['lesson' => $lesson->toArray()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'lesson' => $lesson,
                    'message' => 'แก้ไขชื่อเรียบร้อยแล้ว'
                ], 200);
            } else {
                return redirect()->route('courses.show', ['id' => $lesson->l_c_id])
                                ->with('success', 'แก้ไขชื่อเรียบร้อยแล้ว');
            }
        } catch (\Exception $e) {
            \Log::error('Error updating lesson', ['error' => $e->getMessage(), 'id' => $id]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ], 500);
            } else {
                return back()->withErrors(['error' => 'เกิดข้อผิดพลาด']);
            }
        }
    }

    // แก้ไข destroyLesson method
    public function destroyLesson(Request $request, $id)
    {
        try {
            $lesson = Lesson::findOrFail($id);
            $courseId = $lesson->l_c_id;
            $lesson->delete();

            \Log::info('Lesson deleted successfully', ['lesson_id' => $id]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ลบบทเรียนเรียบร้อยแล้ว'
                ], 200);
            } else {
                return redirect()->route('courses.show', ['id' => $courseId])
                                ->with('success', 'ลบบทเรียนเรียบร้อยแล้ว');
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting lesson', ['error' => $e->getMessage(), 'id' => $id]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ], 500);
            } else {
                return back()->withErrors(['error' => 'เกิดข้อผิดพลาด']);
            }
        }
    }
}
