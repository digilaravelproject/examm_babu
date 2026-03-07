<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    public function index()
    {
        $advertisements = Advertisement::latest()->paginate(10);
        return view('admin.advertisements.index', compact('advertisements'));
    }

    public function create()
    {
        return view('admin.advertisements.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'image'      => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link_url'   => 'nullable|url',
            'location'   => 'required|string',
            'status'     => 'boolean',
        ]);

        $imagePath = $request->file('image')->store('uploads/ads', 'public');

        Advertisement::create([
            'title'      => $request->title,
            'image_path' => $imagePath,
            'link_url'   => $request->link_url,
            'location'   => $request->location,
            'status'     => $request->has('status'),
        ]);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement created successfully.');
    }

    public function edit(Advertisement $advertisement)
    {
        return view('admin.advertisements.edit', compact('advertisement'));
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'link_url'   => 'nullable|url',
            'location'   => 'required|string',
            'status'     => 'boolean',
        ]);

        $data = [
            'title'    => $request->title,
            'link_url' => $request->link_url,
            'location' => $request->location,
            'status'   => $request->has('status'),
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if ($advertisement->image_path) {
                Storage::disk('public')->delete($advertisement->image_path);
            }
            $data['image_path'] = $request->file('image')->store('uploads/ads', 'public');
        }

        $advertisement->update($data);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement updated successfully.');
    }

    public function destroy(Advertisement $advertisement)
    {
        if ($advertisement->image_path) {
            Storage::disk('public')->delete($advertisement->image_path);
        }
        $advertisement->delete();
        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement deleted successfully.');
    }

    public function toggle($id)
    {
        $ad = Advertisement::findOrFail($id);
        $ad->status = !$ad->status;
        $ad->save();
        return response()->json(['success' => true, 'status' => $ad->status]);
    }
}
