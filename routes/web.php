<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProfileInformationController;
use App\Http\Controllers\IineController;
use App\Http\Controllers\OkiniiriController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;



use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomMessageController;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use App\Models\User;
use App\Models\Room;
use App\Models\ChatMessage;
use App\Models\RoomMessage;
use App\Models\Iine;
use App\Models\Okiniiri;
use App\Models\Tip;
use App\Models\RoomLike;
use App\Models\Product;
use App\Models\Avatar;
use App\Events\MessageSent;
use App\Events\RoomMessageSent;
use Illuminate\Foundation\Application;
use App\Models\Category;
use App\Http\Controllers\RoomTipController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\MyTipController;
use App\Http\Controllers\RoomLikeController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SaleController;

use App\Http\Controllers\Avater;
use App\Http\Controllers\LiveScheduleController;
use App\Http\Controllers\Agency\DashboardController as AgencyDashboardController;
use App\Http\Controllers\VideoUploadController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\SaleApprovalController;
use App\Http\Controllers\SaleRejectionController;

use App\Http\Controllers\PreregisterController;
use App\Http\Controllers\AvatarUploadController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\AvatarClothesController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\LineLoginController;
use App\Http\Controllers\Admin\AdminClothesController;

use App\Http\Controllers\ClothesController;
use App\Http\Controllers\AdminGiftController;
use App\Http\Controllers\GiftController;
use App\Http\Controllers\Admin\AdminAvatarController;



Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/avatars/create', [AdminAvatarController::class, 'create'])->name('admin.avatars.create');
        Route::post('/avatars', [AdminAvatarController::class, 'store'])->name('admin.avatars.store');
        Route::get('/avatars', [AdminAvatarController::class, 'index'])
            ->name('admin.avatars.index');
        Route::delete('/avatars/{avatar}', [AdminAvatarController::class, 'destroy'])
            ->name('admin.avatars.destroy');
    });
Route::middleware(['auth', 'verified'])
    ->get('/user/profile/edit-basic', [ProfileInformationController::class, 'editBasic'])
    ->name('profile.basic.edit');

Route::middleware(['auth', 'verified'])
    ->post('/user/profile/basic', [ProfileInformationController::class, 'updateBasic'])
    ->name('profile.basic.update');

    Route::middleware(['auth','admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/gifts', [AdminGiftController::class, 'index']);
        Route::post('/gifts', [AdminGiftController::class, 'store']);
        Route::put('/gifts/{gift}', [AdminGiftController::class, 'update']);
        Route::post('/gifts/{gift}/toggle', [AdminGiftController::class, 'toggle']);
        Route::delete('/gifts/{gift}', [AdminGiftController::class, 'destroy']);
    });


Route::post('/rooms/{room}/gift', [GiftController::class, 'send'])
    ->middleware(['auth']);
Route::get('/api/tip-images', function () {
    return \App\Models\TipImage::where('is_active', true)
        ->orderBy('sort')
        ->get();
});


Route::post('/admin/users/{user}/send-line', function (User $user, Request $request) {

    if (!$user->line_user_id || !$user->is_line_friend) {
        return back()->with('error', 'LINE送信不可');
    }
    app(\App\Services\LineMessageService::class)
        ->push($user->line_user_id, $request->message);

    return back()->with('success', '送信しました');
})->middleware(['auth', 'admin']);

Route::get('/line-friend-required', function () {
    return Inertia::render('Auth/LineFriendRequired');
});

