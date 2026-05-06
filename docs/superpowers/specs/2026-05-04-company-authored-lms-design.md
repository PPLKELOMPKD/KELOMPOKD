# Company-Authored LMS Design

## Goal

Build a relational LMS where company users create and publish courses, chapters, lessons, and quizzes, while student users must enroll before learning and receive course progress based on completed chapters.

## Scope

This design replaces the prototype `lms_courses.chapters` JSON structure with normalized LMS tables. It covers company authoring, student enrollment, lesson completion, quiz scoring, chapter completion, and course progress calculation.

The first implementation should preserve the current public LMS browsing page and module detail page, but back them with relational data.

## Roles

Company users can manage only their own LMS courses. They can create, edit, publish, and unpublish courses; define chapters; add lessons; create quizzes; define quiz questions and options; mark correct answers; and set the passing score for each quiz.

Student users can see published courses, enroll before accessing course content, complete lessons, submit quizzes, and see progress calculated from their own enrollment.

Guests can see the public LMS listing if the current app allows public LMS browsing, but they cannot enroll, complete lessons, submit quizzes, or access protected learning actions.

## Data Model

`lms_courses` stores course metadata and ownership:
- `id`
- `company_id` referencing `users.id`
- `slug`
- `title`
- `provider`
- `description`
- `level`
- `status` as `draft` or `published`
- `started_at`
- `ends_at`
- `image_url`
- `image_alt`
- timestamps

`lms_chapters` stores ordered chapters:
- `id`
- `course_id`
- `title`
- `description`
- `position`
- timestamps

`lms_lessons` stores ordered chapter content:
- `id`
- `chapter_id`
- `title`
- `type` as `video`, `article`, or `resource`
- `content`
- `video_url`
- `video_image_url`
- `position`
- timestamps

`lms_quizzes` stores one quiz per chapter for the initial version:
- `id`
- `chapter_id`
- `title`
- `description`
- `passing_score`
- timestamps

`lms_quiz_questions` stores quiz questions:
- `id`
- `quiz_id`
- `question`
- `position`
- timestamps

`lms_quiz_options` stores answer options:
- `id`
- `question_id`
- `option_text`
- `is_correct`
- `position`
- timestamps

`lms_enrollments` stores student enrollment:
- `id`
- `course_id`
- `student_id` referencing `users.id`
- `enrolled_at`
- `completed_at`
- timestamps
- unique `(course_id, student_id)`

`lms_lesson_completions` stores completed lessons:
- `id`
- `enrollment_id`
- `lesson_id`
- `completed_at`
- timestamps
- unique `(enrollment_id, lesson_id)`

`lms_quiz_attempts` stores submitted quiz attempts:
- `id`
- `enrollment_id`
- `quiz_id`
- `score`
- `passed`
- `answers` JSON storing selected option ids by question id
- `submitted_at`
- timestamps

`lms_chapter_completions` stores completed chapters:
- `id`
- `enrollment_id`
- `chapter_id`
- `completed_at`
- timestamps
- unique `(enrollment_id, chapter_id)`

## Progress Rules

A student must enroll before learning a course.

A lesson is complete when the student explicitly marks it complete.

A quiz attempt score is calculated as:

```
correct answers / total questions * 100
```

A quiz is passed when the attempt score is greater than or equal to `lms_quizzes.passing_score`.

A chapter is complete when all lessons in the chapter are complete and the chapter quiz is passed. If a chapter has no quiz, the chapter is complete when all lessons are complete.

Course progress is calculated per enrollment:

```
completed chapters / total chapters * 100
```

A course is complete when every chapter is complete. `lms_enrollments.completed_at` is set when course progress reaches 100%.

## Company Flow

The company dashboard gets an LMS management section under the authenticated `perusahaan` route group.

Company users can:
- view their courses
- create a course as draft
- edit course metadata
- add and reorder chapters
- add and reorder lessons
- create a chapter quiz
- add questions and answer options
- set the correct answer options
- set `passing_score`
- publish or unpublish a course

Publishing requires at least one chapter. A chapter can be published without a quiz only if it has at least one lesson.

## Student Flow

The `/lms` page lists published courses.

If the current student is not enrolled in a course, the course card shows an enroll action and progress as 0%.

If the current student is enrolled, the course card shows continue learning and progress from that enrollment.

The `/lms/module/{course}` page requires enrollment for student learning. A student who is not enrolled should be redirected or shown an enroll prompt before accessing lessons.

Students can:
- mark lessons complete
- submit quiz answers
- retry quizzes when they do not pass
- see updated chapter and course progress after completion events

## Error Handling And Authorization

Company users cannot edit courses owned by another company.

Students cannot complete lessons or submit quizzes for a course they are not enrolled in.

Quiz submission rejects missing answers for required questions.

Quiz submission rejects option ids that do not belong to the submitted quiz.

Enrollment creation is idempotent: enrolling twice returns the existing enrollment.

## Migration From Prototype

The current three seeded LMS courses should be recreated in the relational seeders.

The `cloud-computing` course should keep the visible example chapters, active lesson, and quiz content so `/lms/module/cloud-computing` continues to work.

After relational tables are used, the `chapters` JSON column on `lms_courses` should no longer be used by controllers or Vue pages.

## Testing

Feature tests should cover:
- company can create course structure with chapters, lessons, quiz questions, answer options, and passing score
- company cannot manage another company's LMS course
- student must enroll before completing lessons or submitting quizzes
- quiz scoring marks attempts as passed only when score meets the company-set passing score
- chapter completion requires all lessons and passing quiz
- course progress is calculated from completed chapters
- `/lms` returns published courses with student-specific progress
- `/lms/module/{course}` returns relational chapters, lessons, quiz state, and progress

Frontend contract tests should cover:
- company LMS pages expose course/chapter/lesson/quiz forms
- student LMS module page has enroll, complete lesson, submit quiz, and progress UI hooks
