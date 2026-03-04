<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StreamerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['nullable', 'min:6'],
        ], [
            'email.unique' => 'このメールアドレスはすでに登録されています。',
        ]);
        

        $agencyUser = Auth::guard('agency')->user();
        $agencyId = $agencyUser->agency_id;

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? 'password'), // 🔐 初期パスワード
            'agency_id' => $agencyId,
        ]);

        Log::debug('Agency ID:', [$agencyId]);
        return redirect()->back()->with('success', 'ライバーを登録しました');
    }
    public function destroy(User $user)
    {
        $agencyUser = Auth::guard('agency')->user();

        // 所属代理店と一致しているか確認
        if ($user->agency_id !== $agencyUser->agency_id) {
            abort(403);
        }

        $user->delete(); // SoftDeletes を使っていない場合は物理削除になります

        return redirect()->back()->with('success', 'ライバーを削除しました');
    }

}
