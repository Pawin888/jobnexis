<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    // แสดงหน้าใบประกาศ (ของผู้ใช้ที่ล็อกอิน)
    public function index()
    {
        try {
            $certificates = Certificate::where('cer_u_id', Auth::id())->get();
            return view('admin.edit-jobber', compact('certificates'));
        } catch (\Throwable $e) {
            Log::error('Certificate index failed', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return back()->withErrors(['cert_index' => 'ไม่สามารถโหลดรายการใบประกาศได้'])->withInput();
        }
    }

    // เพิ่มใบประกาศ (admin สามารถส่ง $userId เพื่อเพิ่มให้ผู้ใช้เป้าหมาย)
    public function store(Request $request, $userId = null)
    {
        try {
            // ถ้าเป็นการโพสต์ในเส้นทางของ admin แต่ผู้ใช้ไม่ใช่ admin → กันไว้
            if (!is_null($userId) && (!Auth::user() || Auth::user()->role !== 'admin')) {
                abort(403, 'คุณไม่มีสิทธิ์เพิ่มใบประกาศให้ผู้ใช้อื่น');
            }

            $data = $request->validate([
                'cer_name'           => ['required','string','max:255'],
                'cer_institute_name' => ['required','string','max:255'],
                'cer_ref_number'     => ['nullable','string','max:255'],
                'cer_image'          => ['required','image','mimes:jpg,jpeg,png,webp','max:4096'],
            ]);

            $targetUserId = $userId ?? Auth::id();

            DB::beginTransaction();

            // อัปโหลดไฟล์ไปที่ storage/app/public/certificates
            $storedPath = $request->file('cer_image')->store('certificates', 'public'); // ex: certificates/abc123.jpg

            Certificate::create([
                'cer_u_id'           => $targetUserId,
                'cer_name'           => $data['cer_name'],
                'cer_institute_name' => $data['cer_institute_name'],
                'cer_ref_number'     => $data['cer_ref_number'] ?? null,
                'cer_image_path'     => $storedPath,
                'cer_publiced'       => false,
                'cer_from_lesson'    => false,
            ]);

            DB::commit();

            return back()->with('success', 'เพิ่มใบประกาศสำเร็จ');
        } catch (\Throwable $e) {
            DB::rollBack();

            // ถ้าบันทึกไฟล์ไปแล้วแต่ล้มเหลวระหว่างกลาง ลองลบไฟล์ที่เพิ่งอัปโหลดออก
            if (isset($storedPath) && !empty($storedPath)) {
                try { Storage::disk('public')->delete($storedPath); } catch (\Throwable $ignore) {}
            }

            Log::error('Certificate store failed', [
                'user_id' => Auth::id(),
                'target_user_id' => $userId,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()->withErrors(['cert_store' => 'บันทึกใบประกาศไม่สำเร็จ: '.$e->getMessage()])->withInput();
        }
    }

    // เปลี่ยนสถานะเผยแพร่/ซ่อน
    public function toggle($id)
    {
        try {
            $certificate = Certificate::findOrFail($id);

            // อนุญาตเฉพาะเจ้าของ หรือ admin
            if (Auth::id() !== $certificate->cer_u_id && Auth::user()->role !== 'admin') {
                abort(403, 'คุณไม่มีสิทธิ์แก้ไขใบประกาศนี้');
            }

            $certificate->cer_publiced = !$certificate->cer_publiced;
            $certificate->save();

            return back()->with('success', 'อัปเดตสถานะใบประกาศเรียบร้อย');
        } catch (\Throwable $e) {
            Log::error('Certificate toggle failed', [
                'user_id' => Auth::id(),
                'cert_id' => $id,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()->withErrors(['cert_toggle' => 'สลับสถานะไม่สำเร็จ'])->withInput();
        }
    }

    // ลบ (เฉพาะใบที่ import มา)
    public function destroy($id)
    {
        try {
            $certificate = Certificate::findOrFail($id);

            // อนุญาตเฉพาะเจ้าของ หรือ admin
            if (Auth::id() !== $certificate->cer_u_id && Auth::user()->role !== 'admin') {
                abort(403, 'คุณไม่มีสิทธิ์ลบใบประกาศนี้');
            }

            if (!$certificate->cer_from_lesson) {
                // ถ้าไม่ได้เป็น URL ภายนอก ให้ลบไฟล์ใน storage ด้วย
                $path = $certificate->cer_image_path;
                $isUrl = filter_var($path, FILTER_VALIDATE_URL);

                DB::beginTransaction();

                if (!$isUrl && !empty($path)) {
                    try { Storage::disk('public')->delete($path); } catch (\Throwable $ignore) {}
                }

                $certificate->delete();

                DB::commit();
            }

            return back()->with('success', 'ลบใบประกาศเรียบร้อย');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Certificate destroy failed', [
                'user_id' => Auth::id(),
                'cert_id' => $id,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()->withErrors(['cert_destroy' => 'ลบใบประกาศไม่สำเร็จ'])->withInput();
        }
    }
}
