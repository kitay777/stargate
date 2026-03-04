<?php

namespace App\Http\Controllers;

use App\Models\Okiniiri;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
class OkiniiriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function getOkiniiriCount($id)
    {
        $iine = Iine::where('target_id', $id)->count();
        return $iine;
    }
    /**
     * Store a newly created resource in storage.
     */
    public function okiniiri(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // お気に入りの状態を取得
        $okiniiri = Okiniiri::where('user_id', $user->id)
                            ->where('target_id', $request->targetid)
                            ->first();

        if ($okiniiri) {
            $okiniiri->delete();
            return response()->json(['favorited' => false]); // ❌ ここが `liked: false` になっていた
        } else {
            $okiniiri = new Okiniiri();
            $okiniiri->user_id = $user->id;
            $okiniiri->target_id = $request->targetid;
            $okiniiri->save();

            return response()->json(['favorited' => true]); // ✅ `favorited` を正しく返す
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Okiniiri $okiniiri)
    {
        //

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Okiniiri $okiniiri)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Okiniiri $okiniiri)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Okiniiri $okiniiri)
    {
        //
    }
}