Route::get('/auth/line', [LineLoginController::class, 'redirect']);
Route::get('/auth/line/callback', [LineLoginController::class, 'callback']);
Route::post('/webhook/line', [LineWebhookController::class, 'handle']);
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'users' => \App\Models\User::count(),
                    'liveRooms' => \App\Models\Room::where('end', '>=', now())->count(),
                    'products' => \App\Models\Product::count(),
                    'reports' => 0, // まだ未実装
                ]
            ]);
        });
        Route::get('/users', [UserController::class, 'adminIndex'])
            ->name('admin.users');

        Route::post('/users/{user}/ban', [UserController::class, 'ban']);
        Route::post('/users/{user}/unban', [UserController::class, 'unban']);
        Route::get('/clothes', [AdminClothesController::class, 'index']);
        Route::get('/clothes/create', [AdminClothesController::class, 'create']);
        Route::post('/clothes', [AdminClothesController::class, 'store']);
        Route::delete('/clothes/{clothes}', [AdminClothesController::class, 'destroy']);

        Route::patch(
            '/clothes/{clothes}/toggle-active',
            [AdminClothesController::class, 'toggleActive']
        );

        Route::patch(
            '/clothes/{clothes}/toggle-visible',
            [AdminClothesController::class, 'toggleVisible']
        );

        Route::patch(
            '/clothes/{clothes}/sort',
            [AdminClothesController::class, 'updateSort']
        );
    });

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/api/avatar/{avatar}/clothes', [AvatarClothesController::class, 'index']);
    Route::post('/api/avatar/{avatar}/clothes/select', [AvatarClothesController::class, 'select']);
    Route::get('/api/avatar/{avatar}/clothes/current', [AvatarClothesController::class, 'current']);
});
Route::middleware('auth')->group(function () {
    Route::get('/avatar/{avatar}/clothes', [AvatarController::class, 'clothes']);
    Route::post('/rooms/{room}/tip', [TipController::class, 'tip']);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/avatar/select', [AvatarController::class, 'index'])
        ->name('avatar.select');

    Route::post('/avatar/select/{avatar}', [AvatarController::class, 'select'])
        ->name('avatar.select.use');
});




Route::middleware(['auth'])->group(function () {
    Route::get('/avatar/upload', [AvatarUploadController::class, 'create'])
        ->name('avatar.upload');

    Route::post('/avatar/upload', [AvatarUploadController::class, 'store'])
        ->name('avatar.upload.store');
});

Route::get('/login', fn() => Inertia::render('Auth/Login'))
    ->middleware('guest')
    ->name('login');

// Agency ログイン画面
Route::get('/agency/login', fn() => Inertia::render('Agency/Login'))
    ->middleware('guest')
    ->name('agency.login');

Route::middleware('web')->group(function () {
    Route::get('/preregister', [PreregisterController::class, 'create'])->name('preregister.create');
    Route::post('/preregister', [PreregisterController::class, 'store'])->name('preregister.store');
    Route::get('/preregister/thanks', [PreregisterController::class, 'thanks'])->name('preregister.thanks');
});

