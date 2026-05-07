# Company-Authored LMS Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a relational LMS where company users author courses, chapters, lessons, and quizzes, while students enroll, complete content, pass company-scored quizzes, and receive calculated progress.

**Architecture:** Replace the prototype JSON-backed LMS with normalized Laravel models and migrations. Add company CRUD routes under `perusahaan.*`, student enrollment/progress actions under authenticated mahasiswa routes, and keep `/lms` plus `/lms/module/{course}` backed by relational data.

**Tech Stack:** Laravel 12, Inertia Laravel, Vue 3, Tailwind CSS, PHPUnit feature tests, MySQL/SQLite-compatible migrations.

---

## File Structure

- Create `database/migrations/2026_05_04_180000_rebuild_lms_relational_tables.php` for relational LMS tables and removal of prototype `chapters` usage.
- Modify `app/Models/LmsCourse.php` to add ownership, status constants, relationships, route key, and progress helpers.
- Create models: `LmsChapter`, `LmsLesson`, `LmsQuiz`, `LmsQuizQuestion`, `LmsQuizOption`, `LmsEnrollment`, `LmsLessonCompletion`, `LmsQuizAttempt`, `LmsChapterCompletion`.
- Create `app/Services/LmsProgressService.php` to centralize scoring, chapter completion, and course progress.
- Modify `database/seeders/LmsCourseSeeder.php` to seed relational data.
- Modify `app/Http/Controllers/LmsController.php` for relational listing/module payloads and enrollment-aware progress.
- Create `app/Http/Controllers/CompanyLmsCourseController.php` for company course CRUD.
- Create `app/Http/Controllers/CompanyLmsContentController.php` for chapter, lesson, and quiz editing.
- Create `app/Http/Controllers/LmsEnrollmentController.php`, `LmsLessonCompletionController.php`, and `LmsQuizAttemptController.php`.
- Modify `routes/web.php` to add company LMS management and student LMS action routes.
- Create Vue pages under `resources/js/Pages/Perusahaan/Lms/`: `Index.vue`, `Form.vue`, `Builder.vue`.
- Modify `resources/js/Pages/Features/Lms.vue` and `resources/js/Pages/Features/LmsModule.vue` for enrollment, relational chapters, completion, and quiz submission.
- Extend `tests/Feature/Sikara/LmsFrontendContractTest.php`.
- Create `tests/Feature/Sikara/LmsCompanyAuthoringTest.php`.
- Create `tests/Feature/Sikara/LmsStudentProgressTest.php`.

---

### Task 1: Relational LMS Database And Models

**Files:**
- Create: `database/migrations/2026_05_04_180000_rebuild_lms_relational_tables.php`
- Modify: `app/Models/LmsCourse.php`
- Create: `app/Models/LmsChapter.php`
- Create: `app/Models/LmsLesson.php`
- Create: `app/Models/LmsQuiz.php`
- Create: `app/Models/LmsQuizQuestion.php`
- Create: `app/Models/LmsQuizOption.php`
- Create: `app/Models/LmsEnrollment.php`
- Create: `app/Models/LmsLessonCompletion.php`
- Create: `app/Models/LmsQuizAttempt.php`
- Create: `app/Models/LmsChapterCompletion.php`
- Test: `tests/Feature/Sikara/LmsCompanyAuthoringTest.php`

- [ ] **Step 1: Write the failing model relationship test**

Add this test:

