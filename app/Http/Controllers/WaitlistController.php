<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Waitlist;

class WaitlistController extends Controller
{
    public function index(Request $request)
    {
        $realCount = Waitlist::count();
        $fakeCount = (int) env('WAITLIST_FAKE_COUNTER', 246);
        $totalCount = $realCount + $fakeCount;
        $launchDate = env('WAITLIST_LAUNCH_DATE', now()->addDays(23)->toDateString());

        return view('waitlist', compact('totalCount', 'launchDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:waitlists,email'
        ]);

        Waitlist::create($request->only('email'));

        $realCount = Waitlist::count();
        $fakeCount = (int) env('WAITLIST_FAKE_COUNTER', 246);
        $totalCount = $realCount + $fakeCount;

        return response()->json([
            'success' => true,
            'position' => $totalCount
        ]);
    }
}
