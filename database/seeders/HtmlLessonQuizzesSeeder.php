<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

/**
 * Lesson quizzes aligned to HTML #1 … HTML #14 by lesson title (not list order).
 *
 *   php artisan db:seed --class=HtmlLessonQuizzesSeeder
 *
 * Replaces existing lesson-only quizzes on matching lessons. Resolves course by slug
 * or by title containing "Learn HTML".
 */
class HtmlLessonQuizzesSeeder extends Seeder
{
    /** Try these slugs first; then title fallback */
    private const COURSE_SLUGS = ['html-complete', 'learn-html-step-by-step-9oxV'];

    /**
     * PACKS[n] = questions for HTML #(n+1). Must stay length 14.
     *
     * @var list<list{array{string, string, string, string}}>
     */
    private const PACKS = [
        // #1 Intro
        [
            ['What does HTML stand for?', 'Hyper Text Markup Language', 'Hyper Transfer Markup Language', 'High Text Machine Language'],
            ['Which tool runs HTML in the learner workflow?', 'A web browser', 'Only Microsoft Word', 'A SQL database'],
            ['HTML files are typically saved with which extension?', '.html', '.exe', '.mp4'],
        ],
        // #2 Basic frame
        [
            ['Which tag is the root element of an HTML page?', '<html>', '<body>', '<root>'],
            ['Which section usually holds the page title and meta tags?', '<head>', '<footer>', '<main>'],
            ['Where does visible page content usually go?', 'Inside <body>', 'Inside <head> only', 'Inside <style> only'],
        ],
        // #3 Headings
        [
            ['Which tag creates the top-level heading?', '<h1>', '<h6>', '<head>'],
            ['Heading levels typically run from…', '<h1> through <h6>', '<h0> through <h10>', 'Only <h1> exists'],
            ['Which heading is usually the largest by default?', '<h1>', '<h6>', '<p>'],
        ],
        // #4 Bold, italic, underline
        [
            ['Which tag often represents bold text in classic HTML?', '<b>', '<br>', '<bold>'],
            ['Which tag often represents italic text?', '<i>', '<italic>', '<em> is the only option'],
            ['Underline is commonly done with…', '<u>', '<underline>', '<ul>'],
        ],
        // #5 Center, font, color
        [
            ['Which tag was historically used to center content (now prefer CSS)?', '<center>', '<middle>', '<c>'],
            ['Changing font face/size/color in old HTML often used…', '<font>', '<text>', '<typography>'],
            ['For modern sites, styling should primarily be done with…', 'CSS', 'Only HTML attributes forever', 'SQL'],
        ],
        // #6 bgcolor, hr, p, pre
        [
            ['Which tag creates a horizontal rule line?', '<hr>', '<line>', '<rule>'],
            ['Which tag defines a paragraph block?', '<p>', '<paragraph>', '<para>'],
            ['Which tag preserves spaces and line breaks as typed?', '<pre>', '<p>', '<code> only'],
        ],
        // #7 List
        [
            ['Which tag starts an unordered (bulleted) list?', '<ul>', '<ol>', '<list>'],
            ['Which tag starts a numbered list?', '<ol>', '<ul>', '<li>'],
            ['Each list item is usually wrapped in…', '<li>', '<item>', '<entry>'],
        ],
        // #8 Alignment
        [
            ['Aligning blocks with HTML align attributes is…', 'Legacy — prefer CSS in modern pages', 'The only valid method', 'Handled by SQL'],
            ['In modern layouts, horizontal alignment is usually done with…', 'CSS (e.g. flexbox / text-align)', 'Only <center>', 'The <align> tag'],
            ['The align attribute on legacy elements was used to…', 'Position or align content', 'Encrypt passwords', 'Compile TypeScript'],
        ],
        // #9 Marquee
        [
            ['The <marquee> element is considered…', 'Obsolete / legacy — avoid for new pages', 'The best way to animate in 2025', 'Required for accessibility'],
            ['Modern scrolling or motion effects should usually be built with…', 'CSS animations or JS', 'Only <marquee>', 'Server redirects'],
            ['Why is <marquee> discouraged?', 'It hurts UX/accessibility and is non-standard', 'It is too fast', 'Browsers cannot render it'],
        ],
        // #10 Image
        [
            ['Which tag embeds an image?', '<img>', '<image>', '<picture> only'],
            ['The image URL is usually placed in which attribute?', 'src', 'href', 'link'],
            ['Which attribute provides text for screen readers when the image is meaningful?', 'alt', 'title', 'caption'],
        ],
        // #11 Link
        [
            ['Which tag creates a hyperlink?', '<a>', '<link>', '<href>'],
            ['The destination URL is stored in which attribute?', 'href', 'src', 'url'],
            ['To open a link in a new tab, a common approach is…', 'target="_blank" (with rel security where needed)', 'newtab="true"', 'open="new"'],
        ],
        // #12 Table
        [
            ['Which tag defines a table row?', '<tr>', '<td>', '<row>'],
            ['Which tag defines a normal data cell?', '<td>', '<th>', '<cell>'],
            ['Which tag defines a header cell?', '<th>', '<td>', '<header>'],
        ],
        // #13 Form
        [
            ['Which tag wraps interactive form controls?', '<form>', '<fieldset> only', '<input> alone'],
            ['Which attribute on <form> defines where data is sent?', 'action', 'method only', 'src'],
            ['Which input type is used for a single-line text field?', 'text (type="text")', 'paragraph', 'string'],
        ],
        // #14 IFrame
        [
            ['Which tag embeds another HTML page inside the current page?', '<iframe>', '<frame> only', '<embed> only'],
            ['The page URL loaded inside an iframe is usually set with…', 'src', 'href', 'url'],
            ['Embedding untrusted sites in iframes requires…', 'Care with security (HTTPS, sandboxing, CSP)', 'No considerations', 'A database join'],
        ],
    ];

