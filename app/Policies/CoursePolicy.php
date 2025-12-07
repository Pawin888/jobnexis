<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Allow admins to do everything on Course.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true;
        }
        return null;
    }

    /**
     * View a course (for education/admin areas).
     */
    public function view(User $user, Course $course): bool
    {
        // Education users can view education area pages
        return $user->role === 'education';
    }

    /**
     * Manage course content: lessons, medias, exams, etc.
     */
    public function manageContent(User $user, Course $course): bool
    {
        // Education who created the course can manage its content
        return $user->role === 'education' && (int) $course->c_create_by_id === (int) $user->id;
    }
}

