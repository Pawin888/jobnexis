<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Education\CourseController;
use App\Http\Controllers\Education\ExamController;
use App\Http\Controllers\Education\LessonController;
use App\Http\Controllers\Education\MediaController;
use App\Http\Controllers\Education\QuestionController;
use App\Http\Controllers\Education\PersonController;
use App\Http\Controllers\EducationProfileController;
use App\Http\Controllers\RecruitmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompaniesProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileDetailController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\SkillController;

Route::get('/', fn() => view('welcome'))->name('home');

// Public catalog page (guest-friendly)
Route::get('/courses', [CourseController::class, 'catalog'])->name('courses.catalog');

// Public: หางาน + ผู้ประกอบการ (guest-friendly)
Route::get('/jobs', [RecruitmentController::class, 'publicIndex'])->name('jobs.index');
Route::get('/jobs/{rcId}', [RecruitmentController::class, 'publicShow'])->name('jobs.show');
Route::get('/companies', [CompaniesProfileController::class, 'guestIndex'])->name('companies.index');
Route::get('/companies/{userId}', [CompaniesProfileController::class, 'guestShow'])->name('companies.show');

Route::middleware(['auth', 'verified'])->group(function () {
    /** -------- Admin: รายงานของ provider แต่ละคน -------- */
    Route::get('/admin/providers/{userId}/recruitments', [RecruitmentController::class, 'adminIndex'])
        ->name('admin.providers.recruitments.index');
    Route::get('/admin/providers/{userId}/recruitments/create', [RecruitmentController::class, 'createForAdmin'])
        ->name('admin.providers.recruitments.create');
    Route::post('/admin/providers/{userId}/recruitments', [RecruitmentController::class, 'storeForAdmin'])
        ->name('admin.providers.recruitments.store');
    Route::get('/admin/recruitments/{rcId}/edit', [RecruitmentController::class, 'edit'])
        ->name('admin.recruitments.edit');
    Route::patch('/admin/recruitments/{rcId}', [RecruitmentController::class, 'update'])
        ->name('admin.recruitments.update');
    Route::delete('/admin/recruitments/{rcId}', [RecruitmentController::class, 'destroy'])
        ->name('admin.recruitments.destroy');
    Route::patch('/admin/recruitments/{rcId}/status', [RecruitmentController::class, 'updateStatus'])
        ->name('admin.recruitments.status');

    /** -------- Provider: จัดการงานของตัวเอง -------- */
    Route::get('/my/recruitments', [RecruitmentController::class, 'providerIndex'])
        ->name('provider.recruitments.index');
    Route::post('/my/recruitments', [RecruitmentController::class, 'storeForProvider'])
        ->name('provider.recruitments.store');
    Route::get('/my/recruitments/create', [RecruitmentController::class, 'createForProvider'])
        ->name('provider.recruitments.create');
    Route::get('/my/recruitments/{rcId}/edit', [RecruitmentController::class, 'edit'])
        ->name('provider.recruitments.edit');
    Route::patch('/my/recruitments/{rcId}', [RecruitmentController::class, 'update'])
        ->name('provider.recruitments.update');
    Route::delete('/my/recruitments/{rcId}', [RecruitmentController::class, 'destroy'])
        ->name('provider.recruitments.destroy');
    Route::patch('/my/recruitments/{rcId}/status', [RecruitmentController::class, 'updateStatus'])
        ->name('provider.recruitments.status');
    //----------------


    /** ---------------- Dashboard ---------------- */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/edit-provider/{userId?}', [CompaniesProfileController::class, 'edit'])
        ->name('provider.profile.edit');
    Route::post('/edit-provider/{userId?}/store', [CompaniesProfileController::class, 'store'])
        ->name('provider.profile.store');
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/providers', [CompaniesProfileController::class, 'index'])
            ->name('admin.providers.index');
        Route::patch('/admin/providers/{user}/toggle-ban', [CompaniesProfileController::class, 'toggleBan'])
            ->name('admin.providers.toggleBan');
        Route::delete('/admin/providers/{user}', [CompaniesProfileController::class, 'destroy'])
            ->name('admin.providers.destroy');
    });

    // สมัคร/ยกเลิกสมัครคอร์ส (เฉพาะ Jobber) - ไม่มี prefix เส้นทาง
    Route::middleware('role:jobber')->group(function () {
        Route::post('/courses/{id}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
        Route::delete('/courses/{id}/enroll', [CourseController::class, 'unenroll'])->name('courses.unenroll');
    });

    /** ---------------- Profile Details ---------------- */
    Route::prefix('admin/profile')->group(function () {
        Route::delete('/education/{id}', [ProfileDetailController::class, 'destroyEducation'])->name('education.destroy');
        Route::delete('/work/{id}', [ProfileDetailController::class, 'destroyWork'])->name('work.destroy');
    });
    Route::patch('/certificates/{id}/toggle', [CertificateController::class, 'toggle'])->name('certificates.toggle');
    Route::delete('/certificates/{id}', [CertificateController::class, 'destroy'])->name('certificates.destroy');
    /** ---------------- Admin Routes ---------------- */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'userStats'])
            ->name('admin.userStats');
        Route::get('/dashboard/data', [AdminDashboardController::class, 'userStatsData'])
            ->name('admin.userStats.data');
        Route::get('/jobber', [ProfileDetailController::class, 'index'])->name('admin.jobber.index');
        Route::get('/education', [EducationProfileController::class, 'index'])->name('admin.educations.index');
        Route::post('/edit-jobber/{userId}/certificate', [CertificateController::class, 'store'])
            ->name('admin.certificates.store');
        Route::get('/edit-jobber/{userId?}', [ProfileDetailController::class, 'edit'])->name('profile-details.edit');
        Route::post('/edit-jobber/{userId?}/store', [ProfileDetailController::class, 'store'])->name('profile-details.store');
        Route::get('/edit-education/{userId?}', [EducationProfileController::class, 'edit'])
            ->name('admin.profile-education.edit');
        Route::post('/edit-education/{userId?}/store', [EducationProfileController::class, 'store'])
            ->name('admin.profile-education.store');
        Route::patch('/educations/{user}/toggle-ban', [EducationProfileController::class, 'toggleBan'])
            ->name('admin.educations.toggleBan');
        Route::delete('/educations/{user}', [EducationProfileController::class, 'destroy'])
            ->name('admin.educations.destroy');
        Route::patch('/jobber/{user}/toggle-ban', [ProfileDetailController::class, 'toggleBan'])
            ->name('admin.jobber.toggleBan');
        Route::delete('/jobber/{user}', [ProfileDetailController::class, 'destroy'])
            ->name('admin.jobber.destroy');
        Route::get('/management-skills', [SkillController::class, 'index'])->name('management.skills.index');
        Route::post('/management-skills', [SkillController::class, 'store'])->name('management.skills.store');
        Route::put('/management-skills/{skill}', [SkillController::class, 'update'])->name('management.skills.update');
        Route::delete('/management-skills/{skill}', [SkillController::class, 'destroy'])->name('management.skills.destroy');
    });
    Route::get('/dashboard/skills', [AdminDashboardController::class, 'skillStats'])->name('admin.skills.data');
    /** ---------------- Provider Routes ---------------- */
    Route::middleware('role:provider')->prefix('provider')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'provider'])->name('provider.dashboard');
    });
    /** ---------------- Education Routes ---------------- */
    Route::get('/edit-education', [EducationProfileController::class, 'edit'])
        ->name('profile-education.edit');
    Route::post('/edit-education/store', [EducationProfileController::class, 'store'])
        ->name('profile-education.store');
});
// Course details (all authenticated roles)
Route::get('/courses/{id}', [CourseController::class, 'publicShow'])->name('courses.view');