    public static function htmlNumberFromTitle(string $title): ?int
    {
        if (preg_match('/HTML\s*#\s*(\d+)/i', $title, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    public function run(): void
    {
        $course = $this->resolveCourse();
        if (! $course) {
            $this->command?->error('No HTML course found. Create one or run HtmlCourseStructureSeeder.');

            return;
        }

        $byNumber = [];
        foreach ($course->modules()->with(['lessons' => fn ($q) => $q->orderBy('sort_order')])->get() as $module) {
            foreach ($module->lessons as $lesson) {
                $n = self::htmlNumberFromTitle($lesson->title);
                if ($n === null || $n < 1 || $n > 14) {
                    continue;
                }
                if (isset($byNumber[$n])) {
                    $this->command?->warn("Duplicate HTML #{$n}; using first lesson id {$byNumber[$n]->id}, skipping lesson id {$lesson->id}");

                    continue;
                }
                $byNumber[$n] = $lesson;
            }
        }

        ksort($byNumber);

        for ($n = 1; $n <= 14; $n++) {
            if (! isset($byNumber[$n])) {
                $this->command?->warn("No lesson found for HTML #{$n}; skipping quiz.");

                continue;
            }

            $lesson = $byNumber[$n];
            $pack = self::PACKS[$n - 1];

            Quiz::query()->where('lesson_id', $lesson->id)->whereNull('module_id')->delete();

            $questionIds = [];
            foreach ($pack as $qi => $row) {
                [$body, $correct, $w1, $w2] = $row;
                $q = Question::query()->create([
                    'technology' => 'HTML',
                    'topic' => 'Lesson check',
                    'body' => $body,
                    'type' => 'mcq',
                ]);
                $questionIds[] = $q->id;

                $options = [
                    ['body' => $correct, 'is_correct' => true],
                    ['body' => $w1, 'is_correct' => false],
                    ['body' => $w2, 'is_correct' => false],
                ];
                // Avoid every correct answer at sort_order 0 (always "A"): rotate per lesson + slot.
                $rotate = ($n + $qi) % 3;
                if ($rotate > 0) {
                    $options = array_merge(array_slice($options, $rotate), array_slice($options, 0, $rotate));
                }

                foreach ($options as $i => $opt) {
                    QuestionOption::query()->create([
                        'question_id' => $q->id,
                        'body' => $opt['body'],
                        'is_correct' => $opt['is_correct'],
                        'sort_order' => $i,
                    ]);
                }
            }

            $quiz = Quiz::query()->create([
                'lesson_id' => $lesson->id,
                'module_id' => null,
                'title' => 'Check — '.$lesson->title,
                'pass_threshold_percent' => 70,
            ]);

            foreach ($questionIds as $sort => $qid) {
                $quiz->questions()->attach($qid, ['sort_order' => $sort]);
            }

            $this->command?->info("Quiz HTML #{$n}: {$lesson->title}");
        }
    }

    private function resolveCourse(): ?Course
    {
        foreach (self::COURSE_SLUGS as $slug) {
            $c = Course::query()->where('slug', $slug)->first();
            if ($c) {
                return $c;
            }
        }

        return Course::query()->where('title', 'like', '%Learn HTML%')->orderBy('id')->first();
    }
}
