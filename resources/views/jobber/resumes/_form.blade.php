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

    <div class="space-y-6">
        {{-- Profile Image --}}
        <div class="mt-4">
            <h3 class="font-semibold mb-2">รูปโปรไฟล์</h3>
            <input id="profile-image" type="file" name="profile_image" accept="image/*" class="input">
            @if(isset($resume->profile_image))
                <img src="{{ asset($resume->profile_image) }}" alt="Profile Image" class="mt-2 w-24 h-24 object-cover rounded-full">
            @endif
        </div>

        {{-- Basic Info --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" name="first_name" placeholder="ชื่อ *"
                value="{{ old('first_name', $resume->first_name ?? '') }}" required class="input">
            <input type="text" name="middle_name" placeholder="ชื่อกลาง"
                value="{{ old('middle_name', $resume->middle_name ?? '') }}" class="input">
            <input type="text" name="last_name" placeholder="นามสกุล *"
                value="{{ old('last_name', $resume->last_name ?? '') }}" required class="input">
            <input type="date" name="birth_date" placeholder="วันเกิด *"
                value="{{ old('birth_date', isset($resume->birth_date) ? \Carbon\Carbon::parse($resume->birth_date)->format('Y-m-d') : '') }}"
                required class="input">
            <select name="gender" class="input" required>
                <option value="">-- เพศ --</option>
                <option value="male" {{ old('gender', $resume->gender ?? '') == 'male' ? 'selected' : '' }}>ชาย</option>
                <option value="female" {{ old('gender', $resume->gender ?? '') == 'female' ? 'selected' : '' }}>หญิง</option>
                <option value="other" {{ old('gender', $resume->gender ?? '') == 'other' ? 'selected' : '' }}>อื่นๆ</option>
            </select>
        </div>

        {{-- Contact --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
            <input type="email" name="email" placeholder="อีเมล *"
                value="{{ old('email', $resume->email ?? '') }}" required class="input">
            <input type="text" name="phone" placeholder="เบอร์ติดต่อ *"
                value="{{ old('phone', $resume->phone ?? '') }}" required class="input">
        </div>

        {{-- Summary --}}
        <textarea name="summary" rows="3" class="input mt-2"
            placeholder="ข้อมูลส่วนตัวโดยสรุป">{{ old('summary', $resume->summary ?? '') }}</textarea>

        {{-- Skills --}}
        <div class="mt-4">
            <h3 class="font-semibold mb-2">ทักษะ</h3>
            <div id="skills-container" class="space-y-3"></div>
            <button type="button" id="add-skill"
                class="mt-2 text-sm text-blue-600 hover:underline">+ เพิ่มทักษะ</button>
        </div>

        {{-- Work Experiences --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-2">ประสบการณ์ทำงาน</h3>
            <div id="work-container" class="space-y-3"></div>
            <button type="button" id="add-work"
                class="mt-2 text-sm text-blue-600 hover:underline">+ เพิ่มประสบการณ์</button>
        </div>

        {{-- Educations --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-2">ประวัติการศึกษา</h3>
            <div id="education-container" class="space-y-3"></div>
            <button type="button" id="add-education"
                class="mt-2 text-sm text-blue-600 hover:underline">+ เพิ่มการศึกษา</button>
        </div>

        {{-- Certificates --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-2">ใบรับรอง/ประกาศนียบัตร</h3>
            <div id="certificate-container" class="space-y-3"></div>
            <button type="button" id="add-certificate"
                class="mt-2 text-sm text-blue-600 hover:underline">+ เพิ่มใบรับรอง</button>
        </div>

        {{-- Languages --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-2">ภาษา</h3>
            <div id="language-container" class="space-y-3"></div>
            <button type="button" id="add-language"
                class="mt-2 text-sm text-blue-600 hover:underline">+ เพิ่มภาษา</button>
        </div>

        {{-- Other --}}
        <div class="mt-6">
            <h3 class="font-semibold mb-2">รายละเอียดเพิ่มเติม</h3>
            <div id="other-container" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">เริ่มงานได้เมื่อ</label>
                        <input type="date" name="available_start_date"
                            value="{{ old('available_start_date', $resume->available_start_date ?? '') }}"
                            class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">สถานที่ทำงานที่ต้องการ</label>
                        <input type="text" name="preferred_location"
                            value="{{ old('preferred_location', $resume->preferred_location ?? '') }}"
                            class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">เงินเดือนที่คาดหวัง</label>
                        <input type="number" name="expected_salary"
                            value="{{ old('expected_salary', $resume->expected_salary ?? '') }}"
                            class="input w-full">
                    </div>
                </div>
            </div>
        </div>

        <label class="flex items-center gap-2 mt-4">
            <input type="checkbox" name="is_visible" value="1"
                {{ old('is_visible', $resume->is_visible ?? true) ? 'checked' : '' }}>
            เปิดเผยเรซูเม่
        </label>

    </div>

<script>
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
    const submitBtn = document.getElementById('submit-btn');
    const profileInput = document.getElementById('profile-image');

    // --- Generic function to prevent submit until files are selected ---
    document.getElementById('resume-form').addEventListener('submit', function(e){
        let uploading = false;
        document.querySelectorAll('input[type="file"]').forEach(f=>{
            if(f.files.length>0 && !f.complete) uploading = true;
        });
        if(uploading){
            e.preventDefault();
            alert('กรุณารอให้ไฟล์อัพโหลดเสร็จก่อนบันทึก');
        }
    });

    // --- Skill Row ---
    function skillRow(index, data={}) {
        const div=document.createElement('div');
        div.className='grid grid-cols-1 md:grid-cols-4 gap-2 items-center';
        div.innerHTML=`
            <select name="skills[${index}][skill_group_id]" class="input group">
                <option value="">-- กลุ่มทักษะ --</option>
                ${skillGroups.map(g=>`<option value="${g.id}" ${g.id==data.skill_group_id?'selected':''}>${g.name}</option>`).join('')}
            </select>
            <select name="skills[${index}][skill_id]" class="input skill"><option value="">-- ทักษะ --</option></select>
            <select name="skills[${index}][proficiency_level]" class="input">
                <option value="">ระดับ</option>
                ${['beginner','intermediate','advanced','expert'].map(l=>`<option value="${l}" ${l==data.proficiency_level?'selected':''}>${l}</option>`).join('')}
            </select>
            <button type="button" class="remove text-red-600 text-sm">ลบ</button>
        `;
        const groupSelect=div.querySelector('.group');
        const skillSelect=div.querySelector('.skill');
        function loadSkills(){
            const group=skillGroups.find(g=>g.id==groupSelect.value);
            skillSelect.innerHTML='<option value="">-- ทักษะ --</option>';
            if(group && group.skills) group.skills.forEach(s=>skillSelect.innerHTML+=`<option value="${s.id}" ${s.id==data.skill_id?'selected':''}>${s.name}</option>`);
        }
        groupSelect.addEventListener('change', loadSkills);
        loadSkills();
        div.querySelector('.remove').onclick=()=>div.remove();
        return div;
    }
    addSkillBtn.onclick=()=>skillContainer.appendChild(skillRow(skillContainer.children.length));
    oldSkills.forEach((s,i)=>skillContainer.appendChild(skillRow(i,s)));

    // --- Work Row ---
    function workRow(index,data={}) {
        const div=document.createElement('div');
        div.className='grid grid-cols-1 md:grid-cols-6 gap-2 items-center';
        div.innerHTML=`<input type="text" name="work_experiences[${index}][job_title]" placeholder="ตำแหน่งงาน" value="${data.job_title??''}" class="input" required>
        <input type="text" name="work_experiences[${index}][company_name]" placeholder="บริษัท" value="${data.company_name??''}" class="input" required>
        <input type="date" name="work_experiences[${index}][start_date]" value="${data.start_date??''}" class="input" required>
        <input type="date" name="work_experiences[${index}][end_date]" value="${data.end_date??''}" class="input">
        <label class="flex items-center gap-2"><input type="checkbox" name="work_experiences[${index}][is_current]" value="1" ${data.is_current?'checked':''}>ปัจจุบัน</label>
        <textarea name="work_experiences[${index}][description]" placeholder="รายละเอียด" class="input">${data.description??''}</textarea>
        <button type="button" class="remove text-red-600 text-sm">ลบ</button>`;
        div.querySelector('.remove').onclick=()=>div.remove();
        return div;
    }
    addWorkBtn.onclick=()=>workContainer.appendChild(workRow(workContainer.children.length));
    oldWorkExperiences.forEach((w,i)=>workContainer.appendChild(workRow(i,w)));

    // --- Education Row ---
    function educationRow(index,data={}) {
        const div=document.createElement('div');
        div.className='grid grid-cols-1 md:grid-cols-5 gap-2 items-center';
        div.innerHTML=`<input type="text" name="educations[${index}][education_level]" placeholder="ระดับการศึกษา" value="${data.education_level??''}" class="input" required>
        <input type="text" name="educations[${index}][field_of_study]" placeholder="สาขา" value="${data.field_of_study??''}" class="input" required>
        <input type="text" name="educations[${index}][institution]" placeholder="สถาบัน" value="${data.institution??''}" class="input" required>
        <input type="number" name="educations[${index}][start_year]" placeholder="ปีเริ่ม" value="${data.start_year??''}" class="input" required>
        <input type="number" name="educations[${index}][end_year]" placeholder="ปีจบ" value="${data.end_year??''}" class="input">
        <button type="button" class="remove text-red-600 text-sm">ลบ</button>`;
        div.querySelector('.remove').onclick=()=>div.remove();
        return div;
    }
    addEducationBtn.onclick=()=>educationContainer.appendChild(educationRow(educationContainer.children.length));
    oldEducations.forEach((e,i)=>educationContainer.appendChild(educationRow(i,e)));

    // --- Certificate Row ---
    function certificateRow(index,data={}) {
        const div=document.createElement('div');
        div.className='grid grid-cols-1 md:grid-cols-5 gap-2 items-center';
        div.dataset.index=index;
        div.innerHTML=`<input type="text" name="certificates[${index}][name]" placeholder="ชื่อใบรับรอง" value="${data.name??''}" class="input" required>
        <input type="text" name="certificates[${index}][issued_by]" placeholder="ออกโดย" value="${data.issued_by??''}" class="input">
        <input type="number" name="certificates[${index}][issued_year]" placeholder="ปีออก" value="${data.issued_year??''}" class="input">
        <input type="file" name="certificates[${index}][file]" class="input">
        ${data.file_path? `<a href="${data.file_path}" target="_blank" class="text-blue-600 text-sm">ไฟล์เดิม</a>`: ''}
        <button type="button" class="remove text-red-600 text-sm">ลบ</button>`;
        div.querySelector('.remove').onclick=()=>div.remove();
        return div;
    }
    addCertificateBtn.onclick=()=>certificateContainer.appendChild(certificateRow(certificateContainer.children.length));
    oldCertificates.forEach((c,i)=>certificateContainer.appendChild(certificateRow(i,c)));

    // --- Language Row ---
    function languageRow(index,data={}) {
        const div=document.createElement('div');
        div.className='grid grid-cols-1 md:grid-cols-3 gap-2 items-center';
        div.innerHTML=`<input type="text" name="languages[${index}][language]" placeholder="ภาษา" value="${data.language??''}" class="input" required>
        <select name="languages[${index}][level]" class="input"><option value="">ระดับ</option>${['basic','conversational','fluent','native'].map(l=>`<option value="${l}" ${l==data.level?'selected':''}>${l}</option>`).join('')}</select>
        <button type="button" class="remove text-red-600 text-sm">ลบ</button>`;
        div.querySelector('.remove').onclick=()=>div.remove();
        return div;
    }
    addLanguageBtn.onclick=()=>languageContainer.appendChild(languageRow(languageContainer.children.length));
    oldLanguages.forEach((l,i)=>languageContainer.appendChild(languageRow(i,l)));
</script>
