<?php

namespace App\Http\Controllers;

use App\Models\Preregistration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PreregisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Preregister');
    }

public function store(Request $request)
{
    $validated = $request->validate(
        [
            'nickname' => ['required','string','max:60', Rule::unique('preregistrations','nickname')],
            'email'    => ['required','string','email','max:255', Rule::unique('preregistrations','email'), Rule::unique('users','email')],
        ],
        [
            'nickname.unique' => 'そのニックネームはすでに使われています。',
            'email.unique'    => 'そのメールアドレスはすでに登録されています。',
        ]
    );

    Preregistration::create([
        'nickname'   => $validated['nickname'],
        'email'      => $validated['email'],
        'ip'         => $request->ip(),
        'user_agent' => substr((string)$request->userAgent(), 0, 512),
        'created_at' => now(),
    ]);

    return redirect()->route('preregister.thanks');
}


    public function thanks()
    {
        return Inertia::render('Auth/PreregisterThanks');
    }
}
