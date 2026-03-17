@csrf
@if(isset($resume))
    @method('PUT')
@endif

@php
    $oldSkills = old('skills', isset($resume)
        ? $resume->resumeSkills->map(fn ($rs) => [
            'skill_group_id' => $rs->skill_group_id,
            'skill_id' => $rs->skill_id,
            'proficiency_level' => $rs->proficiency_level,
        ])->toArray()
        : []
    );

    $oldWorkExperiences = old('work_experiences', isset($resume)
        ? $resume->workExperiences->map(fn($we) => [
            'job_title' => $we->job_title,
            'company_name' => $we->company_name,
            'start_date' => $we->start_date,
            'end_date' => $we->end_date,
            'is_current' => $we->is_current,
            'description' => $we->description,
        ])->toArray()
        : []
    );

    $oldEducations = old('educations', isset($resume)
        ? $resume->educations->map(fn($ed) => [
            'education_level' => $ed->education_level,
            'field_of_study' => $ed->field_of_study,
            'institution' => $ed->institution,
            'start_year' => $ed->start_year,
            'end_year' => $ed->end_year,
        ])->toArray()
        : []
    );

    $oldCertificates = old('certificates', isset($resume)
        ? $resume->certificates->map(fn($cert) => [
            'name' => $cert->name,
            'issued_by' => $cert->issued_by,
            'issued_year' => $cert->issued_year,
            'file_path' => $cert->file_path,
        ])->toArray()
        : []
    );

    $oldLanguages = old('languages', isset($resume)
        ? $resume->languages->map(fn($lang) => [
            'language' => $lang->language,
            'level' => $lang->level,
        ])->toArray()
        : []
    );
@endphp

{{-- Error Alert Box --}}
@if($errors->any())
<div id="error-alert" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-red-800 font-medium mb-2">พบข้อผิดพลาดในการกรอกข้อมูล:</h3>
            <ul class="list-disc list-inside text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" onclick="document.getElementById('error-alert').remove()" class="flex-shrink-0 ml-4 text-red-500 hover:text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
@endif

{{-- Validation Error Box (Client-side) --}}
<div id="validation-errors" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg hidden">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-red-800 font-medium mb-2">กรุณากรอกข้อมูลต่อไปนี้:</h3>
            <ul id="validation-error-list" class="list-disc list-inside text-red-700 space-y-1"></ul>
        </div>
        <button type="button" onclick="document.getElementById('validation-errors').classList.add('hidden')" class="flex-shrink-0 ml-4 text-red-500 hover:text-red-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>

<div id="submit-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m9 0A9 9 0 1112 3a9 9 0 019 9z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">ยืนยันการบันทึกเรซูเม่</h3>
                <p class="text-sm text-gray-500">กรุณาตรวจสอบความถูกต้องก่อนบันทึก</p>
            </div>
        </div>
        <p class="text-gray-600 mb-6">{{ isset($resume) ? 'คุณแน่ใจหรือว่าต้องการบันทึกการแก้ไขเรซูเม่นี้?' : 'คุณแน่ใจหรือว่าต้องการสร้างเรซูเม่นี้?' }}</p>
        <div class="flex gap-3 justify-end">
            <button type="button" id="cancel-submit-confirm" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                ยกเลิก
            </button>
            <button type="button" id="confirm-submit-confirm" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                ยืนยันการบันทึก
            </button>
        </div>
    </div>
</div>

{{-- Tab Navigation --}}
<div class="bg-white rounded-lg shadow-sm border mb-3 overflow-hidden">
    <div class="flex">
        <button type="button" class="tab-button active flex-1 border-r border-gray-200 last:border-r-0" data-tab="personal">
            <div class="flex items-center justify-center gap-2 px-4 py-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-sm font-medium">ข้อมูลส่วนตัว</span>
            </div>
        </button>
        <button type="button" class="tab-button flex-1 border-r border-gray-200 last:border-r-0" data-tab="experience">
            <div class="flex items-center justify-center gap-2 px-4 py-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm font-medium">ประสบการณ์</span>
            </div>
        </button>
        <button type="button" class="tab-button flex-1 border-r border-gray-200 last:border-r-0" data-tab="skills">
            <div class="flex items-center justify-center gap-2 px-4 py-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span class="text-sm font-medium">ทักษะ</span>
            </div>
        </button>
        <button type="button" class="tab-button flex-1 border-r border-gray-200 last:border-r-0" data-tab="education">
            <div class="flex items-center justify-center gap-2 px-4 py-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="text-sm font-medium">การศึกษา</span>
            </div>
        </button>
        <button type="button" class="tab-button flex-1" data-tab="application">
            <div class="flex items-center justify-center gap-2 px-4 py-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-medium">ข้อมูลการสมัคร</span>
            </div>
        </button>
    </div>
</div>

{{-- Tab 1: Personal Information --}}
<div class="tab-content active" data-content="personal">
    <div class="section-card">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            ข้อมูลส่วนตัว
        </h2>

        {{-- Profile Image --}}
        <div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">รูปโปรไฟล์</label>
    <div class="flex items-center gap-4">
        <div class="relative">
            @if(isset($resume->profile_image) && $resume->profile_image)
                {{-- กรณีมีรูปเดิม --}}
                <img id="preview-image" src="{{ asset('storage/' . $resume->profile_image) }}" alt="Profile" class="w-24 h-24 object-cover rounded-full border-4 border-gray-200">
                <div id="preview-placeholder" class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center border-4 border-gray-300 hidden">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            @else
                {{-- กรณีไม่มีรูปเดิม --}}
                <div id="preview-placeholder" class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center border-4 border-gray-300">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <img id="preview-image" src="" alt="Profile" class="w-24 h-24 object-cover rounded-full border-4 border-gray-200 hidden">
            @endif
        </div>
        <div class="flex-1">
            <input id="profile-image" type="file" name="profile_image" accept="image/*" class="hidden">
            <label for="profile-image" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm text-gray-700">เลือกรูปภาพ</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 2MB</p>
        </div>
    </div>
