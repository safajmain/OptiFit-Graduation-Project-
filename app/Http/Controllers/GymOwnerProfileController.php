<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GymOwnerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless((int) $request->user()->role_id === 2, 403);

        return view('optifit.owner.edit', [
            'user' => $request->user(),
            'gym' => Gym::where('owner_id', $request->user()->id)->orderBy('gym_id')->first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless((int) $user->role_id === 2, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $gym = Gym::where('owner_id', $user->id)->orderBy('gym_id')->first();
        // The phone number belongs to the gym, so don't silently write it to users.
        if (!$gym && filled($data['phone'] ?? null)) {
            return back()->withErrors(['phone' => 'لا يوجد نادي مرتبط بهذا الحساب لحفظ رقم الهاتف.'])->withInput();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $gym, $data) {
            $user->name = $data['name'];
            $user->email = $data['email'];
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();

            if ($gym) {
                $gym->phone = $data['phone'] ?? null;
                $gym->save();
            }
        });

        return Redirect::route('owner.profile.edit')->with('status', 'profile-updated');
    }
}
