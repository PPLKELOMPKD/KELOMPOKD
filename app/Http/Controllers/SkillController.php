<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'proficiency' => ['required', 'integer', 'between:1,100'],
        ]);

        $request->user()->skills()->create($data);

        return redirect()->route('profile.show');
    }
}