/*
|--------------------------------------------------------------------------
| Avatar API（Blade + Vue 用）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // アバター一覧
    Route::get('/api/avatars', [AvatarController::class, 'index']);

    // アバター選択
    Route::post('/api/avatars/select', [AvatarController::class, 'select']);
});

Route::middleware(['web', 'auth'])->get('/api/me', function (Request $request) {
    return response()->json(
        $request->user()->load('avatar.apngParts')
    );
});

Route::get('/videos/upload', [VideoUploadController::class, 'create'])->name('videos.upload');
Route::post('/videos/store', [VideoUploadController::class, 'store'])->name('videos.store');

Route::middleware('auth:agency')->get('/agency/dashboard', [AgencyDashboardController::class, 'index'])
    ->name('agency.dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
Route::get('/', [DashboardController::class, 'index']);
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/all', [ProductController::class, 'publicView'])->name('products.all');
Route::get('/ec', [ProductController::class, 'publicView'])->name('shop');
Route::get('/auctions/open', [ProductController::class, 'openAuctions'])
    ->name('auctions.open');

Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

Route::post('/api/rooms', [RoomController::class, 'apistore'])->middleware(['auth', 'verified']);

Route::get('/presenter', function () {

    $user = auth()->user();

    if (!$user->avatar_id) {
        return redirect('/avatar/select')
            ->with('error', 'アバターを選択してください');
    }

    $room = \App\Models\Room::firstOrCreate(
        [
            'user_id' => $user->id,
            'is_personal' => true,
        ],
        [
            'name' => $user->name . 'の配信部屋',
            'description' => '',
            'category_id' => 1,
            'start' => now(),
            'end' => now()->addMinutes(30),
        ]
    );
    $room->update([
        'start' => now(),
        'end'   => now()->addMinutes(30),
    ]);

    return Inertia::render('WebRTC/Presenter', [
        'room' => $room,
        'user' => $user,
    ]);
});

Route::middleware(['auth'])
    ->put('/rooms/{room}/update-personal', function (Request $request, \App\Models\Room $room) {

        abort_unless($room->user_id === auth()->id(), 403);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string','max:1000'],
            'category_id' => ['nullable','exists:categories,id'],
        ]);

        $room->update($data);

        return back()->with('success', 'ルーム情報を更新しました');
    })
    ->name('rooms.update.personal');
// ルーム作成とリダイレクト
Route::post('/realpresenter/create', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1024',
        'category_id' => 'required|exists:categories,id',
    ]);

    $room = Room::create([
        'name' => $request->name,
        'description' => $request->description,
        'category_id' => $request->category_id,
        'start_time' => now(),
        'end_time' => now()->addMinutes(30),
        'user_id' => Auth::id(),
    ]);

    return redirect("/presenter/{$room->id}");
})->name('realpresenter.create');


// routes/web.php
//require __DIR__.'/agency.php';


Route::post('/test-upload', function (Request $request) {
    \Log::info('📥 テストアップロード来た');
    return response()->json(['ok' => true]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
    Route::delete('/products/images/{id}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
});


Route::get('/products/seller-manage', function () {
    return Inertia::render('Products/SellerManage', [
        'products' => Product::with([
            'sales' => function ($query) {
                $query->with('room.user'); // ✅ roomとuserを必ず取得
            }
        ])->select('id', 'name')->get(),
        'allUsers' => User::select('id', 'name')->get(),
    ]);
})->middleware(['auth', 'verified'])->name('products.seller-manage');

Route::middleware(['auth'])->group(function () {
    Route::post('/sales/apply', [SaleController::class, 'apply'])->name('sales.apply');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/purchased', [CartController::class, 'purchased'])->name('cart.purchased');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/products-all-api', function () {
        return response()->json([
            'products' => \App\Models\Product::with(['user.profile', 'images', 'room'])->latest()->paginate(12),
        ]);
    });
    Route::post('/products/{product}/sellers', [ProductController::class, 'addSeller']);
    Route::delete('/products/{product}/sellers/{user}', [ProductController::class, 'removeSeller']);
    // routes/web.php
    Route::post('/sales/{sale}/approve', [SaleController::class, 'approve'])->name('sales.approve');
    Route::post('/sales/{sale}/reject', [SaleController::class, 'reject'])->name('sales.reject');
    Route::get('/live-schedule', [LiveScheduleController::class, 'index'])->name('live.schedule');
    Route::get('/live-calendar', [LiveScheduleController::class, 'calendar'])->name('live.calendar');
});

// routes/web.php
Route::get('/webrtc/{friend}', function (User $friend) {
    return view('kurento', [
        'friend' => $friend
    ]);
});


Route::get('/user-counts/{id}', [UserController::class, 'getUserCounts']);

Route::get('/room-name/{id}', function ($id) {
    $room = \App\Models\Room::find($id);
    if (!$room) {
        return response()->json(['name' => null], 404);
    }
    return ['name' => $room->name];
})->middleware(['auth']);


Route::middleware(['auth', 'verified'])->group(function () {
    Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
        ->name('user-profile-information.update');
    Route::delete('/user/profile-photo', [ProfileInformationController::class, 'destroy'])
        ->name('current-user-photo.destroy');
    Route::get('/user/profile/{id}', [ProfileInformationController::class, 'view'])
        ->name('user-profile.view');
    Route::post('iine', [IineController::class, 'iine']);
    Route::get('iinecount/{id}', [IineController::class, 'getIineCount']);
    Route::post('okiniiri', [OkiniiriController::class, 'okiniiri']);
    Route::get('okiniiricount/{id}', [OkiniiriController::class, 'getOkiniiriCount']);
    Route::get('/makeroom', function () {
        return Inertia::render('MakeRoom');
    })->name('make-room');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/categories', [CategoryController::class, 'index']);
    // routes/web.php
    Route::post('/roomtips/{room}', [RoomTipController::class, 'store'])->middleware('auth');
    Route::post('/tips', [TipController::class, 'store']);
    Route::get('/my-tips', [MyTipController::class, 'index'])->middleware('auth');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms/{room}/like', [RoomLikeController::class, 'toggle'])->name('rooms.like')->middleware('auth');
    Route::get('/rooms/{room}/edit', [\App\Http\Controllers\RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'update'])->name('rooms.update');

    Route::get('/my-liked-presenters', [RoomLikeController::class, 'likedPresenters'])->middleware('auth');
    Route::get('/rooms/{id}/streaming-status', [\App\Http\Controllers\RoomController::class, 'isRoomStreaming']);
    // routes/api.php

});

Route::post('/background/upload', function (Request $request) {
    $request->validate([
        'image' => 'required|image|max:2048000',
    ]);

    $user = $request->user();

    if (!$user) {
        abort(401, 'ユーザー未認証');
    }

    $file = $request->file('image');
    $ext = $file->getClientOriginalExtension();
    $filename = "{$user->id}.{$ext}";

    $destination = storage_path("app/public/backgrounds");
    if (!file_exists($destination)) {
        mkdir($destination, 0775, true);
    }

    $file->move($destination, $filename);

    $fullPath = "{$destination}/{$filename}";
    \Log::info('✅ move 完了', [
        'path' => "public/backgrounds/{$filename}",
        'fullPath' => $fullPath,
        'exists' => file_exists($fullPath),
        'user_id' => $user->id,
    ]);

    return response()->json([
        'url' => Storage::url("backgrounds/{$filename}")
    ]);
})->middleware('auth');






Route::middleware(['auth', 'verified'])->get('/user/profile', function () {
    $user = Auth::user();

    // 🔹 プロフィールデータを取得 or デフォルト値をセット
    $profile = Profile::firstOrCreate(
        ['user_id' => $user->id], // 🔹 条件: `user_id` が一致するデータを検索
        [
            'firstname' => '',
            'lastname' => '',
            'sex' => '未設定',
            'birthday' => null,
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '未設定',
            'zip' => '',
            'country' => '未設定',
        ]
    );

    return Inertia::render('Profile/Show', [
        'user' => $user,
        'profile' => $profile,
    ]);
})->name('profile.show');





Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    /* presenter.blade.php
    Route::get('/presenter/{room}', function (Room $room) {
        $room = \App\Models\Room::find($room->id);

        if (!$room) {
            abort(403, 'この配信ルームは存在しません');
        }
        return view('webrtc.presenter', [
            'room' => $room,
        ]);
    });
    */