</div>

        {{-- Name Fields --}}
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อ <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" placeholder="ชื่อ" value="{{ old('first_name', $resume->first_name ?? '') }}" required class="input @error('first_name') border-red-500 @enderror">
                    @error('first_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อกลาง</label>
                    <input type="text" name="middle_name" placeholder="ชื่อกลาง (ถ้ามี)" value="{{ old('middle_name', $resume->middle_name ?? '') }}" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">นามสกุล <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" placeholder="นามสกุล" value="{{ old('last_name', $resume->last_name ?? '') }}" required class="input @error('last_name') border-red-500 @enderror">
                    @error('last_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Birth Date & Gender --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">วันเกิด <span class="text-red-500">*</span></label>
                <input type="date" name="birth_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('birth_date', isset($resume->birth_date) ? \Carbon\Carbon::parse($resume->birth_date)->format('Y-m-d') : '') }}" required class="input @error('birth_date') border-red-500 @enderror">
                @error('birth_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">เพศ <span class="text-red-500">*</span></label>
                <select name="gender" class="input @error('gender') border-red-500 @enderror" required style="font-size: 1rem !important; line-height: 1.5rem !important; height: 2.75rem !important; padding: 0.75rem !important;">
                    <option value="">-- เลือกเพศ --</option>
                    <option value="male" {{ old('gender', $resume->gender ?? '') == 'male' ? 'selected' : '' }}>ชาย</option>
                    <option value="female" {{ old('gender', $resume->gender ?? '') == 'female' ? 'selected' : '' }}>หญิง</option>
                    <option value="other" {{ old('gender', $resume->gender ?? '') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                </select>
                @error('gender')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="mb-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">อีเมล <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <input type="email" name="email" placeholder="อีเมล" value="{{ old('email', $resume->email ?? '') }}" required class="input pl-10 @error('email') border-red-500 @enderror">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">เบอร์ติดต่อ <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <input type="text" name="phone" placeholder="เบอร์ติดต่อ" value="{{ old('phone', $resume->phone ?? '') }}" required inputmode="numeric" maxlength="10" class="input pl-10 @error('phone') border-red-500 @enderror">
                    </div>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">เกี่ยวกับตัวคุณ</label>
            <textarea name="summary" rows="4" class="input" placeholder="แนะนำตัวคุณเองสั้นๆ เช่น ความสนใจ จุดเด่น หรือเป้าหมายในการทำงาน...">{{ old('summary', $resume->summary ?? '') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">ควรมีความยาวประมาณ 2-3 ประโยค</p>
        </div>
    </div>
</div>

{{-- Tab 2: Work Experience --}}
<div class="tab-content" data-content="experience">
    <div class="section-card">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            ประสบการณ์ทำงาน
        </h2>
        <p class="text-gray-600 mb-4">เพิ่มประสบการณ์การทำงานของคุณ (เริ่มจากประสบการณ์ล่าสุด)</p>

        <div id="work-container" class="space-y-4"></div>

        <button type="button" id="add-work" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            เพิ่มประสบการณ์
        </button>
    </div>
</div>

{{-- Tab 3: Skills --}}
<div class="tab-content" data-content="skills">
    <div class="section-card">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            ทักษะความสามารถ
        </h2>
        <p class="text-gray-600 mb-4">ระบุทักษะและระดับความชำนาญของคุณ</p>

        <div id="skills-container" class="space-y-4"></div>

        <button type="button" id="add-skill" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            เพิ่มทักษะ
        </button>
    </div>
</div>

{{-- Tab 4: Education --}}
<div class="tab-content" data-content="education">
    <div class="section-card mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            ประวัติการศึกษา
        </h2>
        <p class="text-gray-600 mb-4">เพิ่มประวัติการศึกษาของคุณ (เริ่มจากล่าสุด)</p>

        <div id="education-container" class="space-y-4"></div>

        <button type="button" id="add-education" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            เพิ่มการศึกษา
        </button>
    </div>

    <div class="section-card mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
            ใบรับรอง/ประกาศนียบัตร
        </h2>
        <p class="text-gray-600 mb-4">เพิ่มใบรับรองหรือประกาศนียบัตรที่เกี่ยวข้อง</p>

        <div id="certificate-container" class="space-y-4"></div>

        <button type="button" id="add-certificate" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            เพิ่มใบรับรอง
        </button>
    </div>

    <div class="section-card">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
            </svg>
            ทักษะด้านภาษา
        </h2>
        <p class="text-gray-600 mb-4">ระบุภาษาที่คุณสามารถใช้ได้และระดับความสามารถ</p>

        <div id="language-container" class="space-y-4"></div>

        <button type="button" id="add-language" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            เพิ่มภาษา
        </button>
    </div>
</div>

{{-- Tab 5: Application Details --}}
<div class="tab-content" data-content="application">
    <div class="section-card">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            ข้อมูลการสมัครงาน
        </h2>
        <p class="text-gray-600 mb-6">ระบุรายละเอียดเกี่ยวกับความต้องการในการทำงาน</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">เริ่มงานได้เมื่อ</label>
                <input type="date" name="available_start_date" min="{{ now()->format('Y-m-d') }}" value="{{ old('available_start_date', $resume->available_start_date ?? '') }}" class="input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">สถานที่ทำงานที่ต้องการ</label>
                <select name="preferred_location" class="input">
                    <option value="">-- เลือกจังหวัด --</option>
                    @foreach(config('th_provinces', []) as $province)
                        <option value="{{ $province }}" {{ old('preferred_location', $resume->preferred_location ?? '') == $province ? 'selected' : '' }}>{{ $province }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">ช่วงเงินเดือนที่คาดหวัง (บาท)</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">ขั้นต่ำ</label>
                    <select name="salary_min" class="input">
                        <option value="">ไม่ระบุ</option>
                        @foreach([10000,15000,20000,25000,30000,35000,40000,45000,50000,60000,70000,80000,100000,120000,150000,200000] as $s)
                        <option value="{{ $s }}" {{ old('salary_min', $resume->salary_min ?? '') == $s ? 'selected' : '' }}>{{ number_format($s) }} บาท</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">สูงสุด</label>
                    <select name="salary_max" class="input">
                        <option value="">ไม่ระบุ</option>
                        @foreach([10000,15000,20000,25000,30000,35000,40000,45000,50000,60000,70000,80000,100000,120000,150000,200000] as $s)
                        <option value="{{ $s }}" {{ old('salary_max', $resume->salary_max ?? '') == $s ? 'selected' : '' }}>{{ number_format($s) }} บาท</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">เลือกช่วงเงินเดือนที่คุณต้องการ หรือเว้นว่างไว้หากยังไม่ระบุ</p>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="hidden" name="is_visible" value="0">
                <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $resume->is_visible ?? true) ? 'checked' : '' }} class="mt-1">
                <div>
                    <span class="font-medium text-gray-800">เปิดเผยเรซูเม่ต่อสาธารณะ</span>
                    <p class="text-sm text-gray-600 mt-1">หากเปิดเผย นายจ้างจะสามารถค้นหาและดูเรซูเม่ของคุณได้</p>
                </div>
            </label>
        </div>
    </div>
</div>

<script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        const skillGroups = @json($skillGroups);
        const oldSkills = @json($oldSkills);
        const oldWorkExperiences = @json($oldWorkExperiences);
        const oldEducations = @json($oldEducations);
        const oldCertificates = @json($oldCertificates);
        const oldLanguages = @json($oldLanguages);

        const skillContainer = document.getElementById('skills-container');
        const addSkillBtn = document.getElementById('add-skill');
        const workContainer = document.getElementById('work-container');
        const addWorkBtn = document.getElementById('add-work');
        const educationContainer = document.getElementById('education-container');
        const addEducationBtn = document.getElementById('add-education');
        const certificateContainer = document.getElementById('certificate-container');
        const addCertificateBtn = document.getElementById('add-certificate');
        const languageContainer = document.getElementById('language-container');
        const addLanguageBtn = document.getElementById('add-language');
        const profileInput = document.getElementById('profile-image');
        const submitConfirmModal = document.getElementById('submit-confirm-modal');
        const submitConfirmButton = document.getElementById('confirm-submit-confirm');
        const submitCancelButton = document.getElementById('cancel-submit-confirm');
        const resumeForm = document.getElementById('resume-form');
        let isSubmitConfirmed = false;

    const worldLanguagesThai = [
        'ภาษาไทย', 'ภาษาอังกฤษ', 'ภาษาจีนกลาง', 'ภาษาจีนกวางตุ้ง', 'ภาษาญี่ปุ่น', 'ภาษาเกาหลี',
        'ภาษาฝรั่งเศส', 'ภาษาเยอรมัน', 'ภาษาสเปน', 'ภาษาโปรตุเกส', 'ภาษาอิตาลี', 'ภาษาดัตช์',
        'ภาษารัสเซีย', 'ภาษายูเครน', 'ภาษาโปแลนด์', 'ภาษาเช็ก', 'ภาษาสโลวัก', 'ภาษาฮังการี',
        'ภาษาโรมาเนีย', 'ภาษาบัลแกเรีย', 'ภาษาเซอร์เบีย', 'ภาษาโครเอเชีย', 'ภาษาสโลวีเนีย',
        'ภาษาบอสเนีย', 'ภาษาแอลเบเนีย', 'ภาษากรีก', 'ภาษาตุรกี', 'ภาษาอาหรับ', 'ภาษาฮีบรู',
        'ภาษาเปอร์เซีย', 'ภาษาอูรดู', 'ภาษาฮินดี', 'ภาษาเบงกาลี', 'ภาษาปัญจาบ', 'ภาษาคุชราตี',
        'ภาษามราฐี', 'ภาษาทมิฬ', 'ภาษาเตลูกู', 'ภาษากันนาดา', 'ภาษามาลายาลัม', 'ภาษาสิงหล',
        'ภาษาเนปาลี', 'ภาษาพม่า', 'ภาษามลายู', 'ภาษาอินโดนีเซีย', 'ภาษาตากาล็อก', 'ภาษาเวียดนาม',
        'ภาษาลาว', 'ภาษาเขมร', 'ภาษามองโกเลีย', 'ภาษาคาซัค', 'ภาษาอุซเบก', 'ภาษาอาเซอร์ไบจาน',
        'ภาษาจอร์เจีย', 'ภาษาอาร์เมเนีย', 'ภาษาสวาฮีลี', 'ภาษาอัมฮาริก', 'ภาษาโซมาลี',
        'ภาษาโยรูบา', 'ภาษาอิกโบ', 'ภาษาเฮาซา', 'ภาษาแอฟริคานส์', 'ภาษาซูลู', 'ภาษาโคซา',
        'ภาษาเดนมาร์ก', 'ภาษานอร์เวย์', 'ภาษาสวีเดน', 'ภาษาฟินแลนด์', 'ภาษาไอซ์แลนด์',
        'ภาษาเอสโตเนีย', 'ภาษาลัตเวีย', 'ภาษาลิทัวเนีย', 'ภาษาไอริช', 'ภาษาเวลส์', 'ภาษาสก็อตเกลิก',
        'ภาษามอลตา', 'ภาษาคาตาลัน', 'ภาษาบาสก์', 'ภาษากาลิเซีย', 'ภาษาละติน',
        'ภาษากลาง (ลิงกวาฟรังกา)', 'ภาษานาวาโฮ', 'ภาษาอินุกติตุต', 'ภาษาเมารี', 'ภาษาฮาวาย',
        'ภาษาซามัว', 'ภาษาตองกา', 'ภาษาฟิจิ', 'ภาษาปาปิอาเมนโต', 'ภาษาเครโอลเฮติ',
        'ภาษาลักเซมเบิร์ก', 'ภาษามาซิโดเนีย', 'ภาษาเบลารุส', 'ภาษามอลโดวา', 'ภาษาคีร์กีซ',
        'ภาษาทาจิก', 'ภาษาเตอร์กเมน', 'ภาษาเคิร์ด', 'ภาษาอัสสัม', 'ภาษาโอเดีย', 'ภาษาสันสกฤต'
    ];

    // Salary min/max cross-filtering
    const salaryMinSel = document.querySelector('select[name="salary_min"]');
    const salaryMaxSel = document.querySelector('select[name="salary_max"]');
    function updateSalaryMaxOptions() {
        if (!salaryMinSel || !salaryMaxSel) return;
        const minVal = parseInt(salaryMinSel.value) || 0;
        Array.from(salaryMaxSel.options).forEach(opt => {
            if (opt.value === '') return;
            opt.disabled = parseInt(opt.value) <= minVal;
        });
        // If current max selection is now invalid, reset it
        if (salaryMaxSel.value && parseInt(salaryMaxSel.value) <= minVal) {
            salaryMaxSel.value = '';
        }
    }
    if (salaryMinSel) salaryMinSel.addEventListener('change', updateSalaryMaxOptions);
    updateSalaryMaxOptions();

    function buildYearOptions(selectedValue, allowFutureYears = false) {
        const selected = String(selectedValue ?? '');
        const currentBEYear = new Date().getFullYear() + 543;
        const maxYear = allowFutureYears ? currentBEYear + 5 : currentBEYear;
        let options = '<option value="">-- เลือกปี พ.ศ. --</option>';

        for (let year = maxYear; year >= 2500; year--) {
            const value = String(year);
            options += `<option value="${value}" ${selected === value ? 'selected' : ''}>${value}</option>`;
        }

        return options;
    }

    function getFileNameFromPath(path) {
        if (!path) return '';
        return path.split('/').pop();
    }

    // Profile Image Preview
   if (profileInput) {
    profileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('ขนาดไฟล์ต้องไม่เกิน 2MB');
            this.value = '';
            return;
        }

        // Validate file type
        if (!file.type.match('image/(jpeg|jpg|png)')) {
            alert('กรุณาเลือกไฟล์ JPG หรือ PNG เท่านั้น');
            this.value = '';
            return;
        }

        // แสดง preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-image').classList.remove('hidden');
            document.getElementById('preview-placeholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    });
}

    // Phone input: digits only, max 10 digits
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    }

    // Tab Navigation
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    const submitBtn = document.getElementById('submit-btn');
    let currentTab = 0;
    const tabs = ['personal', 'experience', 'skills', 'education', 'application'];

    function showTab(index) {
        tabContents.forEach(content => content.classList.remove('active'));
        tabButtons.forEach(btn => btn.classList.remove('active'));

        const targetTab = tabs[index];
        const targetContent = document.querySelector(`[data-content="${targetTab}"]`);
        const targetButton = document.querySelector(`[data-tab="${targetTab}"]`);

        if (targetContent) targetContent.classList.add('active');
        if (targetButton) targetButton.classList.add('active');

        currentTab = index;

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    tabButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            showTab(index);
        });
    });

    showTab(0);

    // Validation function
    function validateCurrentTab() {
        const errors = [];
        const currentTabName = tabs[currentTab];

        // Clear previous errors
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });

        if (currentTabName === 'personal') {
            // Get the personal tab content to ensure we're getting fields from the right place
            const personalTab = document.querySelector('[data-content="personal"]');

            const firstName = personalTab.querySelector('input[name="first_name"]');
            const lastName = personalTab.querySelector('input[name="last_name"]');
            const birthDate = personalTab.querySelector('input[name="birth_date"]');
            const gender = personalTab.querySelector('select[name="gender"]');
            const email = personalTab.querySelector('input[name="email"]');
            const phone = personalTab.querySelector('input[name="phone"]');

            console.log('=== Validation Debug ===');
            console.log('Personal Tab found:', !!personalTab);
            console.log('First Name:', firstName ? firstName.value : 'NOT FOUND');
            console.log('Last Name:', lastName ? lastName.value : 'NOT FOUND');
            console.log('Birth Date:', birthDate ? birthDate.value : 'NOT FOUND');
            console.log('Gender:', gender ? gender.value : 'NOT FOUND');
            console.log('Email:', email ? email.value : 'NOT FOUND');
            console.log('Email type:', email ? email.type : 'NOT FOUND');
            console.log('Phone:', phone ? phone.value : 'NOT FOUND');
            console.log('=======================');

            if (!firstName || !firstName.value.trim()) {
                errors.push('กรุณากรอกชื่อ');
                if (firstName) firstName.classList.add('border-red-500');
            }
            if (!lastName || !lastName.value.trim()) {
                errors.push('กรุณากรอกนามสกุล');
                if (lastName) lastName.classList.add('border-red-500');
            }
            if (!birthDate || !birthDate.value) {
                errors.push('กรุณาเลือกวันเกิด');
                if (birthDate) birthDate.classList.add('border-red-500');
            }
            if (!gender || !gender.value) {
                errors.push('กรุณาเลือกเพศ');
                if (gender) gender.classList.add('border-red-500');
            }
            if (!email || !email.value.trim()) {
                errors.push('กรุณากรอกอีเมล');
                if (email) email.classList.add('border-red-500');
                console.log('Email is empty!');
            } else {
                const emailValue = email.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                console.log('Checking email:', emailValue);
                console.log('Regex result:', emailRegex.test(emailValue));
                if (!emailRegex.test(emailValue)) {
                    errors.push('กรุณากรอกอีเมลให้ถูกต้อง (ตัวอย่าง: example@email.com)');
                    if (email) email.classList.add('border-red-500');
                }
            }
            if (!phone || !phone.value.trim()) {
                errors.push('กรุณากรอกเบอร์ติดต่อ');
                if (phone) phone.classList.add('border-red-500');
            }
        } else if (currentTabName === 'experience') {
            const workRows = workContainer ? workContainer.children : [];

            if (!workRows.length) {
                errors.push('กรุณาเพิ่มประสบการณ์ทำงานอย่างน้อย 1 รายการก่อนไปขั้นตอนทักษะ');
            } else {
                Array.from(workRows).forEach((row, idx) => {
                    const jobTitle = row.querySelector('input[name*="[job_title]"]');
                    const companyName = row.querySelector('input[name*="[company_name]"]');
                    const startDate = row.querySelector('input[name*="[start_date]"]');

                    if (!jobTitle || !jobTitle.value.trim()) {
                        errors.push(`ประสบการณ์ทำงานรายการที่ ${idx + 1}: กรุณากรอกตำแหน่งงาน`);
                        if (jobTitle) jobTitle.classList.add('border-red-500');
                    }
                    if (!companyName || !companyName.value.trim()) {
                        errors.push(`ประสบการณ์ทำงานรายการที่ ${idx + 1}: กรุณากรอกชื่อบริษัท/องค์กร`);
                        if (companyName) companyName.classList.add('border-red-500');
                    }
                    if (!startDate || !startDate.value) {
                        errors.push(`ประสบการณ์ทำงานรายการที่ ${idx + 1}: กรุณาเลือกวันที่เริ่มงาน`);
                        if (startDate) startDate.classList.add('border-red-500');
                    }
                });
            }
        } else if (currentTabName === 'skills') {
            const skillRows = skillContainer ? skillContainer.children : [];

            if (!skillRows.length) {
                errors.push('กรุณาเพิ่มทักษะอย่างน้อย 1 รายการก่อนไปขั้นตอนถัดไป');
            } else {
                Array.from(skillRows).forEach((row, idx) => {
                    const group = row.querySelector('select[name*="[skill_group_id]"]');
                    const skill = row.querySelector('select[name*="[skill_id]"]');
                    const level = row.querySelector('select[name*="[proficiency_level]"]');

                    if (!group || !group.value) {
                        errors.push(`ทักษะรายการที่ ${idx + 1}: กรุณาเลือกกลุ่มทักษะ`);
                        if (group) group.classList.add('border-red-500');
                    }
                    if (!skill || !skill.value) {
                        errors.push(`ทักษะรายการที่ ${idx + 1}: กรุณาเลือกทักษะ`);
                        if (skill) skill.classList.add('border-red-500');
                    }
                    if (!level || !level.value) {
                        errors.push(`ทักษะรายการที่ ${idx + 1}: กรุณาเลือกระดับความชำนาญ`);
                        if (level) level.classList.add('border-red-500');
                    }
                });
            }
        }

        if (errors.length > 0) {
            console.log('Validation errors:', errors);
            showValidationErrors(errors);
            return false;
        }

        hideValidationErrors();
        return true;
    }

    // Clear validation errors when user types
    function setupRealTimeValidation() {
        // Handle all required fields
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            field.addEventListener('input', function() {
                // Special handling for email
                if (this.name === 'email') {
                    const emailValue = this.value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailValue && emailRegex.test(emailValue)) {
                        this.classList.remove('border-red-500');
                    }
                } else if (this.value.trim()) {
                    this.classList.remove('border-red-500');
                }

                // Check if all errors are resolved
                const hasErrors = document.querySelectorAll('.border-red-500').length > 0;
                if (!hasErrors) {
                    hideValidationErrors();
                }
            });

            field.addEventListener('change', function() {
                // Special handling for email
                if (this.name === 'email') {
                    const emailValue = this.value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailValue && emailRegex.test(emailValue)) {
                        this.classList.remove('border-red-500');
                    }
                } else if (this.value.trim()) {
                    this.classList.remove('border-red-500');
                }

                const hasErrors = document.querySelectorAll('.border-red-500').length > 0;
                if (!hasErrors) {
                    hideValidationErrors();
                }
            });

            // Special blur event for email to show feedback
            if (field.name === 'email') {
                field.addEventListener('blur', function() {
                    const emailValue = this.value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailValue && !emailRegex.test(emailValue)) {
                        this.classList.add('border-red-500');
                        // Optionally show a tooltip or message
                    } else if (emailValue && emailRegex.test(emailValue)) {
                        this.classList.remove('border-red-500');
                    }
                });
            }
        });
    }

    // Call setup after a short delay to ensure all fields are rendered
    setTimeout(setupRealTimeValidation, 100);

    function showValidationErrors(errors) {
        const errorBox = document.getElementById('validation-errors');
        const errorList = document.getElementById('validation-error-list');

        errorList.innerHTML = '';
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });

        errorBox.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideValidationErrors() {
        document.getElementById('validation-errors').classList.add('hidden');
    }

    function openSubmitConfirmModal() {
    Swal.fire({
        title: '{{ isset($resume) ? "ยืนยันการบันทึกการแก้ไข?" : "ยืนยันการสร้างเรซูเม่" }}',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>สร้าง',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
    }).then(result => {
        if (result.isConfirmed) {
            isSubmitConfirmed = true;
            if (resumeForm) {
                resumeForm.requestSubmit();
            }
        }
    });
}

    function closeSubmitConfirmModal() {
        if (submitConfirmModal) {
            submitConfirmModal.style.display = 'none';
        }
    }

    if (submitCancelButton) {
        submitCancelButton.addEventListener('click', closeSubmitConfirmModal);
    }

    if (submitConfirmButton) {
        submitConfirmButton.addEventListener('click', function() {
            isSubmitConfirmed = true;
            closeSubmitConfirmModal();
            if (resumeForm) {
                resumeForm.requestSubmit();
            }
        });
    }

    if (submitConfirmModal) {
        submitConfirmModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeSubmitConfirmModal();
            }
        });
    }

    function getFieldLabel(field) {
        const name = field.name || '';

        const flatFieldLabels = {
            first_name: 'ชื่อ',
            middle_name: 'ชื่อกลาง',
            last_name: 'นามสกุล',
            birth_date: 'วันเกิด',
            gender: 'เพศ',
            email: 'อีเมล',
            phone: 'เบอร์ติดต่อ',
            summary: 'เกี่ยวกับตัวคุณ',
            available_start_date: 'วันที่พร้อมเริ่มงาน',
            preferred_location: 'สถานที่ทำงานที่ต้องการ',
            salary_min: 'เงินเดือนขั้นต่ำ',
            salary_max: 'เงินเดือนสูงสุด',
            is_visible: 'การเปิดเผยเรซูเม่',
            profile_image: 'รูปโปรไฟล์',
        };

        if (flatFieldLabels[name]) {
            return flatFieldLabels[name];
        }

        const groupedFieldLabels = {
            skills: {
                skill_group_id: 'กลุ่มทักษะ',
                skill_id: 'ทักษะ',
                proficiency_level: 'ระดับความชำนาญ',
            },
            work_experiences: {
                job_title: 'ตำแหน่งงาน',
                company_name: 'บริษัท/องค์กร',
                start_date: 'วันที่เริ่มงาน',
                end_date: 'วันที่สิ้นสุดงาน',
                description: 'รายละเอียดงาน',
            },
            educations: {
                education_level: 'ระดับการศึกษา',
                field_of_study: 'สาขาวิชา',
                institution: 'สถาบันการศึกษา',
                start_year: 'ปีที่เริ่มศึกษา',
                end_year: 'ปีที่จบการศึกษา',
            },
            certificates: {
                name: 'ชื่อใบรับรอง',
                issued_by: 'ออกโดย',
                issued_year: 'ปีที่ออกใบรับรอง',
                file: 'ไฟล์ใบรับรอง',
            },
            languages: {
                language: 'ภาษา',
                level: 'ระดับความสามารถ',
            },
        };

        const groupedNameMatch = name.match(/^([^\[]+)\[\d+]\[([^\]]+)]$/);
        if (groupedNameMatch) {
            const groupName = groupedNameMatch[1];
            const keyName = groupedNameMatch[2];
            if (groupedFieldLabels[groupName] && groupedFieldLabels[groupName][keyName]) {
                return groupedFieldLabels[groupName][keyName];
            }
        }

        const fieldBlock = field.closest('div');
        const label = fieldBlock ? fieldBlock.querySelector('label') : null;
        if (label) {
            return label.textContent.replace('*', '').trim();
        }

        return 'ข้อมูลที่จำเป็น';
    }

    // Form validation before submit
    document.getElementById('resume-form').addEventListener('submit', function(e) {
        // Clear previous error highlights
        document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
        hideValidationErrors();

        let hasError = false;
        const errors = [];
        const invalidFields = Array.from(this.querySelectorAll(':invalid')).filter(field => !field.disabled);

        invalidFields.forEach(field => {
            field.classList.add('border-red-500');
            const isSelect = field.tagName === 'SELECT';

            if (field.validity.valueMissing) {
                errors.push(`${isSelect ? 'กรุณาเลือก' : 'กรุณากรอก'}${getFieldLabel(field)}`);
                return;
            }

            errors.push(`กรุณากรอก${getFieldLabel(field)}ให้ถูกต้อง`);
        });

        const phone = this.querySelector('input[name="phone"]');
        if (phone && phone.value && phone.value.length !== 10) {
            phone.classList.add('border-red-500');
            errors.push('เบอร์ติดต่อจะต้องเป็นตัวเลข 10 หลัก');
        }

        const salaryMin = this.querySelector('select[name="salary_min"]');
        const salaryMax = this.querySelector('select[name="salary_max"]');
        if (salaryMin && salaryMax && salaryMin.value && salaryMax.value) {
            if (parseInt(salaryMax.value) <= parseInt(salaryMin.value)) {
                salaryMin.classList.add('border-red-500');
                salaryMax.classList.add('border-red-500');
                errors.push('เงินเดือนสูงสุดต้องมากกว่าเงินเดือนขั้นต่ำ');
            }
        }

        if (invalidFields.length > 0 || errors.length > 0) {
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();

            const uniqueErrors = [...new Set(errors)];
            showValidationErrors(uniqueErrors);

            const firstInvalidField = invalidFields[0];
            if (firstInvalidField) {
                const tabContent = firstInvalidField.closest('.tab-content');
                if (tabContent && tabContent.dataset.content) {
                    const tabIndex = tabs.indexOf(tabContent.dataset.content);
                    if (tabIndex >= 0) {
                        showTab(tabIndex);
                    }
                }
            } else {
                showTab(2);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
            return false;
        }

        // Check for uploading files
        let hasInvalidFile = false;
        document.querySelectorAll('input[type="file"]').forEach(f => {
            if (f.files.length > 0) {
        const file = f.files[0];
        // ตรวจสอบเฉพาะขนาดไฟล์
        if (f.name === 'profile_image' && file.size > 2 * 1024 * 1024) {
            hasInvalidFile = true;
            alert('รูปโปรไฟล์มีขนาดเกิน 2MB');
        }
    }
});

if (hasInvalidFile) {
    e.preventDefault();
    return false;
}

        if (!isSubmitConfirmed) {
            e.preventDefault();
            openSubmitConfirmModal();
            return false;
        }

        isSubmitConfirmed = false;

        // Show loading state
        const submitButton = document.getElementById('submit-btn');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="flex items-center gap-2"><svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>กำลังบันทึก...</span>';
        }
    });

    // Skill Row with validation
    function skillRow(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">กลุ่มทักษะ <span class="text-red-500">*</span></label>
                    <select name="skills[${index}][skill_group_id]" class="input group" required style="font-size: 1rem !important; line-height: 1.5rem !important; height: 2.75rem !important; padding: 0.75rem !important;">
                        <option value="">-- เลือกกลุ่ม --</option>
                        ${skillGroups.map(g => `<option value="${g.id}" ${g.id == data.skill_group_id ? 'selected' : ''}>${g.name}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ทักษะ <span class="text-red-500">*</span></label>
                    <select name="skills[${index}][skill_id]" class="input skill" required style="font-size: 1rem !important; line-height: 1.5rem !important; height: 2.75rem !important; padding: 0.75rem !important;">
                        <option value="">-- เลือกทักษะ --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ระดับความชำนาญ <span class="text-red-500">*</span></label>
                    <select name="skills[${index}][proficiency_level]" class="input" required style="font-size: 1rem !important; line-height: 1.5rem !important; height: 2.75rem !important; padding: 0.75rem !important;">
                        <option value="">เลือกระดับ</option>
                        <option value="beginner" ${data.proficiency_level == 'beginner' ? 'selected' : ''}>เริ่มต้น</option>
                        <option value="intermediate" ${data.proficiency_level == 'intermediate' ? 'selected' : ''}>ปานกลาง</option>
                        <option value="advanced" ${data.proficiency_level == 'advanced' ? 'selected' : ''}>ขั้นสูง</option>
                        <option value="expert" ${data.proficiency_level == 'expert' ? 'selected' : ''}>ผู้เชี่ยวชาญ</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" class="remove w-full px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm">
                        <span class="flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            ลบ
                        </span>
                    </button>
                </div>
            </div>
        `;

        const groupSelect = div.querySelector('.group');
        const skillSelect = div.querySelector('.skill');

        function loadSkills() {
            const group = skillGroups.find(g => g.id == groupSelect.value);
            skillSelect.innerHTML = '<option value="">-- เลือกทักษะ --</option>';
            if (group && group.skills) {
                group.skills.forEach(s => {
                    skillSelect.innerHTML += `<option value="${s.id}" ${s.id == data.skill_id ? 'selected' : ''}>${s.name}</option>`;
                });
            }
        }

        groupSelect.addEventListener('change', loadSkills);
        loadSkills();
        div.querySelector('.remove').onclick = () => {
           Swal.fire({
            title: 'ยืนยันการลบ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) div.remove();
        });
    };
        return div;
    }

    // Work Experience Row with validation
    function workRow(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
        div.innerHTML = `
            <div class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">ตำแหน่งงาน <span class="text-red-500">*</span></label>
                        <input type="text" name="work_experiences[${index}][job_title]" placeholder="เช่น Software Engineer" value="${data.job_title ?? ''}" class="input" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">บริษัท/องค์กร <span class="text-red-500">*</span></label>
                        <input type="text" name="work_experiences[${index}][company_name]" placeholder="เช่น ABC Company" value="${data.company_name ?? ''}" class="input" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">วันที่เริ่มงาน <span class="text-red-500">*</span></label>
                        <input type="date" name="work_experiences[${index}][start_date]" value="${data.start_date ?? ''}" class="input start-date" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">วันที่สิ้นสุด <span class="text-red-500">*</span></label>
                        <input type="date" name="work_experiences[${index}][end_date]" value="${data.end_date ?? ''}" class="input end-date" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">รายละเอียดงาน</label>
                    <textarea name="work_experiences[${index}][description]" placeholder="อธิบายหน้าที่ความรับผิดชอบและผลงานที่สำคัญ..." class="input" rows="3">${data.description ?? ''}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="remove px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            ลบ
                        </span>
                    </button>
                </div>
            </div>
        `;

        const startDateInput = div.querySelector('.start-date');
        const endDateInput = div.querySelector('.end-date');
        const today = new Date().toISOString().slice(0, 10);

        function syncEndDateMin() {
            if (!startDateInput || !endDateInput) return;

            endDateInput.max = today;

            if (startDateInput.value) {
                endDateInput.min = startDateInput.value;
                if (endDateInput.value && endDateInput.value < startDateInput.value) {
                    endDateInput.value = '';
                }
                return;
            }

            endDateInput.removeAttribute('min');
        }

        if (startDateInput) {
            startDateInput.addEventListener('change', syncEndDateMin);
            startDateInput.addEventListener('input', syncEndDateMin);
        }

        syncEndDateMin();

        div.querySelector('.remove').onclick = () => {
            if (confirm('คุณต้องการลบประสบการณ์นี้หรือไม่?')) {
                div.remove();
            }
        };
        return div;
    }

    // Education Row with validation
    function educationRow(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ระดับการศึกษา <span class="text-red-500">*</span></label>
                    <input type="text" name="educations[${index}][education_level]" placeholder="เช่น ปริญญาตรี" value="${data.education_level ?? ''}" class="input" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">สาขาวิชา <span class="text-red-500">*</span></label>
                    <input type="text" name="educations[${index}][field_of_study]" placeholder="เช่น วิศวกรรมคอมพิวเตอร์" value="${data.field_of_study ?? ''}" class="input" required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">สถาบันการศึกษา <span class="text-red-500">*</span></label>
                    <input type="text" name="educations[${index}][institution]" placeholder="เช่น มหาวิทยาลัย..." value="${data.institution ?? ''}" class="input" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ปีที่เริ่มศึกษา <span class="text-red-500">*</span></label>
                    <select name="educations[${index}][start_year]" class="input" required>
                        ${buildYearOptions(data.start_year, true)}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ปีที่จบการศึกษา <span class="text-red-500">*</span></label>
                    <select name="educations[${index}][end_year]" class="input" required>
                        ${buildYearOptions(data.end_year)}
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" class="remove px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        ลบ
                    </span>
                </button>
            </div>
        `;
        div.querySelector('.remove').onclick = () => {
            if (confirm('คุณต้องการลบการศึกษานี้หรือไม่?')) {
                div.remove();
            }
        };
        return div;
    }

    // Certificate Row
    function certificateRow(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ชื่อใบรับรอง <span class="text-red-500">*</span></label>
                    <input type="text" name="certificates[${index}][name]" placeholder="เช่น AWS Certified" value="${data.name ?? ''}" class="input" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ออกโดย <span class="text-red-500">*</span></label>
                    <input type="text" name="certificates[${index}][issued_by]" placeholder="เช่น Amazon Web Services" value="${data.issued_by ?? ''}" class="input" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ปีที่ออกใบรับรอง <span class="text-red-500">*</span></label>
                    <select name="certificates[${index}][issued_year]" class="input" required>
                        ${buildYearOptions(data.issued_year)}
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1">ไฟล์ใบรับรอง (PDF, JPG, PNG) <span class="text-red-500">*</span></label>
                    <input type="hidden" name="certificates[${index}][existing_file_path]" value="${data.file_path ?? ''}">
                    <input id="certificate-file-${index}" type="file" name="certificates[${index}][file]" class="hidden certificate-file-input" accept=".pdf,.jpg,.jpeg,.png" ${data.file_path ? '' : 'required'}>
                    <div class="flex flex-col gap-2">
                        <label for="certificate-file-${index}" class="inline-flex w-fit items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition text-sm text-gray-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 10-5.656-5.656L5.757 10.757a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            เลือกไฟล์
                        </label>
                        <p class="certificate-file-name text-xs text-gray-500">${data.file_path ? `ไฟล์ปัจจุบัน: ${getFileNameFromPath(data.file_path)}` : 'ยังไม่ได้เลือกไฟล์'}</p>
                        ${data.file_path ? `<a href="/storage/${data.file_path}" target="_blank" class="text-xs text-blue-600 hover:underline inline-block">ดูไฟล์เดิม</a>` : ''}
                    </div>
                </div>
                <button type="button" class="remove ml-3 px-3 py-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        ลบ
                    </span>
                </button>
            </div>
        `;
        const fileInput = div.querySelector('.certificate-file-input');
        const fileNameText = div.querySelector('.certificate-file-name');

        if (fileInput && fileNameText) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    fileNameText.textContent = `ไฟล์ที่เลือก: ${this.files[0].name}`;
                    return;
                }

                fileNameText.textContent = data.file_path
                    ? `ไฟล์ปัจจุบัน: ${getFileNameFromPath(data.file_path)}`
                    : 'ยังไม่ได้เลือกไฟล์';
            });
        }

        div.querySelector('.remove').onclick = () => {
            if (confirm('คุณต้องการลบใบรับรองนี้หรือไม่?')) {
                div.remove();
            }
        };
        return div;
    }

    // Language Row with validation
    function languageRow(index, data = {}) {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 p-4 rounded-lg border border-gray-200';
        div.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ภาษา <span class="text-red-500">*</span></label>
                    <select name="languages[${index}][language]" class="input" required>
                        <option value="">-- เลือกภาษา --</option>
                        ${worldLanguagesThai.map(language => `<option value="${language}" ${data.language === language ? 'selected' : ''}>${language}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">ระดับความสามารถ <span class="text-red-500">*</span></label>
                    <select name="languages[${index}][level]" class="input" required style="font-size: 1rem !important; line-height: 1.5rem !important; height: 2.75rem !important; padding: 0.75rem !important;">
                        <option value="">เลือกระดับ</option>
                        <option value="basic" ${data.level == 'basic' ? 'selected' : ''}>พื้นฐาน</option>
                        <option value="conversational" ${data.level == 'conversational' ? 'selected' : ''}>สนทนาได้</option>
                        <option value="fluent" ${data.level == 'fluent' ? 'selected' : ''}>คล่องแคล่ว</option>
                        <option value="native" ${data.level == 'native' ? 'selected' : ''}>เจ้าของภาษา</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" class="remove w-full px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition text-sm">
                        <span class="flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            ลบ
                        </span>
                    </button>
                </div>
            </div>
        `;
        div.querySelector('.remove').onclick = () => {
            if (confirm('คุณต้องการลบภาษานี้หรือไม่?')) {
                div.remove();
            }
        };
        return div;
    }

    // Initialize rows
    let skillIndex = oldSkills.length;
    let workIndex = oldWorkExperiences.length;
    let educationIndex = oldEducations.length;
    let certificateIndex = oldCertificates.length;
    let languageIndex = oldLanguages.length;

    if (addSkillBtn) {
        addSkillBtn.onclick = () => {
            skillContainer.appendChild(skillRow(skillIndex));
            skillIndex++;
        };
    }
    oldSkills.forEach((s, i) => skillContainer.appendChild(skillRow(i, s)));

    if (addWorkBtn) {
        addWorkBtn.onclick = () => {
            workContainer.appendChild(workRow(workIndex));
            workIndex++;
        };
    }
    oldWorkExperiences.forEach((w, i) => workContainer.appendChild(workRow(i, w)));

    if (addEducationBtn) {
        addEducationBtn.onclick = () => {
            educationContainer.appendChild(educationRow(educationIndex));
            educationIndex++;
        };
    }
    oldEducations.forEach((e, i) => educationContainer.appendChild(educationRow(i, e)));

    if (addCertificateBtn) {
        addCertificateBtn.onclick = () => {
            certificateContainer.appendChild(certificateRow(certificateIndex));
            certificateIndex++;
        };
    }
    oldCertificates.forEach((c, i) => certificateContainer.appendChild(certificateRow(i, c)));

    if (addLanguageBtn) {
        addLanguageBtn.onclick = () => {
            languageContainer.appendChild(languageRow(languageIndex));
            languageIndex++;
        };
    }
    oldLanguages.forEach((l, i) => languageContainer.appendChild(languageRow(i, l)));

    }); // End of DOMContentLoaded
</script>
