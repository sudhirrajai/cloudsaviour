<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Waitlist;
use App\Models\User;
use App\Mail\WaitlistAccountCreatedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class WaitlistController extends Controller
{
    public function index()
    {
        $waitlists = Waitlist::latest()->paginate(20);
        $fakeCount = (int) env('WAITLIST_FAKE_COUNTER', 0);
        $launchDate = env('WAITLIST_LAUNCH_DATE');
        
        $daysLeft = 0;
        if ($launchDate) {
            $daysLeft = Carbon::now()->diffInDays(Carbon::parse($launchDate), false);
        }

        return Inertia::render('Admin/Waitlists/Index', [
            'waitlists' => $waitlists,
            'fakeCount' => $fakeCount,
            'launchDate' => $launchDate,
            'daysLeft' => (int) $daysLeft
        ]);
    }

    public function updateConfig(Request $request)
    {
        $request->validate([
            'fake_counter' => 'required|integer',
            'days_left' => 'required|integer'
        ]);

        $launchDate = Carbon::now()->addDays($request->days_left)->toDateString();

        $this->updateEnv([
            'WAITLIST_FAKE_COUNTER' => $request->fake_counter,
            'WAITLIST_LAUNCH_DATE' => $launchDate
        ]);

        return back()->with('success', 'Waitlist configuration updated.');
    }

    public function createAccount(Waitlist $waitlist)
    {
        if (User::where('email', $waitlist->email)->exists()) {
            $waitlist->update(['is_account_created' => true]);
            return back()->with('error', 'User already exists. Marked as created.');
        }

        $password = Str::random(12);
        
        $user = User::create([
            'name' => explode('@', $waitlist->email)[0],
            'email' => $waitlist->email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Automatically create a workspace for them if needed, 
        // but for now let's just create the user.
        // Assuming they will be prompted to create workspace on first login.

        $waitlist->update(['is_account_created' => true]);

        Mail::to($user->email)->send(new WaitlistAccountCreatedMail($user->email, $password));

        return back()->with('success', 'Account created and email sent!');
    }

    private function updateEnv(array $data)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            foreach ($data as $key => $value) {
                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                } else {
                    $content .= "\n{$key}={$value}";
                }
            }
            file_put_contents($path, $content);
        }
    }
}