Route::get('/presenter/{room}', function (\App\Models\Room $room) {

    $user = auth()->user();
    // 🔴 アバター未選択ならリダイレクト
    if (!$user->avatar_id) {
        return redirect('/avatar/select')
            ->with('error', 'アバターを選択してください');
    }

    return Inertia::render('WebRTC/Presenter', [
        'room' => $room,
        'user' => $user,
    ]);
});



    /*
    Route::get('/presenter/{room}', function (Room $room) {
        return Inertia::render('WebRTC/Presenter', [
            'room' => $room,
            'user' => auth()->user(),
        ]);
    });
    */
    /* blade
    Route::get('/viewer/{room}', function (Room $room) {
        $room = \App\Models\Room::find($room->id);

        if (!$room) {
            abort(403, 'この配信ルームは存在しません');
        }
    
        return view('webrtc.viewer', [
            'room' => $room
        ]);
    });
    */
    Route::get('/viewer/{room}', function ($roomId) {
        $room = \App\Models\Room::with('user')->findOrFail($roomId);
        return Inertia::render('WebRTC/Viewer', [
            'room' => $room,
            'authUser' => auth()->user(),
        ]);
    });

    Route::get('/multiviewer', function () {
        return view('webrtc.multiviewer');
    });
    /*
    Route::get('/', function (Room $room) {
        return view('welcome');
    });
    */
    /*
    Route::get('/dashboard', function (Room $room) {
        return view('home');
    })->name('dashboard');
    */
    /*
    Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    */
    /*
    Route::get('/dashboard', function (Request $request) {
        $query = Room::with(['category', 'user'])
            ->withCount('likedBy') // ← ✅ ここで追加！
            ->with(['likedBy' => function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            }])
            ->where('end', '>=', now());
    
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        $rooms = $query
            ->orderBy('start')
            ->paginate(10)
            ->withQueryString();
    
        $categories = Category::orderBy('name')->get();
    
        return Inertia::render('Dashboard', [
            'scheduledRooms' => $rooms, // ✅ room.likes_count 使える！
            'categories' => $categories,
            'filters' => $request->only('search', 'category_id'),
        ]);
    })->middleware(['auth', 'verified'])->name('dashboard');
    */

    Route::get('/myrooms', function (Request $request) {
        $roomsQuery = Room::with(['category', 'user'])
            ->where('user_id', Auth::id())
            ->where('end', '>=', now()) // ← ここ！
            ->orderBy('start', 'asc');

        $page = $request->input('page', 1);

        $rooms = $roomsQuery->paginate(10)->withQueryString();


        if ($rooms->count() === 0 && $page > 1) {
            return redirect()->route('myrooms', ['page' => $rooms->lastPage()]);
        }

        return Inertia::render('MyRooms', [
            'myRooms' => $rooms,
        ]);
    })->middleware(['auth'])->name('myrooms');




    /*
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return redirect('/');
    })->name('logout');
    */
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
    Route::get('/dashboard2', function () {
        $categories = Category::all(); // 🔹 カテゴリーのデータを取得

        return Inertia::render('Dashboard', [
            'categories' => $categories, // 🔹 Vue にデータを渡す
            'users' => User::query()->where('id', '!=', auth()->id())->get()
        ]);
    })->middleware(['auth'])->name('dashboard2');

    Route::get('/roomchat/{room}', function (Room $room) {
        return Inertia::render('RoomChat', [
            'room' => $room,
            'user' => auth()->user()
        ]);
    })->middleware(['auth'])->name('roomchat');

    Route::get('/chat/{friend}', function (User $friend) {
        return Inertia::render('Chat', [
            'friend' => $friend,
            'user' => auth()->user()
        ]);
    })->middleware(['auth'])->name('chat');

    Route::get('/roommessages/{room}', [RoomMessageController::class, 'show']);

    Route::get('/messages/{friend}', function (User $friend) {
        return ChatMessage::query()
            ->where(function ($query) use ($friend) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $friend->id);
            })
            ->orWhere(function ($query) use ($friend) {
                $query->where('sender_id', $friend->id)
                    ->where('receiver_id', auth()->id());
            })
            ->with(['sender', 'receiver'])
            ->orderBy('id', 'asc')
            ->get();
    })->middleware(['auth']);

    Route::post('/roommessages/{room}', [RoomMessageController::class, 'store']);

    Route::post('/messages/{friend}', function (User $friend) {
        $message = ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $friend->id,
            'text' => request()->input('message')
        ]);

        broadcast(new MessageSent($message));

        return $message;
    });
});