```php
<?php

namespace Tests\Feature\Sikara;

use App\Models\LmsChapter;
use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsQuizOption;
use App\Models\LmsQuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsCompanyAuthoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_lms_relational_models_can_store_company_course_content_and_student_enrollment(): void
    {
        $company = User::factory()->perusahaan()->create();
        $student = User::factory()->mahasiswa()->create();

        $course = LmsCourse::query()->create([
            'company_id' => $company->id,
            'slug' => 'cloud-computing',
            'title' => 'KOMPUTASI AWAN SI-47-06',
            'provider' => 'SOUTH BANDUNG INFORMATION...',
            'description' => 'Belajar cloud computing.',
            'level' => 'INTERMEDIATE',
            'status' => 'published',
            'image_url' => 'https://example.com/cloud.jpg',
            'image_alt' => 'Cloud computing',
        ]);

        $chapter = LmsChapter::query()->create([
            'course_id' => $course->id,
            'title' => 'Bab 1: Pendahuluan',
            'description' => 'Dasar pembelajaran.',
            'position' => 1,
        ]);

        $lesson = LmsLesson::query()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Video: Pengenalan Cloud',
            'type' => 'video',
            'content' => 'Konten video.',
            'position' => 1,
        ]);

        $quiz = LmsQuiz::query()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Kuis Pendahuluan',
            'description' => 'Kuis bab 1.',
            'passing_score' => 75,
        ]);

        $question = LmsQuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'question' => 'Apa itu cloud?',
            'position' => 1,
        ]);

        LmsQuizOption::query()->create([
            'question_id' => $question->id,
            'option_text' => 'Layanan komputasi melalui internet',
            'is_correct' => true,
            'position' => 1,
        ]);

        $enrollment = LmsEnrollment::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'enrolled_at' => now(),
        ]);

        $this->assertTrue($course->company->is($company));
        $this->assertTrue($course->chapters->first()->is($chapter));
        $this->assertTrue($chapter->lessons->first()->is($lesson));
        $this->assertTrue($chapter->quiz->is($quiz));
        $this->assertTrue($quiz->questions->first()->is($question));
        $this->assertTrue($course->enrollments->first()->is($enrollment));
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run:

```bash
php artisan test --filter=LmsCompanyAuthoringTest
```

Expected: FAIL because the new models and relational columns do not exist.

- [ ] **Step 3: Create the migration**

Create `database/migrations/2026_05_04_180000_rebuild_lms_relational_tables.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            if (! Schema::hasColumn('lms_courses', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('lms_courses', 'description')) {
                $table->text('description')->nullable()->after('provider');
            }
        });

        Schema::create('lms_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('lms_chapters')->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('article');
            $table->longText('content')->nullable();
            $table->text('video_url')->nullable();
            $table->text('video_image_url')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->unique()->constrained('lms_chapters')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('passing_score')->default(70);
            $table->timestamps();
        });

        Schema::create('lms_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->text('question');
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('lms_quiz_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('lms_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'student_id']);
        });

        Schema::create('lms_lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lms_lessons')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'lesson_id']);
        });

        Schema::create('lms_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->boolean('passed')->default(false);
            $table->json('answers');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lms_chapter_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
            $table->foreignId('chapter_id')->constrained('lms_chapters')->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'chapter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_chapter_completions');
        Schema::dropIfExists('lms_quiz_attempts');
        Schema::dropIfExists('lms_lesson_completions');
        Schema::dropIfExists('lms_enrollments');
        Schema::dropIfExists('lms_quiz_options');
        Schema::dropIfExists('lms_quiz_questions');
        Schema::dropIfExists('lms_quizzes');
        Schema::dropIfExists('lms_lessons');
        Schema::dropIfExists('lms_chapters');
    }
};
```

- [ ] **Step 4: Add model relationships**

Update `app/Models/LmsCourse.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsCourse extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'company_id',
        'slug',
        'title',
        'provider',
        'description',
        'level',
        'status',
        'started_at',
        'ends_at',
        'image_url',
        'image_alt',
        'chapters',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ends_at' => 'date',
            'chapters' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(LmsChapter::class, 'course_id')->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LmsEnrollment::class, 'course_id');
    }
}
```

Create the other model files with these relationships:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LmsChapter extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'position'];

    public function course(): BelongsTo { return $this->belongsTo(LmsCourse::class, 'course_id'); }
    public function lessons(): HasMany { return $this->hasMany(LmsLesson::class, 'chapter_id')->orderBy('position'); }
    public function quiz(): HasOne { return $this->hasOne(LmsQuiz::class, 'chapter_id'); }
}
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsLesson extends Model
{
    protected $fillable = ['chapter_id', 'title', 'type', 'content', 'video_url', 'video_image_url', 'position'];

    public function chapter(): BelongsTo { return $this->belongsTo(LmsChapter::class, 'chapter_id'); }
}
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsQuiz extends Model
{
    protected $fillable = ['chapter_id', 'title', 'description', 'passing_score'];

    public function chapter(): BelongsTo { return $this->belongsTo(LmsChapter::class, 'chapter_id'); }
    public function questions(): HasMany { return $this->hasMany(LmsQuizQuestion::class, 'quiz_id')->orderBy('position'); }
}
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsQuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'question', 'position'];

    public function quiz(): BelongsTo { return $this->belongsTo(LmsQuiz::class, 'quiz_id'); }
    public function options(): HasMany { return $this->hasMany(LmsQuizOption::class, 'question_id')->orderBy('position'); }
}
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsQuizOption extends Model
{
    protected $fillable = ['question_id', 'option_text', 'is_correct', 'position'];

    protected function casts(): array { return ['is_correct' => 'boolean']; }
    public function question(): BelongsTo { return $this->belongsTo(LmsQuizQuestion::class, 'question_id'); }
}
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsEnrollment extends Model
{
    protected $fillable = ['course_id', 'student_id', 'enrolled_at', 'completed_at'];

    protected function casts(): array { return ['enrolled_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function course(): BelongsTo { return $this->belongsTo(LmsCourse::class, 'course_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function lessonCompletions(): HasMany { return $this->hasMany(LmsLessonCompletion::class, 'enrollment_id'); }
    public function quizAttempts(): HasMany { return $this->hasMany(LmsQuizAttempt::class, 'enrollment_id'); }
    public function chapterCompletions(): HasMany { return $this->hasMany(LmsChapterCompletion::class, 'enrollment_id'); }
}
```

