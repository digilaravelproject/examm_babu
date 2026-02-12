<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeStat;
use Illuminate\Http\Request;

class HomeStatController extends Controller
{
    public function index()
    {
        $stats = HomeStat::orderBy('sort_order', 'asc')->paginate(10);
        return view('admin.home_stats.index', compact('stats'));
    }

    public function create()
    {
        return view('admin.home_stats.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'count' => 'required|string',
            'label' => 'required|string',
            'icon' => 'required|string',
            'text_class' => 'required|string',
            'bg_class' => 'required|string',
            'sort_order' => 'integer',
        ]);

        HomeStat::create($request->all());

        return redirect()->route('admin.home-stats.index')->with('success', 'Stat added successfully.');
    }

    public function edit($id)
    {
        $stat = HomeStat::findOrFail($id);
        return view('admin.home_stats.edit', compact('stat'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'count' => 'required|string',
            'label' => 'required|string',
            'icon' => 'required|string',
            'text_class' => 'required|string',
            'bg_class' => 'required|string',
            'sort_order' => 'integer',
        ]);

        $stat = HomeStat::findOrFail($id);
        $stat->update($request->all());

        return redirect()->route('admin.home-stats.index')->with('success', 'Stat updated successfully.');
    }

    public function destroy($id)
    {
        $stat = HomeStat::findOrFail($id);
        $stat->delete();
        return redirect()->route('admin.home-stats.index')->with('success', 'Stat deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $stat = HomeStat::findOrFail($id);
        $stat->is_active = !$stat->is_active;
        $stat->save();
        return back()->with('success', 'Status updated.');
    }
}
