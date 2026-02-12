<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order', 'asc')->paginate(10);
        return view('admin.hero_slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.hero_slides.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'badge_text' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'button_text' => 'required|string|max:50',
            'button_link' => 'required|string',
            'theme_color' => 'required|string',
            'bg_gradient_start' => 'required|string',
            'bg_gradient_end' => 'required|string',
            'icon_top' => 'required|string',
            'icon_bottom' => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        HeroSlide::create($request->all());

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Slide created successfully.');
    }

    public function edit($id)
    {
        $slide = HeroSlide::findOrFail($id);
        return view('admin.hero_slides.edit', compact('slide'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'badge_text' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'button_text' => 'required|string|max:50',
            'button_link' => 'required|string',
            'theme_color' => 'required|string',
            'bg_gradient_start' => 'required|string',
            'bg_gradient_end' => 'required|string',
            'icon_top' => 'required|string',
            'icon_bottom' => 'required|string',
            'sort_order' => 'nullable|integer',
        ]);

        $slide = HeroSlide::findOrFail($id);
        $slide->update($request->all());

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Slide updated successfully.');
    }

    public function destroy($id)
    {
        $slide = HeroSlide::findOrFail($id);
        $slide->delete();

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Slide deleted successfully.');
    }

    // Extra: Toggle Status (Active/Inactive)
    public function toggleStatus($id)
    {
        $slide = HeroSlide::findOrFail($id);
        $slide->is_active = !$slide->is_active;
        $slide->save();

        return back()->with('success', 'Status updated successfully.');
    }
}