Create `LmsLessonCompletion`, `LmsQuizAttempt`, and `LmsChapterCompletion` with fillable fields matching their table columns and `BelongsTo` relationships to enrollment plus lesson/quiz/chapter.

- [ ] **Step 5: Run test to verify it passes**

Run:

```bash
php artisan test --filter=LmsCompanyAuthoringTest
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_04_180000_rebuild_lms_relational_tables.php app/Models tests/Feature/Sikara/LmsCompanyAuthoringTest.php
git commit -m "feat: add relational LMS data model"
```

---

### Task 2: Relational Seeder And Student-Facing Payloads

**Files:**
- Modify: `database/seeders/LmsCourseSeeder.php`
- Modify: `app/Http/Controllers/LmsController.php`
- Test: `tests/Feature/Sikara/LmsFrontendContractTest.php`

- [ ] **Step 1: Write failing tests for relational payloads**

Update `test_lms_page_lists_seeded_learning_courses` to assert `is_enrolled`, `progress`, and relational chapters:

```php
$this->get('/lms')
    ->assertOk()
    ->assertInertia(fn (Assert $page) => $page
        ->component('Features/Lms')
        ->has('courses', 3)
        ->where('courses.0.slug', 'cloud-computing')
        ->where('courses.0.progress', 0)
        ->where('courses.0.is_enrolled', false)
    );
```

Update `test_lms_module_page_shows_seeded_course_content` to assert relational quiz data:

```php
$this->get('/lms/module/cloud-computing')
    ->assertOk()
    ->assertInertia(fn (Assert $page) => $page
        ->component('Features/LmsModule')
        ->where('course.slug', 'cloud-computing')
        ->has('course.chapters', 4)
        ->where('course.chapters.2.quiz.passing_score', 75)
        ->where('course.chapters.2.lessons.0.title', 'Video: Publik vs Privat')
    );
```

- [ ] **Step 2: Run tests to verify failure**

```bash
php artisan test --filter=LmsFrontendContractTest
```

Expected: FAIL because payloads still read prototype JSON or do not include enrollment fields.

- [ ] **Step 3: Rewrite `LmsCourseSeeder` using relationships**

Use `User::factory()->perusahaan()->firstOrCreate` pattern if needed. Seed the three courses as published. For `cloud-computing`, create four chapters, lessons under Bab 2 and Bab 3, and one quiz under Bab 3 with `passing_score` 75 and two questions.

The seeder must call `updateOrCreate` by slug and delete/recreate child rows for deterministic seed output.

- [ ] **Step 4: Update `LmsController@index`**

Load published courses with company, chapters, and authenticated enrollment. Return:

```php
[
    'slug' => $course->slug,
    'title' => $course->title,
    'provider' => $course->provider,
    'level' => $course->level,
    'status' => $enrollment ? 'in_progress' : 'available',
    'progress' => $progress,
    'is_enrolled' => (bool) $enrollment,
    'started_at' => $course->started_at?->format('d M Y'),
    'ends_at' => $course->ends_at?->format('d M Y'),
    'image_url' => $course->image_url,
    'image_alt' => $course->image_alt,
]
```

- [ ] **Step 5: Update `LmsController@show`**

Load `chapters.lessons`, `chapters.quiz.questions.options`, and authenticated enrollment state. Return `course.chapters` with:

