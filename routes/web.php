<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CourseAdminController;
use App\Http\Controllers\Admin\ForumSetupController;
use App\Http\Controllers\Admin\LessonAdminController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\ModuleAdminController;
use App\Http\Controllers\Admin\QuestionBankController;
use App\Http\Controllers\Admin\QuizAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CourseCatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LessonCommentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizTakeController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('courses.index'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('approval/pending', fn () => view('auth.approval-pending'))
    ->middleware('auth')
    ->name('approval.notice');

Route::get('courses', [CourseCatalogController::class, 'index'])->name('courses.index');
Route::get('courses/{course}', [CourseCatalogController::class, 'show'])->name('courses.show');

Route::get('u/{profile}', [ProfileController::class, 'show'])->name('profiles.show');

Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('enrolled.course')->group(function () {
        Route::get('lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
        Route::post('lessons/{lesson}/comments', [LessonCommentController::class, 'store'])->name('lessons.comments.store');

        Route::get('quizzes/{quiz}', [QuizTakeController::class, 'show'])->name('quizzes.show');
        Route::post('quizzes/{quiz}', [QuizTakeController::class, 'store'])->name('quizzes.store');
        Route::get('quizzes/{quiz}/attempts/{attempt}', [QuizTakeController::class, 'result'])->name('quizzes.result');
    });

    Route::get('profiles/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('profiles', [ProfileController::class, 'update'])->name('profiles.update');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('forums', [ForumController::class, 'index'])->name('forums.index');
    Route::get('forums/{forumCategory}', [ForumController::class, 'category'])->name('forums.category');
    Route::get('forums/{forumCategory}/new', [ForumController::class, 'createThread'])->name('forums.threads.create');
    Route::post('forums/{forumCategory}', [ForumController::class, 'storeThread'])->name('forums.threads.store');
    Route::get('forums/{forumCategory}/t/{forumThread}', [ForumController::class, 'thread'])->name('forums.thread');
    Route::post('forums/{forumCategory}/t/{forumThread}/posts', [ForumController::class, 'storePost'])->name('forums.posts.store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('monitoring/lessons', [MonitoringController::class, 'lessons'])->name('monitoring.lessons');
    Route::get('monitoring/quizzes', [MonitoringController::class, 'quizzes'])->name('monitoring.quizzes');
    Route::get('monitoring/forums', [MonitoringController::class, 'forums'])->name('monitoring.forums');
    Route::get('monitoring/courses', [MonitoringController::class, 'courses'])->name('monitoring.courses');
    Route::get('users/{user}/monitoring', [MonitoringController::class, 'user'])->name('monitoring.user');

    Route::get('users', [UserAdminController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserAdminController::class, 'show'])->name('users.show');
    Route::post('users/{user}/approve', [UserAdminController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/revoke', [UserAdminController::class, 'revoke'])->name('users.revoke');
    Route::put('users/{user}/role', [UserAdminController::class, 'updateRole'])->name('users.role');
    Route::put('users/{user}/courses', [UserAdminController::class, 'updateCourses'])->name('users.courses');

    Route::get('courses', [CourseAdminController::class, 'index'])->name('courses.index');
    Route::get('courses/create', [CourseAdminController::class, 'create'])->name('courses.create');
    Route::post('courses', [CourseAdminController::class, 'store'])->name('courses.store');
    Route::get('courses/{course}/edit', [CourseAdminController::class, 'edit'])->name('courses.edit');
    Route::put('courses/{course}', [CourseAdminController::class, 'update'])->name('courses.update');
    Route::delete('courses/{course}', [CourseAdminController::class, 'destroy'])->name('courses.destroy');

    Route::post('courses/{course}/modules', [ModuleAdminController::class, 'store'])->name('modules.store');
    Route::delete('courses/{course}/modules/{module}', [ModuleAdminController::class, 'destroy'])->name('modules.destroy');

    Route::get('courses/{course}/modules/{module}/lessons/create', [LessonAdminController::class, 'create'])->name('lessons.create');
    Route::post('courses/{course}/modules/{module}/lessons', [LessonAdminController::class, 'store'])->name('lessons.store');
    Route::get('courses/{course}/modules/{module}/lessons/{lesson}/edit', [LessonAdminController::class, 'edit'])->name('lessons.edit');
    Route::put('courses/{course}/modules/{module}/lessons/{lesson}', [LessonAdminController::class, 'update'])->name('lessons.update');

    Route::get('courses/{course}/modules/{module}/lessons/{lesson}/quiz/create', [QuizAdminController::class, 'createLessonQuiz'])->name('quizzes.lesson.create');
    Route::post('courses/{course}/modules/{module}/lessons/{lesson}/quiz', [QuizAdminController::class, 'storeLessonQuiz'])->name('quizzes.lesson.store');
    Route::get('courses/{course}/modules/{module}/quiz/create', [QuizAdminController::class, 'createModuleQuiz'])->name('quizzes.module.create');
    Route::post('courses/{course}/modules/{module}/quiz', [QuizAdminController::class, 'storeModuleQuiz'])->name('quizzes.module.store');

    Route::get('questions', [QuestionBankController::class, 'index'])->name('questions.index');
    Route::get('questions/create', [QuestionBankController::class, 'create'])->name('questions.create');
    Route::post('questions', [QuestionBankController::class, 'store'])->name('questions.store');
    Route::get('questions/{question}/edit', [QuestionBankController::class, 'edit'])->name('questions.edit');
    Route::put('questions/{question}', [QuestionBankController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [QuestionBankController::class, 'destroy'])->name('questions.destroy');

    Route::get('forums/categories', [ForumSetupController::class, 'categories'])->name('forums.categories');
    Route::post('forums/categories', [ForumSetupController::class, 'storeCategory'])->name('forums.categories.store');
    Route::get('forums/tags', [ForumSetupController::class, 'tags'])->name('forums.tags');
    Route::post('forums/tags', [ForumSetupController::class, 'storeTag'])->name('forums.tags.store');
});
