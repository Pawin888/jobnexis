<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class MediaController extends Controller
{
    use AuthorizesRequests;
    // แสดงฟอร์มสร้างสื่อการสอน
    public function create($courseId = null)
    {
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $this->authorize('manageContent', $course);
        }

        $lessons = Lesson::where('l_c_id', $courseId)->get();
        return view('education.medias.create', compact('lessons', 'courseId'));
    }

    // บันทึกสื่อการสอน พร้อมรองรับหลายไฟล์
    public function store(Request $request)
    {
        Log::info('=== MediaController store START ===');
        Log::info('Store request data:', $request->except(['files']));

        if ($request->course_id) {
            $course = Course::findOrFail($request->course_id);
            $this->authorize('manageContent', $course);
        }

        $maxKb = (int) config('media.max_upload_mb', 3072) * 1024; // convert MB → KB for Laravel 'max'
        $request->validate([
            'm_name' => 'required|string|max:50',
            'm_l_id' => 'nullable|exists:lessons,l_id',
            'm_desc' => 'nullable|string|max:200',
            'm_index' => 'nullable|integer',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,svg,mp4,mov,avi,mkv,webm,wmv,flv,3gp|max:' . $maxKb,
        ]);

        Log::info('Validation passed for store');

        // สร้าง Media
        $media = Media::create([
            'm_name' => $request->m_name,
            'm_l_id' => $request->m_l_id,
            'm_c_id' => $request->course_id,
            'm_index' => $request->m_index ?? 0,
            'm_path' => null,
            'm_desc' => $request->m_desc,
        ]);

        Log::info('Media created:', ['media_id' => $media->m_id, 'media_name' => $media->m_name]);

        // ตรวจสอบและบันทึกไฟล์หลายไฟล์อย่างปลอดภัย
        $uploadedFiles = $request->file('files') ?? [];
        Log::info('Processing file uploads:', ['files_count' => count($uploadedFiles)]);

        $disk = config('media.disk', 'public');
        foreach ($uploadedFiles as $index => $file) {
            $path = $file->store('media', $disk);

            $mediaFile = MediaFile::create([
                'mf_m_id' => $media->m_id,
                'mf_path' => $path,
                'mf_original_name' => $file->getClientOriginalName(),
                'mf_type' => $file->getClientMimeType(),
                'mf_size' => (int) ($file->getSize() / 1024),
            ]);

            Log::info("File {$index} uploaded:", [
                'file_name' => $file->getClientOriginalName(),
                'path' => $path,
                'media_file_id' => $mediaFile->mf_id
            ]);
        }

        $courseId = $request->course_id ?? ($media->lesson->l_c_id ?? null);

        if (!$courseId) {
            Log::error('Course ID not found for media:', ['media_id' => $media->m_id]);
            return redirect()->route('courses.index')->with('error', 'ไม่พบรหัสคอร์ส');
        }

        Log::info('=== MediaController store END ===', ['media_id' => $media->m_id, 'course_id' => $courseId]);

        return redirect()->route('courses.show', ['id' => $courseId])
                 ->with('success', 'บันทึกสื่อการสอนเรียบร้อยแล้ว');
    }

    // สร้างบทเรียนใหม่ (สำหรับ modal)
    public function storeLesson(Request $request)
    {
        Log::info('=== Store lesson START ===');
        Log::info('Store lesson request:', $request->all());

        if ($request->l_c_id) {
            $course = Course::findOrFail($request->l_c_id);
            $this->authorize('manageContent', $course);
        }
        $request->validate([
            'l_name' => 'required|string|max:50',
            'l_c_id' => 'nullable|exists:courses,c_id',
            'l_description' => 'nullable|string',
            'l_status' => 'nullable|in:open,closed,draft',
            'l_index' => 'nullable|string',
        ]);

        $lesson = Lesson::create([
            'l_name' => $request->l_name,
            'l_description' => $request->l_description ?? null,
            'l_status' => $request->l_status ?? 'draft',
            'l_index' => $request->l_index ?? 0,
            'l_c_id' => $request->l_c_id ?? null,
        ]);

        Log::info('Lesson created:', ['lesson_id' => $lesson->l_id, 'lesson_name' => $lesson->l_name]);

        return response()->json([
            'l_id' => $lesson->l_id,
            'l_name' => $lesson->l_name,
        ]);
    }

    // หน้าแก้ไขสื่อ
    public function edit($id)
    {
        Log::info('=== MediaController edit START ===', ['media_id' => $id]);

        $media = Media::with('files')->findOrFail($id);

        // ใช้ course_id จาก media หรือจากบทเรียน
        $courseId = $media->m_c_id ?? ($media->lesson->l_c_id ?? null);

        Log::info('Media loaded for edit:', [
            'media_id' => $media->m_id,
            'media_name' => $media->m_name,
            'course_id' => $courseId,
            'files_count' => $media->files->count()
        ]);

        if (!$courseId) {
            Log::error('Course ID not found for media edit:', ['media_id' => $id]);
            return redirect()->route('courses.index')
                            ->with('error', 'ไม่พบรหัสคอร์ส');
        }

        $course = Course::findOrFail($courseId);
        $this->authorize('manageContent', $course);

        $lessons = Lesson::where('l_c_id', $courseId)->get();

        Log::info('=== MediaController edit END ===');

        return view('education.medias.edit', compact('media', 'lessons', 'courseId'));
    }

    // อัปเดตสื่อ
    public function update(Request $request, $id)
    {
        Log::info('=== MediaController update START ===');
        Log::info('Media ID:', ['id' => $id]);
        Log::info('Request method:', ['method' => $request->method()]);
        Log::info('All request data:', $request->all());
        Log::info('Delete files from request:', ['delete_files' => $request->get('delete_files', [])]);
        Log::info('Delete files count:', ['count' => count($request->get('delete_files', []))]);
        Log::info('Has new files:', ['has_files' => $request->hasFile('files')]);
        Log::info('New files count:', ['files_count' => $request->hasFile('files') ? count($request->file('files')) : 0]);

        $media = Media::findOrFail($id);

        $courseId = $media->m_c_id ?? ($media->lesson->l_c_id ?? null);

        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $this->authorize('manageContent', $course);
        }

        // Validation
        $maxKb = (int) config('media.max_upload_mb', 3072) * 1024; // convert MB → KB for Laravel 'max'
        $request->validate([
            'm_name' => 'required|string|max:50',
            'm_l_id' => 'nullable|exists:lessons,l_id',
            'm_desc' => 'nullable|string|max:200',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,svg,mp4,mov,avi,mkv,webm,wmv,flv,3gp|max:' . $maxKb,
            'delete_files' => 'nullable|array',
            'delete_files.*' => 'integer|exists:media_files,mf_id'
        ]);

        Log::info('Validation passed for update');

        // อัพเดทข้อมูลหลัก
        $oldData = [
            'm_name' => $media->m_name,
            'm_desc' => $media->m_desc,
            'm_l_id' => $media->m_l_id
        ];

        $media->update([
            'm_name' => $request->m_name,
            'm_desc' => $request->m_desc,
            'm_l_id' => $request->m_l_id ?: null,
        ]);

        Log::info('Media updated:', [
            'media_id' => $media->m_id,
            'old_data' => $oldData,
            'new_data' => [
                'm_name' => $media->m_name,
                'm_desc' => $media->m_desc,
                'm_l_id' => $media->m_l_id
            ]
        ]);

        // ลบไฟล์ที่ถูกเลือกให้ลบ
        $deleteFiles = $request->get('delete_files', []);
        Log::info('Processing file deletions:', [
            'delete_files_array' => $deleteFiles,
            'is_array' => is_array($deleteFiles),
            'count' => count($deleteFiles),
            'array_values' => array_values($deleteFiles)
        ]);

        if (!empty($deleteFiles)) {
            Log::info('Starting file deletion process...');

            foreach ($deleteFiles as $index => $fileId) {
                Log::info("Processing deletion for file {$index}:", ['file_id' => $fileId, 'type' => gettype($fileId)]);

                // ค้นหาไฟล์ที่ต้องลบ
                $file = $media->files()->where('mf_id', $fileId)->first();

                if ($file) {
                    Log::info('File found for deletion:', [
                        'file_id' => $file->mf_id,
                        'file_name' => $file->mf_original_name,
                        'file_path' => $file->mf_path,
                        'media_id' => $file->mf_m_id
                    ]);

                    // ลบไฟล์จาก storage
                    if ($file->mf_path && Storage::disk(config('media.disk', 'public'))->exists($file->mf_path)) {
                        $deleteResult = Storage::disk(config('media.disk', 'public'))->delete($file->mf_path);
                        Log::info('File deletion from storage:', [
                            'path' => $file->mf_path,
                            'success' => $deleteResult
                        ]);
                    } else {
                        Log::warning('File not found in storage or path empty:', [
                            'path' => $file->mf_path,
                            'exists' => $file->mf_path ? Storage::disk(config('media.disk', 'public'))->exists($file->mf_path) : false
                        ]);
                    }

                    // ลบ record จากฐานข้อมูล
                    $deleteResult = $file->delete();
                    Log::info('File record deletion from database:', [
                        'file_id' => $fileId,
                        'success' => $deleteResult
                    ]);
                } else {
                    Log::warning('File not found in media files:', [
                        'file_id' => $fileId,
                        'media_id' => $media->m_id,
                        'available_files' => $media->files()->pluck('mf_id')->toArray()
                    ]);

                }
            }

            // ตรวจสอบไฟล์ที่เหลือหลังการลบ
            $remainingFiles = $media->files()->get();
            Log::info('Files remaining after deletion:', [
                'count' => $remainingFiles->count(),
                'file_ids' => $remainingFiles->pluck('mf_id')->toArray()
            ]);

        } else {
            Log::info('No files marked for deletion');
        }

        // อัปโหลดไฟล์ใหม่
        if ($request->hasFile('files')) {
            $newFiles = $request->file('files');
            $newFilesCount = count($newFiles);
            Log::info('Processing new file uploads:', ['count' => $newFilesCount]);

            foreach ($newFiles as $index => $uploadedFile) {
                Log::info("Uploading file {$index}:", [
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'size' => $uploadedFile->getSize(),
                    'mime_type' => $uploadedFile->getClientMimeType()
                ]);

                $path = $uploadedFile->store('media', config('media.disk', 'public'));

                $mediaFile = $media->files()->create([
                    'mf_m_id' => $media->m_id,
                    'mf_original_name' => $uploadedFile->getClientOriginalName(),
                    'mf_path' => $path,
                    'mf_type' => $uploadedFile->getClientMimeType(),
                    'mf_size' => (int) ($uploadedFile->getSize() / 1024),
                ]);

                Log::info("File {$index} uploaded successfully:", [
                    'file_name' => $uploadedFile->getClientOriginalName(),
                    'path' => $path,
                    'media_file_id' => $mediaFile->mf_id,
                    'size_kb' => $mediaFile->mf_size
                ]);
            }
        } else {
            Log::info('No new files to upload');
        }

        // ตรวจสอบไฟล์ทั้งหมดหลังการอัปเดต
        $finalFiles = $media->fresh()->files;
        Log::info('Final files after update:', [
            'total_count' => $finalFiles->count(),
            'file_list' => $finalFiles->map(function($file) {
                return [
                    'id' => $file->mf_id,
                    'name' => $file->mf_original_name,
                    'path' => $file->mf_path
                ];
            })->toArray()
        ]);

        Log::info('=== MediaController update END ===', [
            'media_id' => $id,
            'course_id' => $courseId,
            'success' => true
        ]);

        return redirect()->route('courses.show', ['id' => $courseId])
                ->with('success', 'อัปเดตสื่อเรียบร้อยแล้ว');
    }

    // ลบสื่อ
    public function destroy($id)
    {
        Log::info('=== MediaController destroy START ===', ['media_id' => $id]);

        $media = Media::with('files')->findOrFail($id);

        $courseId = $media->m_c_id ?? ($media->lesson->l_c_id ?? null);
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $this->authorize('manageContent', $course);
        }

        Log::info('Media found for deletion:', [
            'media_id' => $media->m_id,
            'media_name' => $media->m_name,
            'files_count' => $media->files->count(),
            'course_id' => $courseId
        ]);

        // ลบไฟล์จริงจาก storage
        foreach ($media->files as $file) {
            if ($file->mf_path) {
                $deleteResult = Storage::disk(config('media.disk', 'public'))->delete($file->mf_path);
                Log::info('File deleted from storage:', [
                    'file_id' => $file->mf_id,
                    'path' => $file->mf_path,
                    'success' => $deleteResult
                ]);
            }
        }

        // ลบข้อมูลไฟล์
        $deletedFilesCount = $media->files()->count();
        $media->files()->delete();
        Log::info('Media files deleted from database:', ['count' => $deletedFilesCount]);

        // ลบ Media
        $media->delete();
        Log::info('Media deleted from database:', ['media_id' => $id]);

        Log::info('=== MediaController destroy END ===', ['success' => true]);

        // redirect กลับไปยัง course
        return redirect()->route('courses.show', ['id' => $courseId])
                        ->with('success', 'ลบสื่อเรียบร้อยแล้ว');
    }
}