```php
[
    'id' => $chapter->id,
    'title' => $chapter->title,
    'state' => $chapterCompleted ? 'completed' : 'active',
    'lessons' => [
        [
            'id' => $lesson->id,
            'type' => $lesson->type === 'video' ? 'play_circle' : 'article',
            'title' => $lesson->title,
            'state' => $lessonCompleted ? 'completed' : 'available',
            'active' => $isFirstAvailableLesson,
            'description_title' => $lesson->title,
            'description' => $lesson->content,
            'video_image_url' => $lesson->video_image_url,
        ],
    ],
    'quiz' => [
        'id' => $quiz->id,
        'title' => $quiz->title,
        'passing_score' => $quiz->passing_score,
        'questions' => [...],
    ],
]
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --filter=LmsFrontendContractTest
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add database/seeders/LmsCourseSeeder.php app/Http/Controllers/LmsController.php tests/Feature/Sikara/LmsFrontendContractTest.php
git commit -m "feat: serve LMS pages from relational data"
```

---

### Task 3: Student Enrollment, Lesson Completion, Quiz Attempts, And Progress

**Files:**
- Create: `app/Services/LmsProgressService.php`
- Create: `app/Http/Controllers/LmsEnrollmentController.php`
- Create: `app/Http/Controllers/LmsLessonCompletionController.php`
- Create: `app/Http/Controllers/LmsQuizAttemptController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Sikara/LmsStudentProgressTest.php`

- [ ] **Step 1: Write failing progress tests**

Create `tests/Feature/Sikara/LmsStudentProgressTest.php`:

```php
<?php

namespace Tests\Feature\Sikara;

use App\Models\LmsCourse;
use App\Models\LmsEnrollment;
use App\Models\LmsLesson;
use App\Models\LmsQuiz;
use App\Models\LmsQuizOption;
use App\Models\User;
use Database\Seeders\LmsCourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsStudentProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_must_enroll_before_completing_lessons(): void
    {
        $this->seed(LmsCourseSeeder::class);
        $student = User::factory()->mahasiswa()->create();
        $lesson = LmsLesson::query()->firstOrFail();

        $this->actingAs($student)
            ->post(route('lms.lessons.complete', $lesson))
            ->assertForbidden();
    }

    public function test_student_progress_updates_after_lessons_and_passing_quiz(): void
    {
        $this->seed(LmsCourseSeeder::class);
        $student = User::factory()->mahasiswa()->create();
        $course = LmsCourse::query()->where('slug', 'cloud-computing')->firstOrFail();

        $this->actingAs($student)
            ->post(route('lms.enrollments.store', $course))
            ->assertRedirect(route('lms.module.show', $course));

        $enrollment = LmsEnrollment::query()->where('student_id', $student->id)->where('course_id', $course->id)->firstOrFail();
        $chapter = $course->chapters()->whereHas('quiz')->with('lessons', 'quiz.questions.options')->firstOrFail();

        foreach ($chapter->lessons as $lesson) {
            $this->actingAs($student)->post(route('lms.lessons.complete', $lesson))->assertRedirect();
        }

        $quiz = $chapter->quiz;
        $answers = $quiz->questions->mapWithKeys(fn ($question) => [
            $question->id => $question->options->firstWhere('is_correct', true)->id,
        ])->all();

        $this->actingAs($student)
            ->post(route('lms.quizzes.submit', $quiz), ['answers' => $answers])
            ->assertRedirect();

        $this->assertDatabaseHas('lms_quiz_attempts', [
            'enrollment_id' => $enrollment->id,
            'quiz_id' => $quiz->id,
            'score' => 100,
            'passed' => true,
        ]);

        $this->assertDatabaseHas('lms_chapter_completions', [
            'enrollment_id' => $enrollment->id,
            'chapter_id' => $chapter->id,
        ]);
    }
}
```

- [ ] **Step 2: Run tests to verify failure**

```bash
php artisan test --filter=LmsStudentProgressTest
```

Expected: FAIL because routes and service do not exist.

- [ ] **Step 3: Implement `LmsProgressService`**

Create methods:

```php
public function scoreQuiz(LmsQuiz $quiz, array $answers): array
```

Return `['score' => 100, 'passed' => true]` style values. Validate by counting each question as correct only when the selected option belongs to that question and `is_correct` is true.

Create:

```php
public function refreshChapterCompletion(LmsEnrollment $enrollment, LmsChapter $chapter): bool
```

