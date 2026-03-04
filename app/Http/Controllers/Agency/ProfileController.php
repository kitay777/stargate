<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(User $user)
    {
        $this->authorizeAgencyUser($user);

        $profile = $user->profile;

        /*
        return Inertia::render('Agency/EditProfile', [
            'user' => $user,
            'profile' => $user->profile,
            'agencyUser' => Auth::guard('agency')->user(),
        ]);
        */
        
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAgencyUser($user);

        // バリデーション
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'sex' => 'required|string|max:50',
            'birthday' => 'nullable|date',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // users テーブル更新
        $user->update([
            'name' => $validated['name'],
        ]);

        // アバター処理
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['profile_photo_path' => $path]);
        }

        // profiles テーブル更新（update or create）
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'sex' => $validated['sex'],
                'birthday' => $validated['birthday'] ?? null,
                'phone' => $validated['phone'] ?? '',
                'address' => $validated['address'] ?? '',
                'city' => $validated['city'] ?? '',
                'state' => $validated['state'] ?? '',
                'zip' => $validated['zip'] ?? '',
                'country' => $validated['country'] ?? '',
                'title' => $validated['title'] ?? '',
                'message' => $validated['message'] ?? '',
            ]
        );

        return redirect()->back()->with('success', 'プロフィールを更新しました');
    }


    private function authorizeAgencyUser(User $user)
    {
        $agencyUser = auth('agency')->user();
        if ($user->agency_id !== $agencyUser->agency_id) {
            abort(403);
        }
    }
    public function destroy(User $user)
    {
        $agencyUser = Auth::guard('agency')->user();

        if ($user->agency_id !== $agencyUser->agency_id) {
            abort(403);
        }

        $user->delete(); // 🔥 ソフトデリート or 実削除
        return redirect()->back()->with('success', 'ライバーを削除しました');
    }

}
