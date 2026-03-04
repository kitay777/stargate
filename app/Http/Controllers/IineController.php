<?php

namespace App\Http\Controllers;

use App\Models\Iine;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
class IineController extends Controller
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

    public function getIineCount($id)
    {
        $iine = Iine::where('target_id', $id)->count();
        return $iine;
    }

    /**
     * Store a newly created resource in storage.
     */
public function iine(Request $request)
{
    $user = Auth::user();
    $iine = Iine::where('user_id', $user->id)->where('target_id', $request->targetid)->first();

    if ($iine) {
        $iine->delete();
        return response()->json(['liked' => false]);
    } else {
        $iine = new Iine();
        $iine->user_id = $user->id;
        $iine->target_id = $request->targetid;
        $iine->status = 0;
        $iine->save();
        return response()->json(['liked' => true]);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(Iine $iine)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Iine $iine)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Iine $iine)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Iine $iine)
    {
        //
    }
}
