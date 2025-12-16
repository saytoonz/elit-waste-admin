<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zones = Zone::latest()->paginate(10);
        return view('zones.index', compact('zones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('zones.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:zones',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Zone::create($validated);

        return redirect()->route('zones.index')
            ->with('success', 'Zone created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {
        return view('zones.show', compact('zone'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {
        return view('zones.edit', compact('zone'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:zones,name,' . $zone->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        // Handle checkbox missing case (although HTML forms send '1' or nothing)
        $validated['is_active'] = $request->has('is_active');

        $zone->update($validated);

        return redirect()->route('zones.index')
            ->with('success', 'Zone updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $zone)
    {
        $zone->delete();

        return redirect()->route('zones.index')
            ->with('success', 'Zone deleted successfully.');
    }
}