It creates `LmsChapterCompletion` only when every lesson in that chapter is completed and the latest passing quiz attempt exists. If no quiz exists, only lesson completion is required.

Create:

```php
public function courseProgress(LmsEnrollment $enrollment): int
```

It returns `0` when the course has zero chapters, otherwise `round(completed_chapters / total_chapters * 100)`.

- [ ] **Step 4: Implement student controllers**

`LmsEnrollmentController@store`:
- require authenticated mahasiswa
- create or return existing enrollment
- redirect to `lms.module.show`

`LmsLessonCompletionController@store`:
- find enrollment for authenticated student and lesson course
- abort 403 if no enrollment
- create completion idempotently
- call `refreshChapterCompletion`
- redirect back

`LmsQuizAttemptController@store`:
- validate `answers` as array
- find enrollment for authenticated student and quiz course
- abort 403 if no enrollment
- score quiz
- save attempt with JSON answers
- call `refreshChapterCompletion`
- redirect back with score flash

- [ ] **Step 5: Add routes**

Inside authenticated `role:mahasiswa` group:

```php
Route::post('/lms/{course}/enroll', [LmsEnrollmentController::class, 'store'])->name('lms.enrollments.store');
Route::post('/lms/lessons/{lesson}/complete', [LmsLessonCompletionController::class, 'store'])->name('lms.lessons.complete');
Route::post('/lms/quizzes/{quiz}/submit', [LmsQuizAttemptController::class, 'store'])->name('lms.quizzes.submit');
```

- [ ] **Step 6: Run progress tests**

```bash
php artisan test --filter=LmsStudentProgressTest
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Services app/Http/Controllers/LmsEnrollmentController.php app/Http/Controllers/LmsLessonCompletionController.php app/Http/Controllers/LmsQuizAttemptController.php routes/web.php tests/Feature/Sikara/LmsStudentProgressTest.php
git commit -m "feat: add LMS enrollment and progress tracking"
```

---

### Task 4: Company LMS Management Routes And Backend CRUD

**Files:**
- Create: `app/Http/Controllers/CompanyLmsCourseController.php`
- Create: `app/Http/Controllers/CompanyLmsContentController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Sikara/LmsCompanyAuthoringTest.php`

- [ ] **Step 1: Write failing authorization and CRUD tests**

Add tests:

```php
public function test_company_can_create_lms_course_with_draft_status(): void
{
    $company = User::factory()->perusahaan()->create();

    $this->actingAs($company)
        ->post(route('perusahaan.lms.store'), [
            'title' => 'Cloud Computing',
            'provider' => 'Telkom University',
            'description' => 'Course cloud.',
            'level' => 'BEGINNER',
            'image_url' => 'https://example.com/cloud.jpg',
            'image_alt' => 'Cloud',
        ])
        ->assertRedirect(route('perusahaan.lms.index'));

    $this->assertDatabaseHas('lms_courses', [
        'company_id' => $company->id,
        'title' => 'Cloud Computing',
        'status' => 'draft',
    ]);
}

public function test_company_cannot_update_another_company_course(): void
{
    $owner = User::factory()->perusahaan()->create();
    $other = User::factory()->perusahaan()->create();
    $course = LmsCourse::query()->create([
        'company_id' => $owner->id,
        'slug' => 'owner-course',
        'title' => 'Owner Course',
        'provider' => 'Owner',
        'level' => 'BEGINNER',
        'status' => 'draft',
        'image_url' => 'https://example.com/course.jpg',
    ]);

    $this->actingAs($other)
        ->put(route('perusahaan.lms.update', $course), ['title' => 'Hacked'])
        ->assertForbidden();
}
```

- [ ] **Step 2: Run tests to verify failure**

```bash
php artisan test --filter=LmsCompanyAuthoringTest
```

Expected: FAIL because company LMS routes do not exist.

- [ ] **Step 3: Implement company course controller**

Methods:
- `index`: list `LmsCourse::where('company_id', auth()->id())->latest()->get()`
- `create`: render `Perusahaan/Lms/Form`
- `store`: validate, create draft, slug from title with uniqueness
- `edit`: authorize ownership, render form
- `update`: authorize ownership, validate and update
- `destroy`: authorize ownership, delete
- `publish`: authorize ownership, require at least one chapter, set `status=published`
- `unpublish`: authorize ownership, set `status=draft`

- [ ] **Step 4: Implement company content controller**

