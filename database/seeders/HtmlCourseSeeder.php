<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Aligned with YouTube playlist **Learn HTML – Step by Step**:
 * https://www.youtube.com/playlist?list=PLGwzwzSIMCmc_aR4lHJcxpNFoVuuc3UHs
 *
 * Readings reference W3Schools (https://www.w3schools.com/html/); the video is the primary walkthrough.
 * Quizzes: lesson checks on several videos (basics + lists/links/tables) plus module recaps after modules 2 & 4.
 */
class HtmlCourseSeeder extends Seeder
{
    private const PLAYLIST_URL = 'https://www.youtube.com/playlist?list=PLGwzwzSIMCmc_aR4lHJcxpNFoVuuc3UHs';

    public function run(): void
    {
        $student = User::query()->where('email', 'student@example.com')->first();
        if (! $student) {
            return;
        }

        if (Course::query()->where('slug', 'html-complete')->exists()) {
            return;
        }

        $w3 = 'https://www.w3schools.com/html/default.asp';

        $course = Course::query()->create([
            'title' => 'Learn HTML — Step by Step',
            'slug' => 'html-complete',
            'description' => 'Follows the playlist **Learn HTML – Step by Step** with readings keyed to W3Schools. Playlist: '.self::PLAYLIST_URL.' · Docs hub: '.$w3,
            'is_published' => true,
        ]);

        // Order = playlist order (same as YouTube). Durations from playlist metadata.
        $playlistLessons = [
            // Module 1 — Introduction & document shape
            ['title' => 'HTML #1 – Intro & Environment Setup', 'video_id' => '-zUNKAdENMw', 'seconds' => 109, 'md' => $this->md('Intro & setup', 'https://www.w3schools.com/html/html_intro.asp', 'What HTML is for, editor/browser basics — covered in depth in the video.')],
            ['title' => 'HTML #2 – Basic Frame & Names', 'video_id' => '-9VeILb9z1I', 'seconds' => 529, 'md' => $this->md('Document frame', 'https://www.w3schools.com/html/html_basic.asp', 'Skeleton of an HTML page: `html`, `head`, `body`, and naming conventions.')],
            ['title' => 'HTML #3 – Header Tag', 'video_id' => 'UO0Dzc-8Bvc', 'seconds' => 264, 'md' => $this->md('Headings', 'https://www.w3schools.com/html/html_headings.asp', 'Heading levels `<h1>`–`<h6>` and when to use them.')],
            // Module 2 — Text & presentation (classic HTML)
            ['title' => 'HTML #4 – Bold, Italic, Underline', 'video_id' => 'qpYMJ-Rz874', 'seconds' => 279, 'md' => $this->md('Text emphasis', 'https://www.w3schools.com/html/html_formatting.asp', 'Bold, italic, underline and related formatting.')],
            ['title' => 'HTML #5 – Center, font size and color', 'video_id' => 'R-qPtE3b07E', 'seconds' => 724, 'md' => $this->md('Alignment & font (classic)', 'https://www.w3schools.com/html/html_styles.asp', 'Centering, font size/color as shown in the lesson (prefer CSS in modern sites).')],
            ['title' => 'HTML #6 – bgcolor, hr color and size, p and pre', 'video_id' => 'sqthG6ZmPOM', 'seconds' => 930, 'md' => $this->md('Background, hr, paragraphs', 'https://www.w3schools.com/html/html_paragraphs.asp', 'Page background, horizontal rules, `<p>` vs `<pre>` for preformatted text.')],
            // Module 3 — Lists, marquee, images
            ['title' => 'HTML #7 – List', 'video_id' => 'fSeR-RO9p90', 'seconds' => 332, 'md' => $this->md('Lists', 'https://www.w3schools.com/html/html_lists.asp', 'Ordered vs unordered lists and nesting.')],
            ['title' => 'HTML #9 – Marquee', 'video_id' => '48tFuPiXmXI', 'seconds' => 685, 'md' => $this->md('Marquee (legacy)', 'https://www.w3schools.com/html/default.asp', 'Legacy scrolling text (obsolete in modern HTML — know it for maintenance; use CSS animations for new work).')],
            ['title' => 'HTML #10 – Image', 'video_id' => 'Ne-ELosUWnQ', 'seconds' => 516, 'md' => $this->md('Images', 'https://www.w3schools.com/html/html_images.asp', '`<img src=\"\" alt=\"\">` and why `alt` matters.')],
            // Module 4 — Links, alignment, tables
            ['title' => 'HTML #11 – A Link', 'video_id' => 'OcXllp35riM', 'seconds' => 527, 'md' => $this->md('Links', 'https://www.w3schools.com/html/html_links.asp', 'The `<a>` tag, `href`, same-tab vs new tab.')],
            ['title' => 'HTML #8 – Alignment Attribute', 'video_id' => 'Pb5y0Ncj9LM', 'seconds' => 196, 'md' => $this->md('Alignment', 'https://www.w3schools.com/html/html_styles.asp', 'Aligning blocks and inline content (in modern pages, CSS is preferred).')],
            ['title' => 'HTML #12 – Table', 'video_id' => 'dws6m7WQzVI', 'seconds' => 538, 'md' => $this->md('Tables', 'https://www.w3schools.com/html/html_tables.asp', 'Rows, cells, headers — use tables for tabular data, not arbitrary layout.')],
        ];

        $moduleTitles = [
            'Module 1 — Introduction & document shape',
            'Module 2 — Text & classic presentation',
            'Module 3 — Lists, marquee & images',
            'Module 4 — Links, alignment & tables',
        ];

        $lessonModels = [];
        $moduleModels = [];

        foreach (array_chunk($playlistLessons, 3) as $mi => $chunk) {
            $module = Module::query()->create([
                'course_id' => $course->id,
                'sort_order' => $mi + 1,
                'title' => $moduleTitles[$mi],
            ]);
            $moduleModels[] = $module;

            foreach ($chunk as $li => $spec) {
                $lessonModels[] = Lesson::query()->create([
                    'module_id' => $module->id,
                    'sort_order' => $li + 1,
                    'title' => $spec['title'],
                    'video_driver' => 'youtube',
                    'video_ref' => $spec['video_id'],
                    'duration_seconds' => $spec['seconds'],
                    'documentation_markdown' => $spec['md'],
                ]);
            }
        }

        // Question bank (HTML) — topics spread across module / lesson quizzes
        $bankDefs = [
            ['What does HTML stand for?', 'Hyper Text Markup Language', 'Hyperlinks and Text Markup Language', 'Home Tool Markup Language'],
            ['Which tag usually denotes the largest heading?', '<h1>', '<h6>', '<head>'],
            ['Which tag typically wraps the visible page content?', '<body>', '<head>', '<html>'],
            ['In classic HTML, which tag often makes text bold?', '<b>', '<i>', '<u>'],
            ['Which tag creates a paragraph block?', '<p>', '<paragraph>', '<para>'],
            ['Which tag creates an unordered (bulleted) list?', '<ul>', '<ol>', '<list>'],
            ['Which tag marks each item inside a list?', '<li>', '<item>', '<entry>'],
            ['Which attribute on <a> holds the link destination?', 'href', 'src', 'link'],
            ['Which tag embeds an image?', '<img>', '<image>', '<picture>'],
            ['For accessibility, images should usually include which attribute?', 'alt', 'title', 'caption'],
            ['Which tag defines a row in a table?', '<tr>', '<td>', '<row>'],
            ['Which tag defines a header cell in a table?', '<th>', '<td>', '<thead>'],
            ['Marquee-style effects in new sites should usually be done with…', 'CSS animations', 'Only <marquee>', 'Server redirects'],
        ];

        $questionIds = [];
        foreach ($bankDefs as $row) {
            $qText = $row[0];
            $correct = $row[1];
            $opts = array_slice($row, 1, 3);
            $topic = match (true) {
                str_contains($qText, 'stand for') => 'Basics',
                str_contains($qText, 'heading') => 'Structure',
                str_contains($qText, 'body') => 'Structure',
                str_contains($qText, 'bold') => 'Text',
                str_contains($qText, 'paragraph') => 'Text',
                str_contains($qText, 'unordered') || str_contains($qText, 'item inside') => 'Lists',
                str_contains($qText, '<a>') || str_contains($qText, 'href') => 'Links',
                str_contains($qText, 'embeds an image') || str_contains($qText, 'accessibility') => 'Media',
                str_contains($qText, 'row') || str_contains($qText, 'header cell') => 'Tables',
                str_contains($qText, 'Marquee') => 'Legacy',
                default => 'HTML',
            };

            $q = Question::query()->create([
                'technology' => 'HTML',
                'topic' => $topic,
                'body' => $qText,
                'type' => 'mcq',
            ]);
            $questionIds[] = $q->id;
            foreach ($opts as $i => $text) {
                QuestionOption::query()->create([
                    'question_id' => $q->id,
                    'body' => $text,
                    'is_correct' => $text === $correct,
                    'sort_order' => $i,
                ]);
            }
        }

        // Lesson quizzes — indices = lesson index in $lessonModels (same order as $playlistLessons).
        $quizLessonIndexes = [
            2 => [0, 1], // #3 Headings
            3 => [0, 2], // #4 Bold — basics + body
            5 => [3, 4], // #6 — paragraphs & text
            6 => [5, 6], // #7 List — ul, li
            9 => [7, 4], // #11 Link — href + paragraph
            11 => [10, 11], // #12 Table — tr, th
        ];

        foreach ($quizLessonIndexes as $lessonIdx => $qIndexes) {
            $lesson = $lessonModels[$lessonIdx];
            $quiz = Quiz::query()->create([
                'lesson_id' => $lesson->id,
                'module_id' => null,
                'title' => 'Check — '.$lesson->title,
                'pass_threshold_percent' => 70,
            ]);
            foreach ($qIndexes as $sort => $qBankIdx) {
                $quiz->questions()->attach($questionIds[$qBankIdx], ['sort_order' => $sort]);
            }
        }

        // Module quizzes — end of module 2 & 4 only (not every module).
        $moduleQuizMap = [
            1 => [0, 1, 2, 3, 4], // Module 2: basics + text
            3 => [7, 9, 10, 11, 12], // Module 4: links, media, tables, legacy
        ];

        foreach ($moduleQuizMap as $modIdx => $qIndexes) {
            $mod = $moduleModels[$modIdx];
            $quiz = Quiz::query()->create([
                'lesson_id' => null,
                'module_id' => $mod->id,
                'title' => 'Module '.($modIdx + 1).' — recap',
                'pass_threshold_percent' => 70,
            ]);
            foreach ($qIndexes as $sort => $qBankIdx) {
                $quiz->questions()->attach($questionIds[$qBankIdx], ['sort_order' => $sort]);
            }
        }

        $student->enrollments()->firstOrCreate(['course_id' => $course->id]);
    }

    private function md(string $heading, string $w3Url, string $summary): string
    {
        return "## {$heading}\n\n{$summary}\n\n**Reference:** [W3Schools]({$w3Url}) · Playlist: ".self::PLAYLIST_URL;
    }
}
