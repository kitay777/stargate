<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;

use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Validation\Rule;

class ProfileInformationController extends Controller
{
    public function editBasic(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Profile/EditBasic', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function updateBasic(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($user->id),
            ],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname'  => ['nullable', 'string', 'max:255'],
            'sex'       => ['nullable', 'string', 'max:255'],
            'birthday'  => ['nullable', 'date'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        // name更新
        $user->update([
            'name' => $data['name'],
        ]);

        // 画像アップロード
        if ($request->hasFile('profile_photo')) {

            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = $request->file('profile_photo')
                ->store('profile-photos', 'public');

            $user->update([
                'profile_photo_path' => $path,
            ]);
        }

        // profile更新
        $profile = $user->profile ?? $user->profile()->create([]);

        $profile->update([
            'firstname' => $data['firstname'] ?? null,
            'lastname'  => $data['lastname'] ?? null,
            'sex'       => $data['sex'] ?? null,
            'birthday'  => $data['birthday'] ?? null,
        ]);

        return back()->with('success', '基本情報を更新しました');
    }
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'string', 'max:10'], // 🔹 追加
            'birthday' => ['nullable', 'date'], // 🔹 追加
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'], // 🔹 `photo` のバリデーション
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->hasFile('photo')) {
            // 古い写真を削除
            /*
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
                */


            // 新しい写真をアップロード
            $filePath = $request->file('photo')->store('profile-photos', 'public'); // 🔹 `storage/app/public/profile-photos/` に保存

            $user->profile_photo_path = $filePath;
            $user->update();
        }

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'title',
                'message',
                'firstname',
                'lastname',
                'sex',
                'birthday',
                'phone',
                'address',
                'city',
                'state',
                'zip',
                'country',
            ])
        );


        return back()->with('status', 'プロフィールが更新されました！');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            // 🔹 画像ファイルを削除
            //Storage::disk('public')->delete($user->profile_photo_path);

            // 🔹 `profile_photo_path` を `null` に更新
            $user->profile_photo_path = null;
            $user->update();
        }

        return back()->with('status', 'プロフィール写真が削除されました！');
    }

    public function view($id)
    {
        $user = User::where('id', $id)->first();
        $profile = Profile::where('user_id', $id)->first();

        return Inertia::render('Profile/View', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}
