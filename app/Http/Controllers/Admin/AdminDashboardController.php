<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $courseCount = Course::query()->count();
        $publishedCourseCount = Course::query()->where('is_published', true)->count();
        $teamCount = Team::query()->count();
        $userCount = User::query()->count();
        $pendingCount = User::query()->whereNull('approved_at')->count();

        $pendingUsers = User::query()
            ->with('profile')
            ->whereNull('approved_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $recentCourses = Course::query()
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get(['id', 'slug', 'title', 'is_published', 'updated_at']);

        $recentActivity = $this->recentActivity(8);

        return view('admin.dashboard', compact(
            'courseCount',
            'publishedCourseCount',
            'teamCount',
            'userCount',
            'pendingCount',
            'pendingUsers',
            'recentCourses',
            'recentActivity',
        ));
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function recentActivity(int $limit)
    {
        $lesson = DB::table('lesson_activity_logs')
            ->selectRaw("'lesson' as source, id, user_id, course_id, lesson_id, event_type, occurred_at");
        $forum = DB::table('forum_activity_logs')
            ->selectRaw("'forum' as source, id, user_id, null as course_id, null as lesson_id, event_type, occurred_at");
        $course = DB::table('course_activity_logs')
            ->selectRaw("'course' as source, id, user_id, course_id, null as lesson_id, event_type, occurred_at");

        $rows = DB::query()
            ->fromSub($lesson->unionAll($forum)->unionAll($course), 'events')
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get();

        $usersById = User::query()
            ->with('profile')
            ->whereIn('id', $rows->pluck('user_id')->unique()->filter()->values())
            ->get()
            ->keyBy('id');

        $coursesById = Course::query()
            ->whereIn('id', $rows->pluck('course_id')->unique()->filter()->values())
            ->get(['id', 'title'])
            ->keyBy('id');

        return $rows->map(function ($row) use ($usersById, $coursesById) {
            $user = $usersById->get($row->user_id);

            return (object) [
                'source' => $row->source,
                'event_type' => $row->event_type,
                'occurred_at' => $row->occurred_at ? Carbon::parse($row->occurred_at) : null,
                'user_label' => $user?->profile?->display_name ?? $user?->email ?? __('Unknown user'),
                'course_title' => $row->course_id ? $coursesById->get($row->course_id)?->title : null,
            ];
        });
    }
}
