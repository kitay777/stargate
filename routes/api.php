<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\IineController;
use App\Http\Controllers\OkiniiriController;
use App\Http\Controllers\BackgroundController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\Api\AvatarController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AvatarClothesController;


Route::middleware('auth')->get('/api/avatars', [AvatarController::class, 'index']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/user-counts/{id}', [UserController::class, 'getUserCounts']);
// いいね
Route::middleware('auth:sanctum')->post('/iine', [IineController::class, 'iine']);
// お気に入り
Route::middleware('auth:sanctum')->post('/okiniiri', [OkiniiriController::class, 'okiniiri']);



// routes/api.php
Route::get('/categories', [CategoryController::class, 'index']);

Route::post('/rooms/streaming-details', function (Request $request) {
    $ids = $request->input('ids', []);
    return \App\Models\Room::with('user')
        ->whereIn('id', $ids)
        ->get()
        ->map(function ($room) {
            $room->image_path = $room->image_path
                ? asset('/storage/rooms/' . basename($room->image_path))
                : null;
            return $room;
        })
        ->values();

});