// Education routes (ให้ admin เข้าถึงได้ด้วย)
Route::middleware('role:education,admin')->group(function () {

    // Dashboard ของ Education
    Route::get('/education/dashboard', [DashboardController::class, 'education'])->name('education.dashboard');

    // Course routes (สำหรับผู้สอน/แอดมิน)
    Route::prefix('education/courses')->name('courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');       // คอร์สทั้งหมด
        Route::get('/create', [CourseController::class, 'create'])->name('create'); // สร้างคอร์ส
        Route::post('/', [CourseController::class, 'store'])->name('store');      // บันทึกคอร์ส
        Route::get('/{id}', [CourseController::class, 'show'])->name('show');     // รายละเอียดคอร์ส
        Route::get('/person/{id}', [PersonController::class, 'show'])->name('person.show');      // บุคคล
        // ออกประกาศนียบัตรให้สมาชิกในคอร์ส
        Route::post('/{id}/members/{userId}/certificate', [PersonController::class, 'issueCertificate'])->name('members.certificate.issue');
        Route::post('/{id}/members/certificates/issue-all', [PersonController::class, 'issueCertificatesAll'])->name('members.certificate.issueAll');
        Route::delete('/{id}', [CourseController::class, 'destroy'])->name('destroy');      // ลบคอร์ส
        Route::get('/{id}/edit', [CourseController::class, 'edit'])->name('edit');      // แก้ไขคอร์ส
        Route::put('/{id}', [CourseController::class, 'update'])->name('update');      // อัพเดทคอร์ส
    });
    // Lesson routes
    Route::prefix('education/lesson')->group(function () {
        Route::put('/{id}', [LessonController::class, 'update'])->name('lesson.update');   // แก้ไขชื่อบทเรียน
        Route::delete('/{id}', [LessonController::class, 'destroyLesson'])->name('lesson.destroyLesson'); // ลบบทเรียน
    });
    // Media routes
    Route::prefix('education/medias')->name('medias.')->group(function () {
        Route::get('/create/{courseId?}', [MediaController::class, 'create'])->name('create');      // ฟอร์มสร้างสื่อ
        Route::post('/', [MediaController::class, 'store'])->name('store');       // บันทึกสื่อ
        Route::post('/lesson', [MediaController::class, 'storeLesson'])->name('lesson.store');      // สร้างบทเรียนใหม่
        Route::get('/{id}/edit', [MediaController::class, 'edit'])->name('edit');                    // หน้าแก้ไขสื่อ
        Route::put('/{id}', [MediaController::class, 'update'])->name('update');                     // อัพเดทสื่อ
        Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');                // ลบสื่อ
    });
    // Exam routes
    Route::prefix('education/exams')->name('exams.')->group(function () {
        Route::get('/create/{courseId?}', [ExamController::class, 'create'])->name('create');
        Route::post('/', [ExamController::class, 'store'])->name('store');
        Route::get('/{id}', [ExamController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ExamController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ExamController::class, 'update'])->name('update');
        Route::delete('/{id}', [ExamController::class, 'destroy'])->name('destroy');
        Route::post('/lesson', [ExamController::class, 'storeLesson'])->name('lesson.store');
    });
    // Question routes
    Route::prefix('education/questions')->name('questions.')->group(function () {
        Route::get('/manage/{exam_id}', [QuestionController::class, 'manage'])->name('manage');
        Route::post('/', [QuestionController::class, 'store'])->name('store');
        Route::post('/batch-update', [QuestionController::class, 'updateBatch'])->name('updateBatch');
        Route::put('/{id}', [QuestionController::class, 'update'])->name('update');
        Route::delete('/{id}', [QuestionController::class, 'destroy'])->name('destroy');
    });
});
/** ---------------- Provider Routes ---------------- */
Route::middleware('role:provider')->prefix('provider')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'provider'])->name('provider.dashboard');
    Route::get('/edit-profile', [CompaniesProfileController::class, 'edit'])->name('provider.profile.edit.self');
    Route::post('/edit-profile/store', [CompaniesProfileController::class, 'store'])->name('provider.profile.store.self');
});

