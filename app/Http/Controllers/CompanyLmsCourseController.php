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
            ->withCount('chapters')
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
        ]);

        $imageUrl = '';
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lms/courses', 'public');
            $imageUrl = '/storage/' . $path;
        }

        $slug = Str::slug($validated['title']) . '-' . uniqid();

        LmsCourse::create([
            'title' => $validated['title'],
            'provider' => $validated['provider'],
            'description' => $validated['description'],
            'level' => $validated['level'],
            'image_url' => $imageUrl,
            'image_alt' => $validated['image_alt'],
            'company_id' => $request->user()->id,
            'slug' => $slug,
            'status' => LmsCourse::STATUS_DRAFT,
        ]);

        return redirect()->route('perusahaan.lms.index');
    }

    public function edit(Request $request, LmsCourse $lms)
    {
        abort_if($lms->company_id !== $request->user()->id, 403);
        
        return Inertia::render('Perusahaan/Lms/Form', [
            'course' => $lms,
        ]);
    }

    public function update(Request $request, LmsCourse $lms)
    {
        abort_if($lms->company_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'provider' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|string|in:BEGINNER,INTERMEDIATE,ADVANCED',
            'image' => 'nullable|image|max:2048',
            'image_alt' => 'nullable|string|max:255',
        ]);

        $data = [
            'title' => $validated['title'],
            'provider' => $validated['provider'],
            'description' => $validated['description'],
            'level' => $validated['level'],
            'image_alt' => $validated['image_alt'],
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lms/courses', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $lms->update($data);

        return redirect()->route('perusahaan.lms.index');
    }

    public function destroy(Request $request, LmsCourse $lms)
    {
        abort_if($lms->company_id !== $request->user()->id, 403);
        $lms->delete();
        return redirect()->route('perusahaan.lms.index');
    }

    public function publish(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);
        abort_if($course->chapters()->count() === 0, 422, 'Cannot publish course without chapters');

        $course->update(['status' => LmsCourse::STATUS_PUBLISHED]);

        return back();
    }

    public function unpublish(Request $request, LmsCourse $course)
    {
        abort_if($course->company_id !== $request->user()->id, 403);

        $course->update(['status' => LmsCourse::STATUS_DRAFT]);

        return back();
    }
}
