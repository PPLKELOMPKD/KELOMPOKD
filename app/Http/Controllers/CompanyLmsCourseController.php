<?php

namespace App\Http\Controllers;

use App\Models\LmsCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CompanyLmsCourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = LmsCourse::where('company_id', $request->user()->id)
            ->withCount(['chapters', 'enrollments'])
            ->latest()
            ->get();

        return Inertia::render('Perusahaan/Lms/Index', [
            'courses' => $courses,
        ]);
    }

    public function create()
    {
        return Inertia::render('Perusahaan/Lms/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|string|in:BEGINNER,INTERMEDIATE,ADVANCED',
            'image' => 'nullable|image|max:2048',
            'image_alt' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'passing_grade' => 'required|integer|min:0|max:100',
            'started_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:started_at',
            'start_time' => 'nullable|string',
        ]);

        $imageUrl = '';
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lms/courses', 'public');
            $imageUrl = '/storage/' . $path;
        }

        $slug = Str::slug($validated['title']) . '-' . uniqid();

        $course = LmsCourse::create([
            'title' => $validated['title'],
            'provider' => $validated['provider'] ?? null,
            'description' => $validated['description'] ?? null,
            'level' => $validated['level'],
            'image_url' => $imageUrl,
            'image_alt' => $validated['image_alt'] ?? null,
            'location' => $validated['location'] ?? null,
            'quota' => $validated['quota'] ?? null,
            'passing_grade' => $validated['passing_grade'] ?? 70,
            'started_at' => $validated['started_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'start_time' => $validated['start_time'] ?? null,
            'company_id' => $request->user()->id,
            'slug' => $slug,
            'status' => LmsCourse::STATUS_DRAFT,
        ]);

        \App\Services\ActivityLogger::log(
            'Membuat Course',
            "Perusahaan {$request->user()->name} membuat course '{$course->title}'",
            'course'
        );

        return redirect()->route('perusahaan.lms.index');
    }

    public function edit(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        return Inertia::render('Perusahaan/Lms/Form', [
            'course' => $course,
        ]);
    }

    public function update(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|string|in:BEGINNER,INTERMEDIATE,ADVANCED',
            'image' => 'nullable|image|max:2048',
            'image_alt' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'passing_grade' => 'required|integer|min:0|max:100',
            'started_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:started_at',
            'start_time' => 'nullable|string',
        ]);

        $data = [
            'title' => $validated['title'],
            'provider' => $validated['provider'] ?? $course->provider,
            'description' => $validated['description'] ?? $course->description,
            'level' => $validated['level'],
            'image_alt' => $validated['image_alt'] ?? $course->image_alt,
            'location' => $validated['location'] ?? $course->location,
            'quota' => $validated['quota'] ?? $course->quota,
            'passing_grade' => $validated['passing_grade'],
            'started_at' => $validated['started_at'] ?? $course->started_at,
            'ends_at' => $validated['ends_at'] ?? $course->ends_at,
            'start_time' => $validated['start_time'] ?? $course->start_time,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lms/courses', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $course->update($data);

        return redirect()->route('perusahaan.lms.index');
    }

    public function destroy(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        $course->delete();
        return redirect()->route('perusahaan.lms.index');
    }

    public function publish(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        
        if ($course->chapters()->count() === 0) {
            return back()->with('error', 'Gagal mempublish: Modul harus memiliki setidaknya satu Bab materi sebelum dipublikasikan.');
        }

        $course->update(['status' => LmsCourse::STATUS_PUBLISHED]);

        \App\Services\ActivityLogger::log(
            'Publish Course',
            "Perusahaan {$request->user()->name} mempublikasikan course '{$course->title}'",
            'course'
        );

        return back()->with('success', 'Modul berhasil dipublikasikan!');
    }

    public function unpublish(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        $course->update(['status' => LmsCourse::STATUS_DRAFT]);

        \App\Services\ActivityLogger::log(
            'Unpublish Course',
            "Perusahaan {$request->user()->name} membatalkan publikasi (unpublish) course '{$course->title}'",
            'course'
        );

        return back();
    }
}
