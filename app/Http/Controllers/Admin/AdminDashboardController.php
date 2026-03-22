<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Question;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $courseCount = Course::query()->count();
        $questionCount = Question::query()->count();

        return view('admin.dashboard', compact('courseCount', 'questionCount'));
    }
}