Methods:
- `builder(LmsCourse $course)`: authorize ownership, load chapters/lessons/quizzes/questions/options, render `Perusahaan/Lms/Builder`
- `storeChapter`
- `storeLesson`
- `storeQuiz`
- `storeQuestion`
- `storeOption`

Validation:
- chapter title required
- lesson title/type required
- quiz passing score integer 0-100
- question required
- option text required and `is_correct` boolean

- [ ] **Step 5: Add company routes**

Inside `role:perusahaan` group:

```php
Route::resource('/lms', CompanyLmsCourseController::class)->names('lms');
Route::post('/lms/{course}/publish', [CompanyLmsCourseController::class, 'publish'])->name('lms.publish');
Route::post('/lms/{course}/unpublish', [CompanyLmsCourseController::class, 'unpublish'])->name('lms.unpublish');
Route::get('/lms/{course}/builder', [CompanyLmsContentController::class, 'builder'])->name('lms.builder');
Route::post('/lms/{course}/chapters', [CompanyLmsContentController::class, 'storeChapter'])->name('lms.chapters.store');
Route::post('/lms/chapters/{chapter}/lessons', [CompanyLmsContentController::class, 'storeLesson'])->name('lms.lessons.store');
Route::post('/lms/chapters/{chapter}/quiz', [CompanyLmsContentController::class, 'storeQuiz'])->name('lms.quizzes.store');
Route::post('/lms/quizzes/{quiz}/questions', [CompanyLmsContentController::class, 'storeQuestion'])->name('lms.questions.store');
Route::post('/lms/questions/{question}/options', [CompanyLmsContentController::class, 'storeOption'])->name('lms.options.store');
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --filter=LmsCompanyAuthoringTest
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/CompanyLmsCourseController.php app/Http/Controllers/CompanyLmsContentController.php routes/web.php tests/Feature/Sikara/LmsCompanyAuthoringTest.php
git commit -m "feat: add company LMS authoring backend"
```

---

### Task 5: Company LMS Vue Pages

**Files:**
- Create: `resources/js/Pages/Perusahaan/Lms/Index.vue`
- Create: `resources/js/Pages/Perusahaan/Lms/Form.vue`
- Create: `resources/js/Pages/Perusahaan/Lms/Builder.vue`
- Modify: `resources/js/Layouts/SikaraLayout.vue`
- Test: `tests/Feature/Sikara/LmsFrontendContractTest.php`

- [ ] **Step 1: Write failing frontend contract test**

Add:

```php
public function test_company_lms_frontend_pages_expose_authoring_forms(): void
{
    $index = file_get_contents(resource_path('js/Pages/Perusahaan/Lms/Index.vue'));
    $form = file_get_contents(resource_path('js/Pages/Perusahaan/Lms/Form.vue'));
    $builder = file_get_contents(resource_path('js/Pages/Perusahaan/Lms/Builder.vue'));

    $this->assertStringContainsString("route('perusahaan.lms.create')", $index);
    $this->assertStringContainsString('form.title', $form);
    $this->assertStringContainsString('form.passing_score', $builder);
    $this->assertStringContainsString("route('perusahaan.lms.chapters.store'", $builder);
    $this->assertStringContainsString("route('perusahaan.lms.options.store'", $builder);
}
```

- [ ] **Step 2: Run test to verify failure**

```bash
php artisan test --filter='LmsFrontendContractTest::test_company_lms_frontend_pages_expose_authoring_forms'
```

Expected: FAIL because Vue pages do not exist.

- [ ] **Step 3: Create `Index.vue`**

Use `SikaraLayout`, show table of company courses with title/status/chapter count and actions: edit, builder, publish/unpublish.

- [ ] **Step 4: Create `Form.vue`**

Use `useForm` fields:

```js
{
  title: props.course?.title || '',
  provider: props.course?.provider || '',
  description: props.course?.description || '',
  level: props.course?.level || 'BEGINNER',
  image_url: props.course?.image_url || '',
  image_alt: props.course?.image_alt || '',
}
```

Submit to store or update route.

- [ ] **Step 5: Create `Builder.vue`**

Use three sections:
- add chapter form
- per chapter add lesson form
- per chapter quiz form with passing score, questions, and options

Keep it functional and simple. Use server round-trips with Inertia `useForm`.

- [ ] **Step 6: Add LMS nav item for perusahaan**

In `SikaraLayout.vue`, add:

```js
{
    label: "LMS",
    href: route("perusahaan.lms.index"),
    active: route().current("perusahaan.lms.*"),
}
```

