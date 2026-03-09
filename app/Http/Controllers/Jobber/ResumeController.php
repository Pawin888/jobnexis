<?php

namespace App\Http\Controllers\Jobber;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use App\Models\ResumeSkill;
use App\Models\ResumeWorkExperience;
use App\Models\ResumeEducation;
use App\Models\ResumeCertificate;
use App\Models\ResumeLanguage;
use App\Models\MasterSkillGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    /**
     * Show create resume form
     */
    public function create()
    {
        $existingResume = Resume::where('user_id', auth()->id())
            ->latest('id')
            ->first();

        if ($existingResume) {
            return redirect()->route('jobber.resumes.edit', $existingResume);
        }
        
        $skillGroups = MasterSkillGroup::query()
            ->where('is_active', true)
            ->whereHas('skills')
            ->with(['skills' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('jobber.resumes.create', compact('skillGroups'));
    }

    /**
     * Store resume (jobber only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // --- Resume core ---
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'email' => 'required|email',
            'phone' => 'required|string|max:50',
            'summary' => 'nullable|string',
            'available_start_date' => 'nullable|date',
            'preferred_location' => 'nullable|string|max:255',
            'expected_salary' => 'nullable|numeric|min:0',
            'is_visible' => 'boolean',

            // --- Profile Image ---
            'profile_image' => 'nullable|image|max:2048',

            // --- Skills ---
            'skills' => 'required|array|min:1',
            'skills.*.skill_group_id' => 'required|exists:master_skill_groups,id',
            'skills.*.skill_id' => 'required|exists:master_skills,id',
            'skills.*.proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',

            // --- Work Experiences ---
            'work_experiences' => 'nullable|array',
            'work_experiences.*.job_title' => 'required|string|max:255',
            'work_experiences.*.company_name' => 'required|string|max:255',
            'work_experiences.*.start_date' => 'required|date',
            'work_experiences.*.end_date' => 'nullable|date|after_or_equal:work_experiences.*.start_date',
            'work_experiences.*.is_current' => 'boolean',
            'work_experiences.*.description' => 'nullable|string',

            // --- Education ---
            'educations' => 'nullable|array',
            'educations.*.education_level' => 'required|string|max:255',
            'educations.*.field_of_study' => 'required|string|max:255',
            'educations.*.institution' => 'required|string|max:255',
            'educations.*.start_year' => 'required|digits:4|integer',
            'educations.*.end_year' => 'nullable|digits:4|integer',

            // --- Certificates ---
            'certificates' => 'nullable|array',
            'certificates.*.name' => 'required|string|max:255',
            'certificates.*.issued_by' => 'nullable|string|max:255',
            'certificates.*.issued_year' => 'nullable|digits:4|integer',
            'certificates.*.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            // --- Languages ---
            'languages' => 'nullable|array',
            'languages.*.language' => 'required|string|max:255',
            'languages.*.level' => 'required|in:basic,conversational,fluent,native',
        ]);

        DB::transaction(function () use ($validated, $request, &$resume) {

            // --- Upload profile image ---
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }

            // --- Create resume ---
            $resume = Resume::create([
                'user_id' => auth()->id(),
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'summary' => $validated['summary'] ?? null,
                'available_start_date' => $validated['available_start_date'] ?? null,
                'preferred_location' => $validated['preferred_location'] ?? null,
                'expected_salary' => $validated['expected_salary'] ?? null,
                'is_visible' => $validated['is_visible'] ?? true,
                'profile_image' => $profileImagePath,
            ]);

            // --- Skills ---
            foreach ($validated['skills'] as $skill) {
                $resume->resumeSkills()->create($skill);
            }

            // --- Work Experiences ---
            if (!empty($validated['work_experiences'])) {
                foreach ($validated['work_experiences'] as $we) {
                    $resume->workExperiences()->create([
                        'job_title' => $we['job_title'],
                        'company_name' => $we['company_name'],
                        'start_date' => $we['start_date'],
                        'end_date' => $we['end_date'] ?? null,
                        'is_current' => $we['is_current'] ?? false,
                        'description' => $we['description'] ?? null,
                    ]);
                }
            }

            // --- Education ---
            if (!empty($validated['educations'])) {
                foreach ($validated['educations'] as $edu) {
                    $resume->educations()->create($edu);
                }
            }

            // --- Reset Certificates ---
            $resume->certificates()->delete();
            if (!empty($validated['certificates'])) {
                foreach ($validated['certificates'] as $index => $cert) {
                    $filePath = null;
                    if ($request->hasFile("certificates.$index.file")) {
                        $filePath = $request->file("certificates.$index.file")->store('certificates', 'public');
                    }
                    $resume->certificates()->create([
                        'name' => $cert['name'] ?? null,
                        'issued_by' => $cert['issued_by'] ?? null,
                        'issued_year' => $cert['issued_year'] ?? null,
                        'file_path' => $filePath,
                    ]);
                }
            }

            // --- Languages ---
            if (!empty($validated['languages'])) {
                foreach ($validated['languages'] as $lang) {
                    $resume->languages()->create($lang);
                }
            }
        });

        return redirect()
            ->route('profile-jobber.edit', $resume->id)
            ->with('success', 'Resume created successfully');
    }

    /**
     * Edit resume (only owner)
     */
    public function edit(Resume $resume)
    {
        abort_if($resume->user_id !== auth()->id(), 403);

        $resume->load(
            'resumeSkills.skill',
            'resumeSkills.skillGroup',
            'workExperiences',
            'educations',
            'certificates',
            'languages'
        );

        $skillGroups = MasterSkillGroup::query()
            ->where('is_active', true)
            ->whereHas('skills')
            ->with(['skills' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('jobber.resumes.edit', compact('resume', 'skillGroups'));
    }

    /**
     * Update resume (only owner)
     */
    public function update(Request $request, Resume $resume)
    {
        abort_if($resume->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'nullable|in:male,female,other',
            'email' => 'required|email',
            'phone' => 'required|string|max:50',
            'summary' => 'nullable|string',
            'available_start_date' => 'nullable|date',
            'preferred_location' => 'nullable|string|max:255',
            'expected_salary' => 'nullable|numeric|min:0',
            'is_visible' => 'boolean',

            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'skills' => 'required|array|min:1',
            'skills.*.skill_group_id' => 'required|exists:master_skill_groups,id',
            'skills.*.skill_id' => 'required|exists:master_skills,id',
            'skills.*.proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',

            'work_experiences' => 'nullable|array',
            'work_experiences.*.job_title' => 'required|string|max:255',
            'work_experiences.*.company_name' => 'required|string|max:255',
            'work_experiences.*.start_date' => 'required|date',
            'work_experiences.*.end_date' => 'nullable|date|after_or_equal:work_experiences.*.start_date',
            'work_experiences.*.is_current' => 'boolean',
            'work_experiences.*.description' => 'nullable|string',

            'educations' => 'nullable|array',
            'educations.*.education_level' => 'required|string|max:255',
            'educations.*.field_of_study' => 'required|string|max:255',
            'educations.*.institution' => 'required|string|max:255',
            'educations.*.start_year' => 'required|digits:4|integer',
            'educations.*.end_year' => 'nullable|digits:4|integer',

            'certificates' => 'nullable|array',
            'certificates.*.name' => 'required|string|max:255',
            'certificates.*.issued_by' => 'nullable|string|max:255',
            'certificates.*.issued_year' => 'nullable|digits:4|integer',
            'certificates.*.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

            'languages' => 'nullable|array',
            'languages.*.language' => 'required|string|max:255',
            'languages.*.level' => 'required|in:basic,conversational,fluent,native',
        ]);

        DB::transaction(function () use ($validated, $request, $resume) {

            // --- Profile image ---
            if ($request->hasFile('profile_image')) {
                if ($resume->profile_image) {
                    Storage::disk('public')->delete($resume->profile_image);
                }
                $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
            } else {
                unset($validated['profile_image']); // กันไม่ให้ object file หลุดเข้า update
            }

            $resume->update($validated);

            // --- Reset Skills ---
            $resume->resumeSkills()->delete();
            foreach ($validated['skills'] as $skill) {
                $resume->resumeSkills()->create($skill);
            }

            // --- Reset Work Experiences ---
            $resume->workExperiences()->delete();
            if (!empty($validated['work_experiences'])) {
                foreach ($validated['work_experiences'] as $we) {
                    $resume->workExperiences()->create([
                        'job_title' => $we['job_title'],
                        'company_name' => $we['company_name'],
                        'start_date' => $we['start_date'],
                        'end_date' => $we['end_date'] ?? null,
                        'is_current' => $we['is_current'] ?? false,
                        'description' => $we['description'] ?? null,
                    ]);
                }
            }

            // --- Reset Educations ---
            $resume->educations()->delete();
            if (!empty($validated['educations'])) {
                foreach ($validated['educations'] as $edu) {
                    $resume->educations()->create($edu);
                }
            }

            // --- Reset Certificates ---
            $resume->certificates()->delete();
            if (!empty($validated['certificates'])) {
                foreach ($validated['certificates'] as $index => $cert) {
                    $filePath = null;
                    if ($request->hasFile("certificates.$index.file")) {
                        $filePath = $request->file("certificates.$index.file")->store('certificates', 'public');
                    }

                    $resume->certificates()->create([
                        'name' => $cert['name'] ?? null,
                        'issued_by' => $cert['issued_by'] ?? null,
                        'issued_year' => $cert['issued_year'] ?? null,
                        'file_path' => $filePath,
                    ]);
                }
            }

            // --- Reset Languages ---
            $resume->languages()->delete();
            if (!empty($validated['languages'])) {
                foreach ($validated['languages'] as $lang) {
                    $resume->languages()->create($lang);
                }
            }
        });

        return redirect()
            ->route('profile-jobber.edit')
            ->with('success', 'Resume updated successfully');
    }

    /**
     * Delete resume (only owner)
     */
    public function destroy(Resume $resume)
    {
        abort_if($resume->user_id !== auth()->id(), 403);

        // ลบไฟล์ profile image
        if ($resume->profile_image) {
            Storage::disk('public')->delete($resume->profile_image);
        }

        // ลบไฟล์ certificates
        foreach ($resume->certificates as $cert) {
            if ($cert->file_path) {
                Storage::disk('public')->delete($cert->file_path);
            }
        }

        // ลบทุกความสัมพันธ์
        $resume->resumeSkills()->delete();
        $resume->workExperiences()->delete();
        $resume->educations()->delete();
        $resume->certificates()->delete();
        $resume->languages()->delete();

        $resume->delete();

        return redirect()
            ->route('profile-jobber.edit')
            ->with('success', 'Resume deleted successfully');
    }
}
