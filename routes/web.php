<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BankAccountAdminController;
use App\Http\Controllers\Admin\CourseAccessAdminController;
use App\Http\Controllers\Admin\CourseAdminController;
use App\Http\Controllers\Admin\CoursePurchaseAdminController;
use App\Http\Controllers\Admin\CourseStructureController;
use App\Http\Controllers\Admin\ForumSetupController;
use App\Http\Controllers\Admin\LessonAdminController;
use App\Http\Controllers\Admin\ModuleAdminController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\TeamAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CourseCatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\LessonCommentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonProgressController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamPostController;
use App\Http\Controllers\TeamWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('courses.index'))->name('home');

Route::get('locale/{locale}', LocaleController::class)->name('locale.switch');

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
    Route::post('courses/{course}/purchase', [CourseCatalogController::class, 'purchase'])->name('courses.purchase');

    Route::middleware('enrolled.course')->group(function () {
        Route::get('lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
        Route::post('lessons/{lesson}/progress', [LessonProgressController::class, 'update'])->name('lessons.progress');
        Route::post('lessons/{lesson}/comments', [LessonCommentController::class, 'store'])->name('lessons.comments.store');
    });

    Route::get('profiles/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('profiles', [ProfileController::class, 'update'])->name('profiles.update');
    Route::put('profiles/password', [ProfileController::class, 'updatePassword'])->name('profiles.password');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('forums', [ForumController::class, 'index'])->name('forums.index');
    Route::get('forums/{forumCategory}', [ForumController::class, 'category'])->name('forums.category');
    Route::get('forums/{forumCategory}/new', [ForumController::class, 'createThread'])->name('forums.threads.create');
    Route::post('forums/{forumCategory}', [ForumController::class, 'storeThread'])->name('forums.threads.store');
    Route::get('forums/{forumCategory}/t/{forumThread}', [ForumController::class, 'thread'])->name('forums.thread');
    Route::post('forums/{forumCategory}/t/{forumThread}/posts', [ForumController::class, 'storePost'])->name('forums.posts.store');

    Route::get('teams', [TeamWorkspaceController::class, 'index'])->name('teams.index');
    Route::get('teams/{team}', [TeamWorkspaceController::class, 'show'])->name('teams.show');
    Route::get('teams/{team}/general', [TeamWorkspaceController::class, 'general'])->name('teams.general');
    Route::post('teams/{team}/posts', [TeamPostController::class, 'store'])->name('teams.posts.store');
    Route::post('teams/{team}/posts/{post}/replies', [TeamPostController::class, 'reply'])->name('teams.posts.replies.store');
    Route::post('teams/{team}/members', [TeamWorkspaceController::class, 'attachStudents'])->name('teams.members.attach');
    Route::delete('teams/{team}/members/{member}', [TeamWorkspaceController::class, 'detachMember'])->name('teams.members.detach');

    Route::get('teams/{team}/assignments/create', [AssignmentController::class, 'create'])->name('teams.assignments.create');
    Route::post('teams/{team}/assignments', [AssignmentController::class, 'store'])->name('teams.assignments.store');
    Route::get('teams/{team}/assignments/{assignment}', [AssignmentController::class, 'show'])->name('teams.assignments.show');
    Route::get('teams/{team}/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('teams.assignments.edit');
    Route::put('teams/{team}/assignments/{assignment}', [AssignmentController::class, 'update'])->name('teams.assignments.update');
    Route::post('teams/{team}/assignments/{assignment}/close', [AssignmentController::class, 'close'])->name('teams.assignments.close');
    Route::delete('teams/{team}/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('teams.assignments.destroy');
    Route::post('teams/{team}/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('teams.assignments.submit');
    Route::post('teams/{team}/assignments/{assignment}/submissions/{submission}/return', [AssignmentController::class, 'returnSubmission'])->name('teams.assignments.return');
    Route::get('teams/{team}/assignments/{assignment}/attachments/{attachment}/download', [AssignmentController::class, 'downloadAttachment'])->name('teams.assignments.attachments.download');
    Route::get('teams/{team}/assignments/{assignment}/submissions/{submission}/files/{file}/download', [AssignmentController::class, 'downloadSubmissionFile'])->name('teams.assignments.files.download');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('monitoring/lessons', [MonitoringController::class, 'lessons'])->name('monitoring.lessons');
    Route::get('monitoring/forums', [MonitoringController::class, 'forums'])->name('monitoring.forums');
    Route::get('monitoring/courses', [MonitoringController::class, 'courses'])->name('monitoring.courses');
    Route::get('users/{user}/monitoring', [MonitoringController::class, 'user'])->name('monitoring.user');

    Route::get('users', [UserAdminController::class, 'index'])->name('users.index');
    Route::post('users', [UserAdminController::class, 'store'])->name('users.store');
    Route::get('teams/{team}', [TeamAdminController::class, 'show'])->name('teams.show');
    Route::post('teams', [TeamAdminController::class, 'store'])->name('teams.store');
    Route::put('teams/{team}', [TeamAdminController::class, 'update'])->name('teams.update');
    Route::post('teams/{team}/members', [TeamAdminController::class, 'attachMembers'])->name('teams.members.attach');
    Route::delete('teams/{team}/members/{user}', [TeamAdminController::class, 'detachMember'])->name('teams.members.detach');
    Route::delete('teams/{team}', [TeamAdminController::class, 'destroy'])->name('teams.destroy');
    Route::get('users/{user}', [UserAdminController::class, 'show'])->name('users.show');
    Route::post('users/{user}/approve', [UserAdminController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/revoke', [UserAdminController::class, 'revoke'])->name('users.revoke');
    Route::put('users/{user}/role', [UserAdminController::class, 'updateRole'])->name('users.role');
    Route::put('users/{user}/password', [UserAdminController::class, 'updatePassword'])->name('users.password');
    Route::delete('users/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');

    Route::get('purchases', [CoursePurchaseAdminController::class, 'index'])->name('purchases.index');
    Route::post('purchases/{purchase}/approve', [CoursePurchaseAdminController::class, 'approve'])->name('purchases.approve');
    Route::post('purchases/{purchase}/reject', [CoursePurchaseAdminController::class, 'reject'])->name('purchases.reject');
    Route::get('purchases/{purchase}/slip', [CoursePurchaseAdminController::class, 'slip'])->name('purchases.slip');

    Route::get('banks', [BankAccountAdminController::class, 'index'])->name('banks.index');
    Route::post('banks', [BankAccountAdminController::class, 'store'])->name('banks.store');
    Route::put('banks/{bank}', [BankAccountAdminController::class, 'update'])->name('banks.update');
    Route::delete('banks/{bank}', [BankAccountAdminController::class, 'destroy'])->name('banks.destroy');

    Route::get('courses', [CourseAdminController::class, 'index'])->name('courses.index');
    Route::get('courses/create', [CourseAdminController::class, 'create'])->name('courses.create');
    Route::post('courses', [CourseAdminController::class, 'store'])->name('courses.store');
    Route::get('courses/{course}/edit', [CourseAdminController::class, 'edit'])->name('courses.edit');
    Route::put('courses/{course}', [CourseAdminController::class, 'update'])->name('courses.update');
    Route::put('courses/{course}/access', [CourseAccessAdminController::class, 'update'])->name('courses.access.update');
    Route::delete('courses/{course}', [CourseAdminController::class, 'destroy'])->name('courses.destroy');
    Route::put('courses/{course}/modules/reorder', [CourseStructureController::class, 'reorderModules'])->name('modules.reorder');
    Route::put('courses/{course}/modules/{module}/lessons/reorder', [CourseStructureController::class, 'reorderLessons'])->name('lessons.reorder');

    Route::post('courses/{course}/modules', [ModuleAdminController::class, 'store'])->name('modules.store');
    Route::delete('courses/{course}/modules/{module}', [ModuleAdminController::class, 'destroy'])->name('modules.destroy');

    Route::get('courses/{course}/modules/{module}/lessons/create', [LessonAdminController::class, 'create'])->name('lessons.create');
    Route::post('courses/{course}/modules/{module}/lessons', [LessonAdminController::class, 'store'])->name('lessons.store');
    Route::get('courses/{course}/modules/{module}/lessons/{lesson}/edit', [LessonAdminController::class, 'edit'])->name('lessons.edit');
    Route::put('courses/{course}/modules/{module}/lessons/{lesson}', [LessonAdminController::class, 'update'])->name('lessons.update');
    Route::delete('courses/{course}/modules/{module}/lessons/{lesson}', [LessonAdminController::class, 'destroy'])->name('lessons.destroy');

    Route::get('forums/categories', [ForumSetupController::class, 'categories'])->name('forums.categories');
    Route::post('forums/categories', [ForumSetupController::class, 'storeCategory'])->name('forums.categories.store');
    Route::put('forums/categories/reorder', [ForumSetupController::class, 'reorderCategories'])->name('forums.categories.reorder');
    Route::put('forums/categories/{forumCategory}', [ForumSetupController::class, 'updateCategory'])->name('forums.categories.update');
    Route::delete('forums/categories/{forumCategory}', [ForumSetupController::class, 'destroyCategory'])->name('forums.categories.destroy');
    Route::get('forums/tags', [ForumSetupController::class, 'tags'])->name('forums.tags');
    Route::post('forums/tags', [ForumSetupController::class, 'storeTag'])->name('forums.tags.store');
    Route::put('forums/tags/{tag}', [ForumSetupController::class, 'updateTag'])->name('forums.tags.update');
    Route::delete('forums/tags/{tag}', [ForumSetupController::class, 'destroyTag'])->name('forums.tags.destroy');
});
