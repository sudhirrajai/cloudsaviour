<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Admin/Plans/Index', [
            'plans' => Plan::orderBy('price')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        Plan::create($validated);

        return redirect()->back()->with('success', 'Plan created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $plan->update($validated);

        return redirect()->back()->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        // Check if any workspace is using this plan
        if ($plan->workspaces()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete plan: Workspaces are currently using it.');
        }

        $plan->delete();

        return redirect()->back()->with('success', 'Plan deleted successfully.');
    }
}
