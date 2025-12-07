<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\CourseMember;
use App\Models\Recruitment;
use App\Models\ExamAttempt;
use App\Models\Exam;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function admin()
    {
        return view('admin.provider');
    }

    public function education()
    {
        $uid = Auth::id();

        $coursesQ = Course::query()->where('c_create_by_id', $uid);
        $totals = [
            'all'    => (clone $coursesQ)->count(),
            'open'   => (clone $coursesQ)->where('c_status','open')->count(),
            'draft'  => (clone $coursesQ)->where('c_status','draft')->count(),
            'closed' => (clone $coursesQ)->where('c_status','closed')->count(),
            'pending'=> (clone $coursesQ)->where('c_status','pending')->count(),
        ];

        $courseIds = (clone $coursesQ)->pluck('c_id');
        $participants = $courseIds->isEmpty()
            ? 0
            : CourseMember::whereIn('cm_c_id', $courseIds)->count();

        $recentCourses = Course::where('c_create_by_id', $uid)
            ->orderByDesc('c_id')
            ->take(5)
            ->get();

        return view('education.dashboard', compact('totals', 'participants', 'recentCourses'));
    }

    public function provider()
    {
        $uid = Auth::id();

        $jobsQ = Recruitment::ownedBy($uid);
        $totals = [
            'all'    => (clone $jobsQ)->count(),
            'open'   => (clone $jobsQ)->where('rc_status','open')->count(),
            'draft'  => (clone $jobsQ)->where('rc_status','draft')->count(),
            'closed' => (clone $jobsQ)->where('rc_status','closed')->count(),
        ];

        $views = (clone $jobsQ)->sum('rc_views');

        $recentJobs = Recruitment::ownedBy($uid)
            ->orderByDesc('rc_posted_at')
            ->orderByDesc('rc_id')
            ->take(6)
            ->get();

        return view('provider.dashboard', compact('totals', 'views', 'recentJobs'));
    }

    public function jobber()
    {
        return view('jobber.dashboard');
    }

    public function educationSkillStats()
    {
        $uid = Auth::id();
        $courseIds = Course::where('c_create_by_id', $uid)->pluck('c_id');
        if ($courseIds->isEmpty()) {
            return response()->json(['labels'=>[], 'data'=>[], 'avg'=>[]]);
        }

        $examIds = Exam::whereIn('e_c_id', $courseIds)->pluck('e_id');
        if ($examIds->isEmpty()) {
            return response()->json(['labels'=>[], 'data'=>[], 'avg'=>[]]);
        }

        $attempts = ExamAttempt::with('exam:e_id,e_skills')
            ->whereIn('exam_id', $examIds)
            ->select(['exam_id','score','total_questions'])
            ->get();

        $agg = [];
        foreach ($attempts as $att) {
            $exam = $att->exam;
            if (!$exam || empty($exam->e_skills) || !is_array($exam->e_skills)) continue;
            $pct = ($att->total_questions > 0) ? ($att->score / $att->total_questions) * 100.0 : 0.0;
            foreach ($exam->e_skills as $name) {
                $key = (string) $name;
                if (!isset($agg[$key])) $agg[$key] = ['count'=>0,'sumPct'=>0.0];
                $agg[$key]['count'] += 1;
                $agg[$key]['sumPct'] += $pct;
            }
        }

        $rows = [];
        foreach ($agg as $skill => $v) {
            $avg = $v['count'] > 0 ? $v['sumPct'] / $v['count'] : 0.0;
            $rows[] = ['skill'=>$skill, 'count'=>$v['count'], 'avg'=>round($avg,1)];
        }
        usort($rows, fn($a,$b)=> $b['count'] <=> $a['count']);
        $top = array_slice($rows, 0, 5);

        return response()->json([
            'labels' => array_map(fn($r)=> $r['skill'], $top),
            'data'   => array_map(fn($r)=> $r['count'], $top),
            'avg'    => array_map(fn($r)=> $r['avg'], $top),
        ]);
    }
}
