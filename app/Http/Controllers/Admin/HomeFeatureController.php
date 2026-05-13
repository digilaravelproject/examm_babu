<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeFeature;
use Illuminate\Http\Request;

class HomeFeatureController extends Controller
{
    public function index()
    {
        $features = HomeFeature::orderBy('sort_order', 'asc')->paginate(10);
        return view('admin.home_features.index', compact('features'));
    }

    public function create()
    {
        return view('admin.home_features.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'icon' => 'required|string',
            'bg_class' => 'required|string',
            'sort_order' => 'integer',
        ]);

        HomeFeature::create($request->all());
        return redirect()->route('admin.home-features.index')->with('success', 'Feature created successfully.');
    }

    public function edit($id)
    {
        $feature = HomeFeature::findOrFail($id);
        return view('admin.home_features.edit', compact('feature'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'icon' => 'required|string',
            'bg_class' => 'required|string',
            'sort_order' => 'integer',
        ]);

        $feature = HomeFeature::findOrFail($id);
        $feature->update($request->all());
        return redirect()->route('admin.home-features.index')->with('success', 'Feature updated successfully.');
    }

    public function destroy($id)
    {
        $feature = HomeFeature::findOrFail($id);
        $feature->delete();
        return redirect()->route('admin.home-features.index')->with('success', 'Feature deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $feature = HomeFeature::findOrFail($id);
        $feature->is_active = !$feature->is_active;
        $feature->save();
        return back()->with('success', 'Status updated.');
    }
}