/** ---------------- Education Routes ---------------- */
Route::middleware('role:education')->prefix('education')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'education'])->name('education.dashboard');
    Route::get('/edit-education', [EducationProfileController::class, 'edit'])->name('profile-education.edit.self');
    Route::post('/edit-education/store', [EducationProfileController::class, 'store'])->name('profile-education.store.self');
});

/** ---------------- Jobber Routes ---------------- */
Route::middleware('role:jobber')->prefix('jobber')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'jobber'])->name('jobber.dashboard');
    Route::get('/jobs', [RecruitmentController::class, 'jobberIndex'])->name('jobber.jobs.index');
    Route::get('/jobs/{rcId}', [RecruitmentController::class, 'jobberShow'])->name('jobber.jobs.show');
    // Companies directory for jobbers
    Route::get('/companies', [CompaniesProfileController::class, 'publicIndex'])->name('jobber.companies.index');
    Route::get('/companies/{userId}', [CompaniesProfileController::class, 'publicShow'])->name('jobber.companies.show');
    Route::post('/jobber/edit-profile/certificate', [CertificateController::class, 'store'])->name('certificates.store');
    Route::get('/edit-profile', [ProfileDetailController::class, 'edit'])->name('profile-jobber.edit');
    Route::post('/edit-profile/store', [ProfileDetailController::class, 'store'])->name('profile-jobber.store');
    // Exam routes สำหรับ Jobber
    Route::get('/exams/{id}/take', [ExamController::class, 'take'])->name('exams.take');
    Route::post('/exams/{id}/submit', [ExamController::class, 'submit'])->name('exams.submit');
});

/** ---------------- User Profile ---------------- */
Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'edit')->name('profile.edit');
    Route::patch('/profile', 'update')->name('profile.update');
    Route::delete('/profile', 'destroy')->name('profile.destroy');
});

require __DIR__ . '/auth.php';
