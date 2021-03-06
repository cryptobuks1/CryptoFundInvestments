<?php

namespace App\Http\Controllers;

use App\TraderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function profile()
    {
        $currentUser = Auth::user();
        $user = Auth::user();

        return view('users.profile', compact('user', 'currentUser'));
    }

    public function userProfile($id)
    {
        $user = User::findOrFail(Auth::user()->getAuthIdentifier());

        if ($user->isAdmin()) {
            $user = User::findOrFail($id);

            return view('users.profile', compact('user'));
        }

        return redirect()->back()->with('errorMessage', 'You do not have permissions to view that page');
    }

    public function trader($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);
        if ($user->isTrader())
        {
            return view('users.profile', compact('user', 'currentUser'));
        }

        return redirect('/home');
    }

    public function edit()
    {
        $user = Auth::user();
        return view('users.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($request->email == $user->email)
        {
            $this->validate($request, [
                'first_name' => 'required|string|min:2|max:255',
                'last_name' => 'required|string|min:2|max:255',
                'phone' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
            ]);
        }
        else
        {
            $this->validate($request, [
                'first_name' => 'required|string|min:2|max:255',
                'last_name' => 'required|string|min:2|max:255',
                'phone' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
            ]);
        }

        $user->update($request->all());
        return redirect('/profile');
    }

    public function remove_trader_role()
    {
        $user = Auth::user();
        $user->roles()->sync([2]);
        return redirect('/profile');
    }

    public function changePassword(Request $request) {

        $user = Auth::user();

        if (!(Hash::check($request->currentPassword, $user->password))) {
            return redirect()->back()->with("errorPassword","Current Password is incorrect.");
        }

        $this->validate($request, [
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->password = Hash::make($request['password']);
        $user->save();

        return redirect()->back()->with("successPassword","Password changed successfully!");

    }

    public function requestTraderRole(Request $qwer) {
        $user = User::find(Auth::user()->getAuthIdentifier());

        if($user->isTrader()) {
            return redirect()->back()->with("errorMessage", "You are already a Trader");
        }

        if (TraderRequest::where('user_id', $user->id)->count() == 0) {
            TraderRequest::create(['user_id'=>$user->id]);
            return redirect()->back()->with("successMessage", "Successfully submitted request for Trader role");
        }
        else {
            return redirect()->back()->with("errorMessage", "There is a request for Trader role");
        }
    }
}
