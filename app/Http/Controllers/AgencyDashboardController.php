<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgencyDashboardController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $categoryRooms = [];

        $userId = optional($request->user())->id; // ← nullでもOKな書き方

        foreach ($categories as $category) {
            $pageParam = 'page_category_' . $category->id;
            $page = $request->input($pageParam, 1);

            $query = Room::with(['category', 'user'])
                ->withCount('likedBy')
                ->where('end', '>=', now())
                ->where('category_id', $category->id);

            if ($userId) {
                $query->with(['likedBy' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }]);
            }

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $categoryRooms[$category->id] = $query
                ->orderBy('start')
                ->paginate(10, ['*'], $pageParam, $page)
                ->withQueryString();
        }

        return Inertia::render('Dashboard', [
            'categories' => $categories,
            'categoryRooms' => $categoryRooms,
            'filters' => $request->only('search'),
        ]);
    }

}

