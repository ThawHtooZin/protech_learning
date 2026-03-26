<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseActivityLog;
use App\Models\ForumActivityLog;
use App\Models\LessonActivityLog;
use App\Models\QuizActivityLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    private function filters(Request $request): array
    {
        return [
            'userId' => $request->filled('user_id') ? $request->integer('user_id') : null,
            'courseId' => $request->filled('course_id') ? $request->integer('course_id') : null,
            'eventType' => $request->filled('event_type') ? $request->string('event_type')->toString() : null,
            'from' => $request->filled('from') ? $request->date('from') : null,
            'to' => $request->filled('to') ? $request->date('to') : null,
        ];
    }

    public function index(Request $request): View
    {
        ['userId' => $userId, 'courseId' => $courseId, 'eventType' => $eventType, 'from' => $from, 'to' => $to] = $this->filters($request);

        $lesson = DB::table('lesson_activity_logs')
            ->selectRaw("'lesson' as source, id, user_id, course_id, lesson_id, null as quiz_id, event_type, occurred_at, meta");
        $quiz = DB::table('quiz_activity_logs')
            ->selectRaw("'quiz' as source, id, user_id, course_id, lesson_id, quiz_id, event_type, occurred_at, meta");
        $forum = DB::table('forum_activity_logs')
            ->selectRaw("'forum' as source, id, user_id, null as course_id, null as lesson_id, null as quiz_id, event_type, occurred_at, meta");
        $course = DB::table('course_activity_logs')
            ->selectRaw("'course' as source, id, user_id, course_id, null as lesson_id, null as quiz_id, event_type, occurred_at, meta");

        foreach ([$lesson, $quiz, $forum, $course] as $q) {
            if ($userId) {
                $q->where('user_id', $userId);
            }
            if ($courseId) {
                $q->where('course_id', $courseId);
            }
            if ($eventType) {
                $q->where('event_type', $eventType);
            }
            if ($from) {
                $q->where('occurred_at', '>=', $from);
            }
            if ($to) {
                $q->where('occurred_at', '<=', $to);
            }
        }

        $union = $lesson->unionAll($quiz)->unionAll($forum)->unionAll($course);
        $base = DB::query()->fromSub($union, 'events')->orderByDesc('occurred_at');

        $perPage = 50;
        $page = max(1, (int) $request->integer('page', 1));
        $total = (clone $base)->count();
        $rows = $base->forPage($page, $perPage)->get();

        $userIds = $rows->pluck('user_id')->unique()->filter()->values();
        $usersById = User::query()->with('profile')->whereIn('id', $userIds)->get()->keyBy('id');

        $courseIds = $rows->pluck('course_id')->unique()->filter()->values();
        $coursesById = Course::query()->whereIn('id', $courseIds)->get()->keyBy('id');

        $lessonIds = $rows->pluck('lesson_id')->unique()->filter()->values();
        $lessonsById = $lessonIds->isEmpty()
            ? collect()
            : \App\Models\Lesson::query()->whereIn('id', $lessonIds)->get()->keyBy('id');

        $quizIds = $rows->pluck('quiz_id')->unique()->filter()->values();
        $quizzesById = $quizIds->isEmpty()
            ? collect()
            : \App\Models\Quiz::query()->whereIn('id', $quizIds)->get()->keyBy('id');

        $events = $rows->map(function ($r) use ($usersById, $coursesById, $lessonsById, $quizzesById) {
            $meta = $r->meta ? json_decode($r->meta, true) : null;

            return (object) [
                'source' => $r->source,
                'id' => $r->id,
                'user' => $usersById->get($r->user_id),
                'course' => $r->course_id ? $coursesById->get($r->course_id) : null,
                'lesson' => $r->lesson_id ? $lessonsById->get($r->lesson_id) : null,
                'quiz' => $r->quiz_id ? $quizzesById->get($r->quiz_id) : null,
                'event_type' => $r->event_type,
                'occurred_at' => $r->occurred_at ? Carbon::parse($r->occurred_at) : null,
                'meta' => $meta,
            ];
        });

        $events = new LengthAwarePaginator(
            $events,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $eventTypes = array_values(array_unique(array_merge(
            LessonActivityLog::query()->select('event_type')->distinct()->pluck('event_type')->all(),
            QuizActivityLog::query()->select('event_type')->distinct()->pluck('event_type')->all(),
            ForumActivityLog::query()->select('event_type')->distinct()->pluck('event_type')->all(),
            CourseActivityLog::query()->select('event_type')->distinct()->pluck('event_type')->all(),
        )));
        sort($eventTypes);

        return view('admin.monitoring.index', compact('events', 'users', 'courses', 'eventTypes'));
    }

    public function lessons(Request $request): View
    {
        ['userId' => $userId, 'courseId' => $courseId, 'eventType' => $eventType, 'from' => $from, 'to' => $to] = $this->filters($request);

        $query = LessonActivityLog::query()
            ->with(['user.profile', 'course', 'lesson'])
            ->orderByDesc('occurred_at');

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        if ($eventType) {
            $query->where('event_type', $eventType);
        }
        if ($from) {
            $query->where('occurred_at', '>=', $from);
        }
        if ($to) {
            $query->where('occurred_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $eventTypes = LessonActivityLog::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type')->all();

        return view('admin.monitoring.lessons', compact('logs', 'users', 'courses', 'eventTypes'));
    }

    public function quizzes(Request $request): View
    {
        ['userId' => $userId, 'courseId' => $courseId, 'eventType' => $eventType, 'from' => $from, 'to' => $to] = $this->filters($request);

        $query = QuizActivityLog::query()
            ->with(['user.profile', 'course', 'lesson', 'quiz', 'attempt'])
            ->orderByDesc('occurred_at');

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        if ($eventType) {
            $query->where('event_type', $eventType);
        }
        if ($from) {
            $query->where('occurred_at', '>=', $from);
        }
        if ($to) {
            $query->where('occurred_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $eventTypes = QuizActivityLog::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type')->all();

        return view('admin.monitoring.quizzes', compact('logs', 'users', 'courses', 'eventTypes'));
    }

    public function forums(Request $request): View
    {
        ['userId' => $userId, 'eventType' => $eventType, 'from' => $from, 'to' => $to] = $this->filters($request);

        $query = ForumActivityLog::query()
            ->with(['user.profile', 'category', 'thread', 'post', 'parentPost'])
            ->orderByDesc('occurred_at');

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($eventType) {
            $query->where('event_type', $eventType);
        }
        if ($from) {
            $query->where('occurred_at', '>=', $from);
        }
        if ($to) {
            $query->where('occurred_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $eventTypes = ForumActivityLog::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type')->all();

        return view('admin.monitoring.forums', compact('logs', 'users', 'eventTypes'));
    }

    public function courses(Request $request): View
    {
        ['userId' => $userId, 'courseId' => $courseId, 'eventType' => $eventType, 'from' => $from, 'to' => $to] = $this->filters($request);

        $query = CourseActivityLog::query()
            ->with(['user.profile', 'course'])
            ->orderByDesc('occurred_at');

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        if ($eventType) {
            $query->where('event_type', $eventType);
        }
        if ($from) {
            $query->where('occurred_at', '>=', $from);
        }
        if ($to) {
            $query->where('occurred_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $eventTypes = CourseActivityLog::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type')->all();

        return view('admin.monitoring.courses', compact('logs', 'users', 'courses', 'eventTypes'));
    }

    public function user(Request $request, User $user): View
    {
        $req = Request::create($request->url(), 'GET', array_merge($request->query(), [
            'user_id' => $user->id,
        ]));

        // Reuse index() for a user-focused timeline.
        $view = $this->index($req);
        /** @var array{events:mixed,users:mixed,courses:mixed,eventTypes:mixed} $data */
        $data = $view->getData();
        $events = $data['events'];

        return view('admin.monitoring.user', compact('user', 'events'));
    }
}

