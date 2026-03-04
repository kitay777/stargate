<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VideoUploadController extends Controller
{
    public function create()
    {
        return Inertia::render('Videos/Upload');
    }

    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,mov,avi|max:512000',
        ]);

        $file = $request->file('video');
        $path = $file->store('public/recordings');

        $relativePath = str_replace('public/', '', $path);

        Video::create([
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $relativePath,
            'size'          => $file->getSize(),
        ]);

        return redirect()->route('videos.upload')->with('success', 'アップロード完了');
    }
}