- [ ] **Step 7: Run tests and build**

```bash
php artisan test --filter=LmsFrontendContractTest
npm run build
```

Expected: PASS and Vite build succeeds.

- [ ] **Step 8: Commit**

```bash
git add resources/js/Pages/Perusahaan/Lms resources/js/Layouts/SikaraLayout.vue tests/Feature/Sikara/LmsFrontendContractTest.php
git commit -m "feat: add company LMS authoring UI"
```

---

### Task 6: Student LMS UI For Enrollment, Completion, Quiz, And Progress

**Files:**
- Modify: `resources/js/Pages/Features/Lms.vue`
- Modify: `resources/js/Pages/Features/LmsModule.vue`
- Test: `tests/Feature/Sikara/LmsFrontendContractTest.php`

- [ ] **Step 1: Write failing frontend contract test**

Add:

```php
public function test_student_lms_pages_expose_enrollment_completion_and_quiz_actions(): void
{
    $lms = file_get_contents(resource_path('js/Pages/Features/Lms.vue'));
    $module = file_get_contents(resource_path('js/Pages/Features/LmsModule.vue'));

    $this->assertStringContainsString("route('lms.enrollments.store'", $lms);
    $this->assertStringContainsString("route('lms.lessons.complete'", $module);
    $this->assertStringContainsString("route('lms.quizzes.submit'", $module);
    $this->assertStringContainsString('selectedQuizAnswers', $module);
    $this->assertStringContainsString('course.progress', $module);
}
```

- [ ] **Step 2: Run test to verify failure**

```bash
php artisan test --filter='LmsFrontendContractTest::test_student_lms_pages_expose_enrollment_completion_and_quiz_actions'
```

Expected: FAIL until pages are updated.

- [ ] **Step 3: Update `/lms` cards**

For each course:
- if `course.is_enrolled`, link to module and show `Lanjutkan`
- if not enrolled and user is mahasiswa, render form button posting to `lms.enrollments.store`
- if guest, link to login
- progress bar uses `course.progress`

- [ ] **Step 4: Update module page completion actions**

Add:

```js
const completeLessonForm = useForm({});
const completeLesson = (lessonId) => {
    completeLessonForm.post(route('lms.lessons.complete', lessonId), {
        preserveScroll: true,
    });
};
```

Render “Tandai Selesai” for selected lesson if not completed.

- [ ] **Step 5: Update module page quiz form**

Add:

```js
const selectedQuizAnswers = ref({});
const quizForm = useForm({ answers: {} });
const submitQuiz = () => {
    quizForm.answers = selectedQuizAnswers.value;
    quizForm.post(route('lms.quizzes.submit', selectedChapter.value.quiz.id), {
        preserveScroll: true,
    });
};
```

Render radio options for `selectedChapter.quiz.questions`.

- [ ] **Step 6: Run tests and build**

```bash
php artisan test --filter=LmsFrontendContractTest
npm run build
```

Expected: PASS and Vite build succeeds.

- [ ] **Step 7: Commit**

```bash
git add resources/js/Pages/Features/Lms.vue resources/js/Pages/Features/LmsModule.vue tests/Feature/Sikara/LmsFrontendContractTest.php
git commit -m "feat: add student LMS enrollment and quiz UI"
```

---

### Task 7: End-To-End Verification

**Files:**
- No new files unless prior task verification finds a concrete bug.

- [ ] **Step 1: Run focused LMS tests**

```bash
php artisan test --filter=Lms
```

Expected: all LMS tests pass.

- [ ] **Step 2: Run full test suite**

```bash
php artisan test
```

Expected: LMS tests pass. If existing unrelated auth/profile failures remain, record them separately and do not conflate them with LMS.

- [ ] **Step 3: Run frontend build**

```bash
npm run build
```

Expected: build succeeds.

- [ ] **Step 4: Manual local checks**

Start servers:

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev -- --host 127.0.0.1 --port 5173
```

Check:
- `/lms` shows published courses and enrollment action
- `/lms/module/cloud-computing` shows progress and relational chapters
- company LMS index is reachable after login as perusahaan
- builder can add chapter, lesson, quiz, question, and answer option
- student can enroll, complete lessons, submit quiz, and see progress update

- [ ] **Step 5: Final commit if verification fixes were needed**

```bash
git status --short
git add <changed-files>
git commit -m "test: verify LMS authoring and progress flow"
```
